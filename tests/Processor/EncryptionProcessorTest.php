<?php

declare(strict_types=1);

use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Processor\EncryptionProcessor;
use KaririCode\Logging\Security\Encryptor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class EncryptionProcessorTest extends TestCase
{
    private Encryptor|MockObject $encryptor;
    private EncryptionProcessor $processor;

    protected function setUp(): void
    {
        /** @var Encryptor */
        $this->encryptor = $this->createMock(Encryptor::class);
        $this->processor = new EncryptionProcessor($this->encryptor);
    }

    public function testProcessEncryptsMessage(): void
    {
        $message = 'Sensitive information';
        $encryptedMessage = 'EncryptedSensitiveInformation';

        $this->encryptor->expects($this->once())
            ->method('encrypt')
            ->with($message)
            ->willReturn($encryptedMessage);

        $record = new LogRecord(
            LogLevel::INFO,
            $message,
            [],
            new DateTimeImmutable()
        );

        $result = $this->processor->process($record);

        $this->assertInstanceOf(LogRecord::class, $result);
        $this->assertEquals($encryptedMessage, $result->message);
        $this->assertEquals([], $result->context);
        $this->assertEquals($record->datetime, $result->datetime);
    }

    public function testProcessThrowsExceptionOnEncryptionFailure(): void
    {
        $message = 'Sensitive information';

        $this->encryptor->expects($this->once())
            ->method('encrypt')
            ->with($message)
            ->willThrowException(new RuntimeException('Encryption failed'));

        $record = new LogRecord(
            LogLevel::INFO,
            $message,
            [],
            new DateTimeImmutable()
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Encryption failed');

        $this->processor->process($record);
    }
}
