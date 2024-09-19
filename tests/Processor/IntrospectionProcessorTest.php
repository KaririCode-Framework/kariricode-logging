<?php

declare(strict_types=1);

namespace Tests\KaririCode\Logging\Processor;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Processor\IntrospectionProcessor;
use PHPUnit\Framework\TestCase;

final class IntrospectionProcessorTest extends TestCase
{
    private IntrospectionProcessor $processor;

    protected function setUp(): void
    {
        $this->processor = new IntrospectionProcessor();
    }

    /**
     * @dataProvider provideNonTrackableLevels
     */
    public function testProcessDoesNotModifyRecordForNonTrackableLevels(LogLevel $level): void
    {
        $record = $this->createMockRecord($level);
        $processedRecord = $this->processor->process($record);
        $this->assertSame($record, $processedRecord);
    }

    /**
     * @dataProvider provideTrackableLevels
     */
    public function testProcessAddsIntrospectionDataForTrackableLevels(LogLevel $level): void
    {
        $record = $this->createMockRecord($level);
        $processedRecord = $this->processor->process($record);
        $this->assertInstanceOf(LogRecord::class, $processedRecord);
        $this->assertArrayHasKey('file', $processedRecord->context);
        $this->assertArrayHasKey('line', $processedRecord->context);
        $this->assertArrayHasKey('class', $processedRecord->context);
        $this->assertArrayHasKey('function', $processedRecord->context);
    }

    public function testProcessRespectsStackDepth(): void
    {
        $customDepthProcessor = new IntrospectionProcessor(2);
        $record = $this->createMockRecord(LogLevel::ERROR);
        $processedRecord = $customDepthProcessor->process($record);
        $this->assertInstanceOf(LogRecord::class, $processedRecord);
        $this->assertArrayHasKey('file', $processedRecord->context);
    }

    public function testProcessPreservesOriginalContext(): void
    {
        $originalContext = ['key' => 'value'];
        $record = $this->createMockRecord(LogLevel::ERROR, $originalContext);
        $processedRecord = $this->processor->process($record);
        $this->assertInstanceOf(LogRecord::class, $processedRecord);
        $this->assertArrayHasKey('key', $processedRecord->context);
        $this->assertEquals('value', $processedRecord->context['key']);
    }

    public function testGetMaxDepthHandlesInvalidTraceDepth(): void
    {
        $reflection = new \ReflectionClass(IntrospectionProcessor::class);
        $getMaxDepthMethod = $reflection->getMethod('getMaxDepth');
        $getMaxDepthMethod->setAccessible(true);
        $deepProcessor = new IntrospectionProcessor(1000);
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $maxDepth = $getMaxDepthMethod->invoke($deepProcessor, $trace);
        $this->assertLessThanOrEqual(count($trace) - 1, $maxDepth);
        $this->assertGreaterThan(0, $maxDepth);
    }

    /**
     * @return array<array{0: LogLevel}>
     */
    public static function provideNonTrackableLevels(): array
    {
        return [
            [LogLevel::DEBUG],
            [LogLevel::INFO],
            [LogLevel::NOTICE],
            [LogLevel::WARNING],
        ];
    }

    /**
     * @return array<array{0: LogLevel}>
     */
    public static function provideTrackableLevels(): array
    {
        return [
            [LogLevel::ERROR],
            [LogLevel::CRITICAL],
            [LogLevel::ALERT],
            [LogLevel::EMERGENCY],
        ];
    }

    private function createMockRecord(LogLevel $level, array $context = []): ImmutableValue
    {
        return new LogRecord(
            $level,
            'Test message',
            $context,
            new \DateTimeImmutable()
        );
    }
}
