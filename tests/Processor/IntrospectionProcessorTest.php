<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Processor;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Processor\IntrospectionProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * High-quality unit tests for the IntrospectionProcessor class.
 */
#[CoversClass(IntrospectionProcessor::class)]
final class IntrospectionProcessorTest extends TestCase
{
    private IntrospectionProcessor $processor;

    protected function setUp(): void
    {
        $this->processor = new IntrospectionProcessor();
    }

    public function testProcessShouldCaptureCorrectStackDepth(): void
    {
        // Arrange - Use a deeper stack depth to ensure we capture test context
        $processor = new IntrospectionProcessor(10);
        $record = $this->createMockRecord(LogLevel::ERROR);

        // Act
        $processedRecord = $processor->process($record);

        // Assert
        $this->assertInstanceOf(LogRecord::class, $processedRecord);
        $context = $processedRecord->context;

        // Verify that introspection data is present (at least file and line should always be there)
        $this->assertArrayHasKey('file', $context, 'File information should be captured');
        $this->assertArrayHasKey('line', $context, 'Line information should be captured');

        // Verify data types and basic validity
        $this->assertIsString($context['file'], 'File should be a string');
        $this->assertIsInt($context['line'], 'Line should be an integer');
        $this->assertGreaterThan(0, $context['line'], 'Line number should be positive');
        $this->assertNotEmpty($context['file'], 'File path should not be empty');

        // Function should typically be present
        if (isset($context['function'])) {
            $this->assertIsString($context['function'], 'Function should be a string');
            $this->assertNotEmpty($context['function'], 'Function should not be empty');
        }

        // Class may or may not be present (depends on context)
        if (isset($context['class'])) {
            $this->assertIsString($context['class'], 'Class should be a string');
            $this->assertNotEmpty($context['class'], 'Class should not be empty');
        }
    }

    public function testProcessShouldWorkWithDifferentStackDepths(): void
    {
        // Test with various stack depths to ensure robustness
        $depths = [1, 3, 5, 8, 10];

        foreach ($depths as $depth) {
            // Arrange
            $processor = new IntrospectionProcessor($depth);
            $record = $this->createMockRecord(LogLevel::ERROR);

            // Act
            $processedRecord = $processor->process($record);

            // Assert
            $this->assertInstanceOf(LogRecord::class, $processedRecord, "Failed for depth {$depth}");

            $context = $processedRecord->context;
            $this->assertArrayHasKey('file', $context, "File missing for depth {$depth}");
            $this->assertArrayHasKey('line', $context, "Line missing for depth {$depth}");

            // Verify basic data validity
            $this->assertNotEmpty($context['file'], "File empty for depth {$depth}");
            $this->assertGreaterThan(0, $context['line'], "Invalid line for depth {$depth}");
        }
    }

    // ========================================================================
    // Data Providers
    // ========================================================================

    public static function provideNonTrackableLevels(): array
    {
        return [
            'debug level' => [LogLevel::DEBUG],
            'info level' => [LogLevel::INFO],
            'notice level' => [LogLevel::NOTICE],
            'warning level' => [LogLevel::WARNING],
        ];
    }

    public static function provideTrackableLevels(): array
    {
        return [
            'error level' => [LogLevel::ERROR],
            'critical level' => [LogLevel::CRITICAL],
            'alert level' => [LogLevel::ALERT],
            'emergency level' => [LogLevel::EMERGENCY],
        ];
    }

    public static function provideInvalidStackDepths(): array
    {
        return [
            'negative depth' => [-1],
            'zero depth' => [0],
            'excessive depth' => [51],
            'very high depth' => [100],
        ];
    }

    public static function provideValidBoundaryStackDepths(): array
    {
        return [
            'minimum valid depth' => [1],
            'maximum valid depth' => [50],
        ];
    }

    // ========================================================================
    // Core Behavior Tests
    // ========================================================================

    #[DataProvider('provideNonTrackableLevels')]
    public function testProcessShouldNotModifyRecordForNonTrackableLevels(LogLevel $level): void
    {
        // Arrange
        $record = $this->createMockRecord($level);

        // Act
        $processedRecord = $this->processor->process($record);

        // Assert
        $this->assertSame($record, $processedRecord, "Record should not be modified for level {$level->value}");
    }

    /**
     * Tests that the processor adds correct introspection data for trackable log levels.
     */
    #[DataProvider('provideTrackableLevels')]
    public function testProcessShouldAddIntrospectionDataForTrackableLevels(LogLevel $level): void
    {
        // Arrange
        $record = $this->createMockRecord($level);

        // Act
        $processedRecord = $this->processor->process($record);

        // Assert
        $this->assertInstanceOf(LogRecord::class, $processedRecord);
        $this->assertNotSame($record, $processedRecord, 'A new record instance should be returned');

        $context = $processedRecord->context;

        // Verify introspection data is added
        $this->assertArrayHasKey('file', $context, 'File information should be present');
        $this->assertArrayHasKey('line', $context, 'Line information should be present');
        $this->assertArrayHasKey('class', $context, 'Class information should be present');
        $this->assertArrayHasKey('function', $context, 'Function information should be present');

        // Verify data types and basic validity
        $this->assertIsString($context['file'], 'File should be a string');
        $this->assertIsInt($context['line'], 'Line should be an integer');
        $this->assertIsString($context['class'], 'Class should be a string');
        $this->assertIsString($context['function'], 'Function should be a string');

        // Verify line number is positive
        $this->assertGreaterThan(0, $context['line'], 'Line number should be positive');

        // Verify file path is not empty
        $this->assertNotEmpty($context['file'], 'File path should not be empty');
    }

