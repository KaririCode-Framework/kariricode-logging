<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Handler;

use KaririCode\Logging\Exception\LoggingException;
use KaririCode\Logging\Formatter\LineFormatter;
use KaririCode\Logging\Handler\FileHandler;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use PHPUnit\Framework\TestCase;

class FileHandlerTest extends TestCase
{
    private string $testLogDir;
    private string $testLogFile;
    private array $mockFunctions = [];

    protected function setUp(): void
    {
        $this->testLogDir = sys_get_temp_dir() . '/test_logs_' . uniqid();
        mkdir($this->testLogDir);
        $this->testLogFile = $this->testLogDir . '/test.log';

        if (file_exists($this->testLogFile)) {
            unlink($this->testLogFile);
        }
        if (is_dir($this->testLogDir)) {
            rmdir($this->testLogDir);
        }
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->testLogDir);
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

    public function testConstructorCreatesDirectory(): void
    {
        new FileHandler($this->testLogFile);
        $this->assertDirectoryExists($this->testLogDir);
    }

    public function testConstructorThrowsExceptionOnInvalidPath(): void
    {
        $this->expectException(LoggingException::class);
        $this->expectExceptionMessage('Unable to create log directory');

        $invalidPath = '/path/that/cant/be/created/' . uniqid();

        $mockFileHandler = $this->getMockBuilder(FileHandler::class)
            ->setConstructorArgs([$invalidPath . '/test.log'])
            ->onlyMethods(['createDirectory'])
            ->getMock();

        $mockFileHandler->expects($this->once())
            ->method('createDirectory')
            ->willReturn(false);

        $mockFileHandler->__construct($invalidPath . '/test.log');
    }

    public function testConstructorThrowsExceptionOnNonWritableDirectory(): void
    {
        $this->expectException(LoggingException::class);
        $this->expectExceptionMessage('Log directory is not writable');

        $nonWritableDir = sys_get_temp_dir() . '/non_writable_dir_' . uniqid();
        mkdir($nonWritableDir);

        $mockFileHandler = $this->getMockBuilder(FileHandler::class)
            ->setConstructorArgs([$nonWritableDir . '/test.log'])
            ->onlyMethods(['isDirectoryWritable'])
            ->getMock();

        $mockFileHandler->expects($this->once())
            ->method('isDirectoryWritable')
            ->willReturn(false);
        /** @var AbstractFileHandlerc $mockFileHandler */
        $mockFileHandler->__construct($nonWritableDir . '/test.log');

        $this->removeDirectory($nonWritableDir);
    }

    // E adicionar este método à sua classe FileHandler:
    protected function createDirectory($path)
    {
        if (isset($this->mockFunctions['mkdir'])) {
            return call_user_func($this->mockFunctions['mkdir'], $path);
        }

        return mkdir($path, 0777, true);
    }

    protected function isDirectoryWritable($path)
    {
        if (isset($this->mockFunctions['is_writable'])) {
            return call_user_func($this->mockFunctions['is_writable'], $path);
        }

        return is_writable($path);
    }

    public function testHandleWritesToFile(): void
    {
        $handler = new FileHandler($this->testLogFile);
        $record = new LogRecord(LogLevel::INFO, 'Test message');

        $handler->handle($record);

        $this->assertFileExists($this->testLogFile);
        $content = file_get_contents($this->testLogFile);
        $this->assertStringContainsString('Test message', $content);
    }

    public function testHandleRespectsMinimumLogLevel(): void
    {
        $handler = new FileHandler($this->testLogFile, LogLevel::WARNING);
        $debugRecord = new LogRecord(LogLevel::DEBUG, 'Debug message');
        $warningRecord = new LogRecord(LogLevel::WARNING, 'Warning message');

        $handler->handle($debugRecord);
        $handler->handle($warningRecord);

        $content = file_get_contents($this->testLogFile);
        $this->assertStringNotContainsString('Debug message', $content);
        $this->assertStringContainsString('Warning message', $content);
    }

    public function testHandleUsesFormatter(): void
    {
        $mockFormatter = $this->createMock(LineFormatter::class);
        $mockFormatter->expects($this->once())
            ->method('format')
            ->willReturn('Formatted message');

        $handler = new FileHandler($this->testLogFile);
        $handler->setFormatter($mockFormatter);

        $record = new LogRecord(LogLevel::INFO, 'Test message');
        $handler->handle($record);

        $content = file_get_contents($this->testLogFile);
        $this->assertStringContainsString('Formatted message', $content);
    }

    public function testDestructorClosesFileHandle(): void
    {
        $handler = new FileHandler($this->testLogFile);
        $reflection = new \ReflectionClass($handler);
        $fileHandleProperty = $reflection->getProperty('fileHandle');
        $fileHandleProperty->setAccessible(true);

        $this->assertIsResource($fileHandleProperty->getValue($handler));

        $handler->__destruct();

        $this->assertFalse(is_resource($fileHandleProperty->getValue($handler)));
    }

    public function testLogFileCreatedWithCorrectPermissions(): void
    {
        new FileHandler($this->testLogFile);
        $this->assertFileExists($this->testLogFile);
        $this->assertEquals('0644', substr(sprintf('%o', fileperms($this->testLogFile)), -4));
    }
}
