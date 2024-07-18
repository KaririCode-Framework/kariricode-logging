<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Processor;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Processor\AsyncLogProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AsyncLogProcessorTest extends TestCase
{
    private AsyncLogProcessor $asyncLogProcessor;
    private Logger|MockObject $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(Logger::class);
        $this->asyncLogProcessor = new AsyncLogProcessor($this->logger, 2);
    }

    public function testEnqueueAndProcess(): void
    {
        $this->logger->expects($this->exactly(2))
            ->method('log');

        $record1 = new LogRecord(LogLevel::INFO, 'Test message 1');
        $record2 = new LogRecord(LogLevel::INFO, 'Test message 2');

        $this->asyncLogProcessor->enqueue($record1);
        $this->asyncLogProcessor->enqueue($record2);

        // Simulate the destructor to force processing
        $this->asyncLogProcessor->__destruct();

        $this->assertTrue(true); // If no exception occurs, test passes
    }

    public function testDestructorProcessesRemainingLogs(): void
    {
        $this->logger->expects($this->once())
            ->method('log');

        $record = new LogRecord(LogLevel::INFO, 'Test message');

        $this->asyncLogProcessor->enqueue($record);
        unset($this->asyncLogProcessor); // Trigger the destructor

        $this->assertTrue(true); // If no exception occurs, test passes
    }
}
