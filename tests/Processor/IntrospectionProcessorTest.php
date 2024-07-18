<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Processor;

use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Processor\IntrospectionProcessor;
use PHPUnit\Framework\TestCase;

class IntrospectionProcessorTest extends TestCase
{
    private IntrospectionProcessor $introspectionProcessor;

    protected function setUp(): void
    {
        $this->introspectionProcessor = new IntrospectionProcessor();
    }

    public function testProcessHappyPath(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message');
        $processedRecord = $this->introspectionProcessor->process($record);

        $this->assertArrayHasKey('file', $processedRecord->context);
        $this->assertArrayHasKey('line', $processedRecord->context);
        $this->assertArrayHasKey('class', $processedRecord->context);
        $this->assertArrayHasKey('function', $processedRecord->context);
    }
}
