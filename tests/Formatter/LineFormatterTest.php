<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Formatter;

use KaririCode\Logging\Formatter\LineFormatter;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use PHPUnit\Framework\TestCase;

final class LineFormatterTest extends TestCase
{
    private LineFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new LineFormatter();
    }

    public function testFormat(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message');

        $formatted = $this->formatter->format($record);
        $this->assertStringContainsString('INFO', $formatted);
        $this->assertStringContainsString('Test message', $formatted);
    }

    public function testFormatWithContext(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message', ['key' => 'value']);

        $formatted = $this->formatter->format($record);
        $this->assertStringContainsString('Test message', $formatted);
        $this->assertStringContainsString('key', $formatted);
        $this->assertStringContainsString('value', $formatted);
    }
}
