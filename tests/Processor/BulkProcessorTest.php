<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Processor;

use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Processor\BulkProcessor;
use PHPUnit\Framework\TestCase;

class BulkProcessorTest extends TestCase
{
    private BulkProcessor $bulkProcessor;
    private array $flushedRecords;

    protected function setUp(): void
    {
        $this->flushedRecords = [];
        $this->bulkProcessor = new BulkProcessor(
            2,
            function (array $records) {
                $this->flushedRecords = array_merge($this->flushedRecords, $records);
            }
        );
    }

    public function testProcessHappyPath(): void
    {
        $record1 = new LogRecord(LogLevel::INFO, 'Test message 1');
        $record2 = new LogRecord(LogLevel::INFO, 'Test message 2');

        $this->bulkProcessor->process($record1);
        $this->bulkProcessor->process($record2);

        $this->assertCount(2, $this->flushedRecords);
    }

    public function testFlush(): void
    {
        $record1 = new LogRecord(LogLevel::INFO, 'Test message 1');
        $record2 = new LogRecord(LogLevel::INFO, 'Test message 2');

        $this->bulkProcessor->process($record1);
        $this->bulkProcessor->flush();

        $this->assertCount(1, $this->flushedRecords);

        $this->bulkProcessor->process($record2);
        $this->bulkProcessor->flush();

        $this->assertCount(2, $this->flushedRecords);
    }

    public function testDestructorFlushesRecords(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message');
        $this->bulkProcessor->process($record);
        unset($this->bulkProcessor);

        $this->assertCount(1, $this->flushedRecords);
    }
}
