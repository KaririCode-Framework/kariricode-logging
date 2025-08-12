<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Formatter;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\Formatter\AbstractFormatter;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * High-quality unit tests for the AbstractFormatter class.
 */
#[CoversClass(AbstractFormatter::class)]
final class AbstractFormatterTest extends TestCase
{
    private TestableFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new TestableFormatter();
    }

    // ========================================================================
    // Data Providers
    // ========================================================================

    public static function provideDateFormats(): array
    {
        return [
            'default format' => ['Y-m-d H:i:s', '2024-01-15 10:30:45'],
            'simple date' => ['Y-m-d', '2024-01-15'],
            'time only' => ['H:i:s', '10:30:45'],
            'full format' => ['Y-m-d H:i:s.u', '2024-01-15 10:30:45.123456'],
            'iso format' => ['c', '2024-01-15T10:30:45+00:00'],
        ];
    }

    public static function provideContextScenarios(): array
    {
        return [
            'empty context' => [[], false, false],
            'non-empty context with inclusion enabled' => [['key' => 'value'], true, true],
            'non-empty context with inclusion disabled' => [['key' => 'value'], false, false],
            'nested context' => [['user' => ['id' => 123, 'name' => 'John']], true, true],
        ];
    }

    public static function provideExtraDataScenarios(): array
    {
        return [
            'empty extra' => [[], false, false],
            'non-empty extra with inclusion enabled' => [['trace_id' => 'abc123'], true, true],
            'non-empty extra with inclusion disabled' => [['trace_id' => 'abc123'], false, false],
            'complex extra data' => [['metrics' => ['memory' => '128MB']], true, true],
        ];
    }

    public static function provideLogRecords(): array
    {
        return [
            'simple record' => [
                new LogRecord(LogLevel::INFO, 'Test message'),
            ],
            'record with context' => [
                new LogRecord(LogLevel::ERROR, 'Error occurred', ['user_id' => 123]),
            ],
            'record with extra' => [
                new LogRecord(LogLevel::DEBUG, 'Debug info', [], new \DateTimeImmutable(), ['trace' => 'abc']),
            ],
        ];
    }

    // ========================================================================
    // Constructor and Configuration Tests
    // ========================================================================

    public function testConstructorShouldSetDefaultValues(): void
    {
        // Arrange & Act
        $formatter = new TestableFormatter();

        // Assert
        $this->assertEquals('Y-m-d H:i:s', $formatter->dateFormat, 'Default date format should be set');
        $this->assertTrue($formatter->includeContext, 'Context inclusion should be enabled by default');
        $this->assertFalse($formatter->includeExtra, 'Extra inclusion should be disabled by default');
    }

    public function testConstructorShouldAcceptCustomValues(): void
    {
        // Arrange
        $customDateFormat = 'Y/m/d H:i:s';
        $includeContext = false;
        $includeExtra = true;

        // Act
        $formatter = new TestableFormatter($customDateFormat, $includeContext, $includeExtra);

        // Assert
        $this->assertEquals($customDateFormat, $formatter->dateFormat, 'Custom date format should be set');
        $this->assertEquals($includeContext, $formatter->includeContext, 'Custom context inclusion should be set');
        $this->assertEquals($includeExtra, $formatter->includeExtra, 'Custom extra inclusion should be set');
    }

    // ========================================================================
    // Fluent Interface Tests
    // ========================================================================

    #[DataProvider('provideDateFormats')]
    public function testWithDateFormatShouldCreateNewInstanceWithNewFormat(string $newFormat, string $expectedOutput): void
    {
        // Arrange
        $originalFormatter = new TestableFormatter('Y-m-d H:i:s', true, false);
        $testDate = new \DateTimeImmutable('2024-01-15 10:30:45.123456');

        // Act
        $newFormatter = $originalFormatter->withDateFormat($newFormat);

        // Assert
        $this->assertNotSame($originalFormatter, $newFormatter, 'Should create new instance');
        $this->assertEquals($newFormat, $newFormatter->dateFormat, 'New formatter should have new date format');
        $this->assertEquals('Y-m-d H:i:s', $originalFormatter->dateFormat, 'Original formatter should be unchanged');

        // Test actual formatting
        $formattedTime = $newFormatter->formatTimestamp($testDate);
        $this->assertStringStartsWith(substr($expectedOutput, 0, -6), $formattedTime, 'Formatted time should match expected pattern');
    }

    public function testWithContextInclusionShouldCreateNewInstanceWithNewSetting(): void
    {
        // Arrange
        $originalFormatter = new TestableFormatter('Y-m-d H:i:s', true, false);

        // Act
        $newFormatter = $originalFormatter->withContextInclusion(false);

        // Assert
        $this->assertNotSame($originalFormatter, $newFormatter, 'Should create new instance');
        $this->assertFalse($newFormatter->includeContext, 'New formatter should have context inclusion disabled');
        $this->assertTrue($originalFormatter->includeContext, 'Original formatter should be unchanged');
        $this->assertEquals($originalFormatter->dateFormat, $newFormatter->dateFormat, 'Date format should be preserved');
        $this->assertEquals($originalFormatter->includeExtra, $newFormatter->includeExtra, 'Extra inclusion should be preserved');
    }

    // ========================================================================
    // Timestamp Formatting Tests
    // ========================================================================

    #[DataProvider('provideDateFormats')]
    public function testFormatTimestampShouldFormatAccordingToConfiguredFormat(string $format, string $expected): void
    {
        // Arrange
        $formatter = new TestableFormatter($format);
        $testDate = new \DateTimeImmutable('2024-01-15 10:30:45.123456');

        // Act
        $result = $formatter->formatTimestamp($testDate);

        // Assert
        if ('c' === $format) {
            // ISO format includes timezone, so just check the date part
            $this->assertStringStartsWith('2024-01-15T10:30:45', $result);
        } else {
            $this->assertEquals($expected, $result, "Timestamp should be formatted as '{$format}'");
        }
    }

    // ========================================================================
    // Context and Extra Data Tests
    // ========================================================================

    #[DataProvider('provideContextScenarios')]
    public function testShouldIncludeContextShouldRespectConfiguration(array $context, bool $includeContext, bool $expected): void
    {
        // Arrange
        $formatter = new TestableFormatter('Y-m-d H:i:s', $includeContext, false);

        // Act
        $result = $formatter->shouldIncludeContext($context);

        // Assert
        $this->assertEquals($expected, $result, "Context inclusion should be {$expected} for given scenario");
    }

    #[DataProvider('provideExtraDataScenarios')]
    public function testShouldIncludeExtraShouldRespectConfiguration(array $extra, bool $includeExtra, bool $expected): void
    {
        // Arrange
        $formatter = new TestableFormatter('Y-m-d H:i:s', true, $includeExtra);

        // Act
        $result = $formatter->shouldIncludeExtra($extra);

        // Assert
        $this->assertEquals($expected, $result, "Extra inclusion should be {$expected} for given scenario");
    }

    // ========================================================================
    // Batch Formatting Tests
    // ========================================================================

    #[DataProvider('provideLogRecords')]
    public function testFormatBatchShouldFormatMultipleRecords(LogRecord $record): void
    {
        // Arrange
        $records = [$record, $record, $record]; // Use same record multiple times for simplicity

        // Act
        $result = $this->formatter->formatBatch($records);

        // Assert
        $this->assertIsString($result, 'Batch format should return a string');

        $lines = explode(PHP_EOL, $result);
        $this->assertCount(3, $lines, 'Should format all records');

        foreach ($lines as $line) {
            $this->assertStringContainsString('FORMATTED:', $line, 'Each line should be formatted');
        }
    }

    public function testFormatBatchShouldHandleEmptyArray(): void
    {
        // Arrange
        $emptyRecords = [];

        // Act
        $result = $this->formatter->formatBatch($emptyRecords);

        // Assert
        $this->assertEquals('', $result, 'Empty batch should return empty string');
    }

    public function testFormatBatchShouldHandleSingleRecord(): void
    {
        // Arrange
        $singleRecord = [new LogRecord(LogLevel::INFO, 'Single message')];

        // Act
        $result = $this->formatter->formatBatch($singleRecord);

        // Assert
        $this->assertStringContainsString('FORMATTED:', $result, 'Single record should be formatted');
        $this->assertStringNotContainsString(PHP_EOL, $result, 'Single record should not contain newlines');
    }

    // ========================================================================
    // Edge Cases and Error Handling Tests
    // ========================================================================

    public function testFormatTimestampShouldHandleDifferentTimezones(): void
    {
        // Arrange
        $formatter = new TestableFormatter('Y-m-d H:i:s e');
        $utcDate = new \DateTimeImmutable('2024-01-15 10:30:45', new \DateTimeZone('UTC'));
        $nyDate = new \DateTimeImmutable('2024-01-15 10:30:45', new \DateTimeZone('America/New_York'));

        // Act
        $utcResult = $formatter->formatTimestamp($utcDate);
        $nyResult = $formatter->formatTimestamp($nyDate);

        // Assert
        $this->assertStringContainsString('UTC', $utcResult, 'UTC timezone should be included');
        $this->assertStringContainsString('America/New_York', $nyResult, 'New York timezone should be included');
        $this->assertNotEquals($utcResult, $nyResult, 'Different timezones should produce different output');
    }

    public function testShouldIncludeContextShouldReturnFalseForNullValues(): void
    {
        // Arrange
        $formatter = new TestableFormatter('Y-m-d H:i:s', true, false);
        $contextWithNulls = ['key1' => null, 'key2' => '', 'key3' => 0];

        // Act
        $result = $formatter->shouldIncludeContext($contextWithNulls);

        // Assert
        $this->assertTrue($result, 'Context with non-empty array should be included even with null values');
    }

    // ========================================================================
    // Integration Tests
    // ========================================================================

    public function testFormatterShouldMaintainImmutability(): void
    {
        // Arrange
        $originalFormatter = new TestableFormatter('Y-m-d H:i:s', true, false);

        // Act
        $formatter1 = $originalFormatter->withDateFormat('H:i:s');
        $formatter2 = $originalFormatter->withContextInclusion(false);
        $formatter3 = $formatter1->withContextInclusion(false);

        // Assert
        $this->assertEquals('Y-m-d H:i:s', $originalFormatter->dateFormat, 'Original should be unchanged');
        $this->assertTrue($originalFormatter->includeContext, 'Original context setting should be unchanged');

        $this->assertEquals('H:i:s', $formatter1->dateFormat, 'Formatter1 should have new date format');
        $this->assertTrue($formatter1->includeContext, 'Formatter1 context should be unchanged');

        $this->assertEquals('Y-m-d H:i:s', $formatter2->dateFormat, 'Formatter2 date format should be unchanged');
        $this->assertFalse($formatter2->includeContext, 'Formatter2 should have new context setting');

        $this->assertEquals('H:i:s', $formatter3->dateFormat, 'Formatter3 should have formatter1 date format');
        $this->assertFalse($formatter3->includeContext, 'Formatter3 should have new context setting');
    }
}

/**
 * Testable concrete implementation of AbstractFormatter for testing purposes.
 */
class TestableFormatter extends AbstractFormatter
{
    public function format(ImmutableValue $record): string
    {
        if (!$record instanceof LogRecord) {
            return 'INVALID_RECORD';
        }

        return 'FORMATTED: ' . $record->message;
    }

    // Expose protected methods for testing
    public function formatTimestamp(\DateTimeImmutable $datetime): string
    {
        return parent::formatTimestamp($datetime);
    }

    public function shouldIncludeContext(array $context): bool
    {
        return parent::shouldIncludeContext($context);
    }

    public function shouldIncludeExtra(array $extra): bool
    {
        return parent::shouldIncludeExtra($extra);
    }
}
