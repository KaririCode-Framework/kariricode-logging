<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Handler;

use KaririCode\Logging\Exception\LoggingException;
use KaririCode\Logging\Handler\RotatingFileHandler;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use PHPUnit\Framework\TestCase;

class RotatingFileHandlerTest extends TestCase
{
    private string $testLogDir;
    private string $testLogFile;
    private RotatingFileHandler $rotatingFileHandler;

    protected function setUp(): void
    {
        $this->testLogDir = sys_get_temp_dir() . '/test_rotating_logs';
        $this->testLogFile = $this->testLogDir . '/test_rotating.log';

        if (!is_dir($this->testLogDir)) {
            mkdir($this->testLogDir, 0777, true);
        }

        $this->rotatingFileHandler = new RotatingFileHandler(
            $this->testLogFile
        );
    }

    public function testHandleHappyPath(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message');
        $this->rotatingFileHandler->handle($record);

        $this->assertFileExists($this->testLogFile);
        $this->assertStringContainsString('Test message', file_get_contents($this->testLogFile));
    }

    public function testRotateLogs(): void
    {
        $this->rotatingFileHandler = new RotatingFileHandler($this->testLogFile, 2, 10); // Smaller file size

        for ($i = 0; $i < 5; ++$i) {
            $record = new LogRecord(LogLevel::INFO, str_repeat('A', 10));
            $this->rotatingFileHandler->handle($record);
        }

        $this->assertFileExists($this->testLogFile);
        $this->assertFileExists($this->testLogFile . '.1');
        $this->assertFileExists($this->testLogFile . '.2');
    }

    public function testHandleThrowsExceptionOnWriteFailure(): void
    {
        $this->expectException(LoggingException::class);
        $this->expectExceptionMessage('Unable to create log directory: /invalid');

        // Suppress warnings for this test
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            return true;
        });

        try {
            $invalidHandler = new RotatingFileHandler('/invalid/path.log');
            $record = new LogRecord(LogLevel::INFO, 'Test message');
            $invalidHandler->handle($record);
        } finally {
            restore_error_handler();
        }
    }

    public function testMaxFiles(): void
    {
        $this->rotatingFileHandler = new RotatingFileHandler($this->testLogFile, 3, 10);

        for ($i = 0; $i < 5; ++$i) {
            $record = new LogRecord(LogLevel::INFO, str_repeat('A', 10));
            $this->rotatingFileHandler->handle($record);
        }

        $this->assertFileExists($this->testLogFile);
        $this->assertFileExists($this->testLogFile . '.1');
        $this->assertFileExists($this->testLogFile . '.2');
        $this->assertFileExists($this->testLogFile . '.3');
        $this->assertFileDoesNotExist($this->testLogFile . '.4');
    }

    public function testRespectLogLevel(): void
    {
        $this->rotatingFileHandler = new RotatingFileHandler($this->testLogFile, 2, 50, LogLevel::WARNING);

        $infoRecord = new LogRecord(LogLevel::INFO, 'Info message');
        $warningRecord = new LogRecord(LogLevel::WARNING, 'Warning message');

        $this->rotatingFileHandler->handle($infoRecord);
        $this->rotatingFileHandler->handle($warningRecord);

        $this->assertFileExists($this->testLogFile);
        $content = file_get_contents($this->testLogFile);
        $this->assertStringNotContainsString('Info message', $content);
        $this->assertStringContainsString('Warning message', $content);
    }
}
