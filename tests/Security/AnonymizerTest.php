<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Security;

use KaririCode\Logging\Contract\AnonymizerStrategy;
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
            'credit_card' => [
                'Payment with card: 1234-5678-9012-3456',
                'Payment with card: ****-****-****-3456',
            ],
            'no_sensitive_data' => [
                'This is a regular message without sensitive data.',
                'This is a regular message without sensitive data.',
            ],
        ];
    }

    public function testAddAnonymizer(): void
    {
        // Mocking a new anonymizer strategy
        $ipAnonymizer = $this->createMock(AnonymizerStrategy::class);
        $ipAnonymizer->expects($this->once())
            ->method('anonymize')
            ->with('Server IP: 192.168.1.1')
            ->willReturn('Server IP: ***.***.*.*');
        $ipAnonymizer->expects($this->once())
            ->method('getPattern')
            ->willReturn('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/');

        $this->anonymizer->addAnonymizer('ip', $ipAnonymizer);

        $input = 'Server IP: 192.168.1.1';
        $expected = 'Server IP: ***.***.*.*';
        $result = $this->anonymizer->anonymize($input);

        $this->assertEquals($expected, $result);
    }

    public function testRemoveAnonymizer(): void
    {
        $input = 'Email: info@example.com';
        $expectedBefore = 'Email: in**@example.com';
        $expectedAfter = 'Email: info@example.com';

        // Anonymize with default email anonymizer
        $resultBefore = $this->anonymizer->anonymize($input);
        $this->assertEquals($expectedBefore, $resultBefore);

        // Remove email anonymizer and ensure it's not applied anymore
        $this->anonymizer->removeAnonymizer('email');
        $resultAfter = $this->anonymizer->anonymize($input);
        $this->assertEquals($expectedAfter, $resultAfter);
    }

    public function testAnonymizeWithInvalidRegex(): void
    {
        $invalidAnonymizer = $this->createMock(AnonymizerStrategy::class);
        $invalidAnonymizer->expects($this->once())
            ->method('getPattern')
            ->willReturn('*'); // Invalid regex pattern

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid regex pattern for type: invalid');

        $this->anonymizer->addAnonymizer('invalid', $invalidAnonymizer);
    }
}
