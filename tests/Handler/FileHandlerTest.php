<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\KaririCode\Logging\Handler;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\Exception\LoggingException;
use KaririCode\Logging\Formatter\LineFormatter;
use KaririCode\Logging\Handler\FileHandler;
use KaririCode\Logging\LogLevel;
use PHPUnit\Framework\TestCase;

class FileHandlerTest extends TestCase
{
    private string $testLogDir;
    private string $testLogFile;

    protected function setUp(): void
    {
        $this->testLogDir = sys_get_temp_dir() . '/test_logs';
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
        if (file_exists($this->testLogFile)) {
            unlink($this->testLogFile);
        }
        if (is_dir($this->testLogDir)) {
            rmdir($this->testLogDir);
        }
    }

    public function testConstructorCreatesDirectory(): void
    {
        new FileHandler($this->testLogFile);
        $this->assertDirectoryExists($this->testLogDir);
    }

    public function testConstructorThrowsExceptionOnInvalidPath(): void
    {
        $this->expectException(LoggingException::class);

        // Suppress warnings for this test
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            // Don't throw E_WARNING, E_NOTICE, or E_USER_WARNING errors
            return true;
        });

        try {
            new FileHandler('/invalid/path/test.log');
        } finally {
            // Restore the original error handler
            restore_error_handler();
        }
    }

    public function testHandleWritesToFile(): void
    {
        $handler = new FileHandler($this->testLogFile);
        $record = $this->createMockRecord('Test message', LogLevel::INFO);

        $handler->handle($record);

        $this->assertFileExists($this->testLogFile);
        $content = file_get_contents($this->testLogFile);
        $this->assertStringContainsString('Test message', $content);
    }

    public function testHandleRespectsMinimumLogLevel(): void
    {
        $handler = new FileHandler($this->testLogFile, LogLevel::WARNING);
        $debugRecord = $this->createMockRecord('Debug message', LogLevel::DEBUG);
        $warningRecord = $this->createMockRecord('Warning message', LogLevel::WARNING);

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

        $record = $this->createMockRecord('Test message', LogLevel::INFO);
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

    private function createMockRecord(string $message, LogLevel $level): ImmutableValue
    {
        return new class($message, $level) implements ImmutableValue {
            public function __construct(
                public readonly string $message,
                public readonly LogLevel $level,
                public readonly array $context = [],
                public readonly array $extra = [],
                public readonly \DateTimeImmutable $datetime = new \DateTimeImmutable()
            ) {
            }

            public function toArray(): array
            {
                return [
                    'message' => $this->message,
                    'level' => $this->level,
                    'context' => $this->context,
                    'extra' => $this->extra,
                    'datetime' => $this->datetime,
                ];
            }
        };
    }
}
