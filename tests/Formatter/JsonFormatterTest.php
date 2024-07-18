<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Formatter;

use KaririCode\Logging\Formatter\JsonFormatter;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use PHPUnit\Framework\TestCase;

class JsonFormatterTest extends TestCase
{
    private JsonFormatter $jsonFormatter;

    protected function setUp(): void
    {
        $this->jsonFormatter = new JsonFormatter();
    }

    public function testFormatHappyPath(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message');

        $expectedOutput = json_encode([
            'datetime' => $record->datetime->format('Y-m-d H:i:s'),
            'level' => 'info',
            'message' => 'Test message',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->assertEquals($expectedOutput, $this->jsonFormatter->format($record));
    }

    public function testFormatWithContext(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message', ['key' => 'value']);

        $expectedOutput = json_encode([
            'datetime' => $record->datetime->format('Y-m-d H:i:s'),
            'level' => 'info',
            'message' => 'Test message',
            'context' => ['key' => 'value'],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->assertEquals($expectedOutput, $this->jsonFormatter->format($record));
    }

    public function testFormatThrowsException(): void
    {
        $this->expectException(\JsonException::class);

        $record = new LogRecord(LogLevel::INFO, "\xB1\x31"); // Invalid UTF-8 sequence

        $this->jsonFormatter->format($record);
    }
}
