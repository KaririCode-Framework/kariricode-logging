<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Processor;

use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Processor\WebProcessor;
use PHPUnit\Framework\TestCase;

class WebProcessorTest extends TestCase
{
    private WebProcessor $webProcessor;

    protected function setUp(): void
    {
        $this->webProcessor = new WebProcessor();
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['HTTP_REFERER'] = 'http://localhost';
    }

    public function testProcessHappyPath(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message');
        $processedRecord = $this->webProcessor->process($record);

        $this->assertArrayHasKey('url', $processedRecord->context);
        $this->assertArrayHasKey('ip', $processedRecord->context);
        $this->assertArrayHasKey('http_method', $processedRecord->context);
        $this->assertArrayHasKey('server', $processedRecord->context);
        $this->assertArrayHasKey('referrer', $processedRecord->context);
    }
}
