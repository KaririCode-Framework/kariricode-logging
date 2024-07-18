<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\KaririCode\Logging\Security;

use KaririCode\Logging\Security\Anonymizer;
use PHPUnit\Framework\TestCase;

class AnonymizerTest extends TestCase
{
    private Anonymizer $anonymizer;

    protected function setUp(): void
    {
        $this->anonymizer = new Anonymizer();

        error_reporting(E_ALL);
        ini_set('display_errors', '1');
    }

    /**
     * @dataProvider provideAnonymizeData
     */
    public function testAnonymize(string $input, string $expected): void
    {
        $result = $this->anonymizer->anonymize($input);
        $this->assertEquals($expected, $result);
    }

    public static function provideAnonymizeData(): array
    {
        return [
            'email' => [
                'Contact us at info@example.com for more information.',
                'Contact us at in**@example.com for more information.',
            ],
            'ip' => [
                'Server IP: 192.168.1.1',
                'Server IP: ***.***.*.*',
            ],
            'credit_card' => [
                'Payment with card: 1234-5678-9012-3456',
                'Payment with card: ****-****-****-3456',
            ],
            'multiple_patterns' => [
                'Email: user@domain.com, IP: 10.0.0.1, Card: 9876-5432-1098-7654',
                'Email: us**@domain.com, IP: **.*.*.*, Card: ****-****-****-7654',
            ],
            'no_sensitive_data' => [
                'This is a regular message without sensitive data.',
                'This is a regular message without sensitive data.',
            ],
        ];
    }

    public function testAddPattern(): void
    {
        $this->anonymizer->addPattern('phone', '/\+\d{1,3}\s?\d{1,14}/');

        $input = 'Call me at +1 1234567890';
        $expected = 'Call me at ** **********';

        $result = $this->anonymizer->anonymize($input);
        $this->assertEquals($expected, $result);
    }

    public function testRemovePattern(): void
    {
        $input = 'Email: test@example.com';
        $expectedBefore = 'Email: te**@example.com';
        $expectedAfter = 'Email: test@example.com';

        $resultBefore = $this->anonymizer->anonymize($input);
        $this->assertEquals($expectedBefore, $resultBefore);

        $this->anonymizer->removePattern('email');

        $resultAfter = $this->anonymizer->anonymize($input);
        $this->assertEquals($expectedAfter, $resultAfter);
    }

    public function testAnonymizeWithInvalidRegex(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid regex pattern for type: invalid');

        $this->anonymizer->addPattern('invalid', '*');
    }
}
