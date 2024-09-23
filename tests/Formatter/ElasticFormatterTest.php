<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Formatter;

use KaririCode\Logging\Formatter\ElasticFormatter;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use PHPUnit\Framework\TestCase;

final class ElasticFormatterTest extends TestCase
{
    private ElasticFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new ElasticFormatter();
    }

    public function testFormat(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message');
        $expected = json_encode([
            '@timestamp' => $record->datetime->format('c'),
            'message' => 'Test message',
            'level' => LogLevel::INFO->value,
            'context' => [],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->assertEquals($expected, $this->formatter->format($record));
    }

    public function testFormatBatch(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message');
        $expected = json_encode([
            '@timestamp' => $record->datetime->format('c'),
            'message' => 'Test message',
            'level' => LogLevel::INFO->value,
            'context' => [],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->assertEquals($expected, $this->formatter->formatBatch([$record]));
    }
}
