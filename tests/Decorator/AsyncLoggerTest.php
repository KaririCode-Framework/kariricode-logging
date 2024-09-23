<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Decorator;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\Decorator\AsyncLogger;
use KaririCode\Logging\LogLevel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AsyncLoggerTest extends TestCase
{
    private AsyncLogger $asyncLogger;
    private Logger|MockObject $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(Logger::class);
        $this->asyncLogger = new AsyncLogger($this->logger);
    }

    public function testLogHappyPath(): void
    {
        $level = LogLevel::INFO;
        $message = 'Test message';
        $context = ['key' => 'value'];

        $this->logger->expects($this->once())
            ->method('log')
            ->with($level, $message, $context);

        $this->asyncLogger->log($level, $message, $context);
    }

    public function testDestructorProcessesAllLogs(): void
    {
        $logs = [
            [LogLevel::INFO, 'First message', ['first' => 'context']],
            [LogLevel::ERROR, 'Second message', ['second' => 'context']],
            [LogLevel::DEBUG, 'Third message', ['third' => 'context']],
        ];

        $this->logger->expects($this->exactly(count($logs)))
            ->method('log')
            ->willReturnCallback(function ($level, $message, $context) use ($logs) {
                static $callCount = 0;
                $this->assertSame($logs[$callCount][0], $level);
                $this->assertSame($logs[$callCount][1], $message);
                $this->assertSame($logs[$callCount][2], $context);
                ++$callCount;
            });

        foreach ($logs as $log) {
            $this->asyncLogger->log(...$log);
        }

        $this->asyncLogger->__destruct();
    }

    public function testLogThrowsException(): void
    {
        $this->logger->method('log')
            ->willThrowException(new \RuntimeException('Logging failed'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Logging failed');

        $this->asyncLogger->log(LogLevel::INFO, 'Test message', ['key' => 'value']);
        $this->asyncLogger->__destruct();
    }
}
