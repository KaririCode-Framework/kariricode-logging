<?php

declare(strict_types=1);

use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Processor\AnonymizerProcessor;
use KaririCode\Logging\Security\Anonymizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AnonymizerProcessorTest extends TestCase
{
    private Anonymizer|MockObject $anonymizer;
    private AnonymizerProcessor $processor;

    protected function setUp(): void
    {
        /** @var Anonymizer */
        $this->anonymizer = $this->createMock(Anonymizer::class);
        $this->processor = new AnonymizerProcessor($this->anonymizer);
    }

    public function testProcessAnonymizesEmail(): void
    {
        // Setup
        $message = 'Sensitive information: user@example.com';
        $anonymizedMessage = 'Sensitive information: us****@example.com';

        $this->anonymizer->expects($this->once())
            ->method('anonymize')
            ->with($message)
            ->willReturn($anonymizedMessage);

        $record = new LogRecord(
            LogLevel::INFO,
            $message,
            [],
            new DateTimeImmutable()
        );

        $result = $this->processor->process($record);

        $this->assertInstanceOf(LogRecord::class, $result);
        $this->assertEquals($anonymizedMessage, $result->message);
    }

    public function testProcessAnonymizesIp(): void
    {
        $message = 'Sensitive information: 192.168.0.1';
        $anonymizedMessage = 'Sensitive information: ***.***.***.***';

        $this->anonymizer->expects($this->once())
            ->method('anonymize')
            ->with($message)
            ->willReturn($anonymizedMessage);

        $record = new LogRecord(
            LogLevel::INFO,
            $message,
            [],
            new DateTimeImmutable()
        );

        $result = $this->processor->process($record);

        $this->assertEquals($anonymizedMessage, $result->message);
    }

    public function testProcessAnonymizesCreditCard(): void
    {
        $message = 'Sensitive information: 4111 1111 1111 1111';
        $anonymizedMessage = 'Sensitive information: **** **** **** 1111';

        $this->anonymizer->expects($this->once())
            ->method('anonymize')
            ->with($message)
            ->willReturn($anonymizedMessage);

        $record = new LogRecord(
            LogLevel::INFO,
            $message,
            [],
            new DateTimeImmutable()
        );

        $result = $this->processor->process($record);

        $this->assertEquals($anonymizedMessage, $result->message);
    }

    public function testProcessDoesNotChangeContextOrDateTime(): void
    {
        $message = 'Sensitive information';
        $anonymizedMessage = 'Anonymized information';

        $this->anonymizer->expects($this->once())
            ->method('anonymize')
            ->with($message)
            ->willReturn($anonymizedMessage);

        $context = ['some_key' => 'some_value'];
        $datetime = new DateTimeImmutable();
        $record = new LogRecord(
            LogLevel::INFO,
            $message,
            $context,
            $datetime
        );

        $result = $this->processor->process($record);

        $this->assertEquals($context, $result->context);
        $this->assertEquals($datetime, $result->datetime);
    }
}
