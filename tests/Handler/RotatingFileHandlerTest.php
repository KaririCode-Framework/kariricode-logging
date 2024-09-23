<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Handler;

use KaririCode\Contract\Logging\LogRotator;
use KaririCode\Logging\Exception\LoggingException;
use KaririCode\Logging\Handler\RotatingFileHandler;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RotatingFileHandlerTest extends TestCase
{
    private string $tempDir;
    private string $logFile;
    private LogRotator|MockObject $mockRotator;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/rotating_file_handler_test_' . uniqid();
        mkdir($this->tempDir);
        $this->logFile = $this->tempDir . '/test.log';
        $this->mockRotator = $this->createMock(LogRotator::class);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->removeDirectory("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }

    private function createLogRecord(LogLevel $level, string $message, array $context = []): LogRecord
    {
        return new LogRecord($level, $message, $context);
    }

    public function testHandleWritesLogWhenLevelIsHighEnough(): void
    {
        $handler = new RotatingFileHandler($this->logFile, $this->mockRotator, LogLevel::INFO);
        $record = $this->createLogRecord(LogLevel::ERROR, 'Test error message');

        $handler->handle($record);

        $this->assertFileExists($this->logFile);
        $this->assertStringContainsString('Test error message', file_get_contents($this->logFile));
    }

    public function testHandleDoesNotWriteLogWhenLevelIsTooLow(): void
    {
        $handler = new RotatingFileHandler($this->logFile, $this->mockRotator, LogLevel::ERROR);
        $record = $this->createLogRecord(LogLevel::INFO, 'Test info message');

        $handler->handle($record);

        if (file_exists($this->logFile)) {
            $this->assertEmpty(file_get_contents($this->logFile));
        } else {
            $this->assertTrue(true);
        }
    }

    public function testHandleRotatesFileWhenNecessary(): void
    {
        $this->mockRotator->method('shouldRotate')->willReturn(true);
        $this->mockRotator->expects($this->once())->method('rotate');

        $handler = new RotatingFileHandler($this->logFile, $this->mockRotator);
        $record = $this->createLogRecord(LogLevel::INFO, 'Test rotation message');

        $handler->handle($record);

        $this->assertFileExists($this->logFile);
        $this->assertStringContainsString('Test rotation message', file_get_contents($this->logFile));
    }

    public function testHandleThrowsLoggingExceptionOnError(): void
    {
        $this->mockRotator->method('shouldRotate')->willThrowException(new \RuntimeException('Rotation error'));

        $handler = new RotatingFileHandler($this->logFile, $this->mockRotator);
        $record = $this->createLogRecord(LogLevel::INFO, 'Test error handling');

        $this->expectException(LoggingException::class);
        $this->expectExceptionMessage('Error handling log record: Rotation error');

        $handler->handle($record);
    }

    public function testHandleReopensFileAfterRotation(): void
    {
        $this->mockRotator->method('shouldRotate')->willReturnOnConsecutiveCalls(true, false);
        $this->mockRotator->expects($this->once())->method('rotate');

        $handler = new RotatingFileHandler($this->logFile, $this->mockRotator);
        $record = $this->createLogRecord(LogLevel::INFO, 'Test message');

        // First call - should rotate and reopen
        $handler->handle($record);

        // Second call - should write to the reopened file
        $handler->handle($record);

        $this->assertFileExists($this->logFile);
        $logContent = file_get_contents($this->logFile);
        $this->assertEquals(2, substr_count($logContent, 'Test message'));
    }
}