    public function testProcessShouldHandleNonLogRecordInput(): void
    {
        // Arrange
        $nonLogRecord = $this->createMock(ImmutableValue::class);

        // Act
        $result = $this->processor->process($nonLogRecord);

        // Assert
        $this->assertSame($nonLogRecord, $result, 'Non-LogRecord inputs should be returned unchanged');
    }

    // ========================================================================
    // Context and Configuration Tests
    // ========================================================================

    public function testProcessShouldPreserveOriginalContext(): void
    {
        // Arrange
        $originalContext = ['user_id' => 123, 'request_id' => 'abc-xyz'];
        $record = $this->createMockRecord(LogLevel::ERROR, $originalContext);

        // Act
        $processedRecord = $this->processor->process($record);

        // Assert
        $this->assertInstanceOf(LogRecord::class, $processedRecord);
        $this->assertEquals(123, $processedRecord->context['user_id'], 'Original context key "user_id" should be preserved');
        $this->assertEquals('abc-xyz', $processedRecord->context['request_id'], 'Original context key "request_id" should be preserved');
        $this->assertArrayHasKey('file', $processedRecord->context, 'Introspection data should be merged with original context');
    }

    public function testProcessShouldMaintainImmutabilityOfOriginalRecord(): void
    {
        // Arrange
        $originalContext = ['original' => 'data'];
        $originalRecord = $this->createMockRecord(LogLevel::ERROR, $originalContext);

        // Act
        $processedRecord = $this->processor->process($originalRecord);

        // Assert
        $this->assertNotSame($originalRecord, $processedRecord, 'Original record should remain unchanged');
        $this->assertEquals(['original' => 'data'], $originalRecord->context, 'Original record context should be unmodified');
        $this->assertCount(1, $originalRecord->context, 'Original record should maintain original context count');
        $this->assertGreaterThan(1, count($processedRecord->context), 'Processed record should have additional introspection data');
    }

    public function testProcessShouldNotIncludeNullValuesInIntrospectionData(): void
    {
        // Arrange
        $record = $this->createMockRecord(LogLevel::ERROR);

        // Act
        $processedRecord = $this->processor->process($record);

        // Assert
        $introspectionKeys = ['file', 'line', 'class', 'function', 'type'];
        foreach ($processedRecord->context as $key => $value) {
            if (in_array($key, $introspectionKeys, true)) {
                $this->assertNotNull($value, "Introspection field '{$key}' should not be null");
            }
        }
    }

    // ========================================================================
    // Constructor and Validation Tests
    // ========================================================================

    #[DataProvider('provideInvalidStackDepths')]
    public function testConstructorShouldThrowExceptionForInvalidStackDepth(int $invalidDepth): void
    {
        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Stack depth must be between 1 and 50, got {$invalidDepth}");

        // Act
        new IntrospectionProcessor($invalidDepth);
    }

    #[DataProvider('provideValidBoundaryStackDepths')]
    public function testConstructorShouldAcceptValidBoundaryStackDepths(int $validDepth): void
    {
        // Act & Assert - Should not throw exceptions
        $processor = new IntrospectionProcessor($validDepth);

        $this->assertInstanceOf(IntrospectionProcessor::class, $processor);
    }

    public function testConstructorShouldHandleCustomStackDepth(): void
    {
        // Arrange
        $customDepth = 3;
        $processor = new IntrospectionProcessor($customDepth);
        $record = $this->createMockRecord(LogLevel::ERROR);

        // Act
        $processedRecord = $processor->process($record);

        // Assert
        $this->assertInstanceOf(LogRecord::class, $processedRecord);
        $this->assertArrayHasKey('file', $processedRecord->context);
        $this->assertArrayHasKey('line', $processedRecord->context);
    }

    public function testConstructorShouldHandleIncludeArgsOption(): void
    {
        // Arrange
        $processor = new IntrospectionProcessor(6, true);
        $record = $this->createMockRecord(LogLevel::ERROR);

        // Act
        $processedRecord = $processor->process($record);

        // Assert
        $this->assertInstanceOf(LogRecord::class, $processedRecord);
        $this->assertArrayHasKey('file', $processedRecord->context);
        $this->assertArrayHasKey('function', $processedRecord->context);
    }

    // ========================================================================
    // Edge Cases and Error Handling Tests
    // ========================================================================

    public function testProcessShouldHandleEmptyContext(): void
    {
        // Arrange
        $record = $this->createMockRecord(LogLevel::ERROR, []);

        // Act
        $processedRecord = $this->processor->process($record);

        // Assert
        $this->assertInstanceOf(LogRecord::class, $processedRecord);
        $this->assertArrayHasKey('file', $processedRecord->context);
        $this->assertArrayHasKey('line', $processedRecord->context);
    }

    public function testProcessShouldHandleRecordWithExtraData(): void
    {
        // Arrange
        $extraData = ['trace_id' => 'abc123', 'span_id' => 'def456'];
        $record = new LogRecord(
            LogLevel::ERROR,
            'Test message',
            ['context' => 'data'],
            new \DateTimeImmutable(),
            $extraData
        );

        // Act
        $processedRecord = $this->processor->process($record);

        // Assert
        $this->assertInstanceOf(LogRecord::class, $processedRecord);
        $this->assertEquals($extraData, $processedRecord->extra, 'Extra data should be preserved');
        $this->assertArrayHasKey('file', $processedRecord->context);
    }

    // ========================================================================
    // Helper Methods
    // ========================================================================

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
