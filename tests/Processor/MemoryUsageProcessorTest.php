<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Processor;

use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Processor\Metric\MemoryUsageProcessor;
use PHPUnit\Framework\TestCase;

final class MemoryUsageProcessorTest extends TestCase
{
    private MemoryUsageProcessor $memoryUsageProcessor;

    protected function setUp(): void
    {
        $this->memoryUsageProcessor = new MemoryUsageProcessor();
    }

    public function testProcessHappyPath(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message');
        $processedRecord = $this->memoryUsageProcessor->process($record);

        $this->assertArrayHasKey('memory_usage', $processedRecord->context);
        $this->assertArrayHasKey('memory_peak', $processedRecord->context);
    }
}
