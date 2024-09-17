<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Logger;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\Exception\LoggerNotFoundException;
use KaririCode\Logging\LoggerRegistry;
use PHPUnit\Framework\TestCase;

class LoggerRegistryTest extends TestCase
{
    private LoggerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new LoggerRegistry();
    }

    public function testAddAndGetLogger(): void
    {
        $mockLogger = $this->createMock(Logger::class);
        $this->registry->addLogger('test', $mockLogger);

        $this->assertSame($mockLogger, $this->registry->getLogger('test'));
    }

    public function testGetNonexistentLogger(): void
    {
        $this->expectException(LoggerNotFoundException::class);
        $this->expectExceptionMessage('Logger with name "nonexistent" not found.'); // Corrigido para usar aspas duplas

        $this->registry->getLogger('nonexistent');
    }

    public function testRemoveLogger(): void
    {
        $mockLogger = $this->createMock(Logger::class);
        $this->registry->addLogger('test', $mockLogger);
        $this->registry->removeLogger('test');

        $this->expectException(LoggerNotFoundException::class);
        $this->expectExceptionMessage('Logger with name "test" not found.'); // Corrigido para usar aspas duplas

        $this->registry->getLogger('test');
    }

    public function testCannotAddLoggerWithSameNameTwice(): void
    {
        $mockLogger = $this->createMock(Logger::class);
        $this->registry->addLogger('test', $mockLogger);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Logger with name "test" already exists.');

        $this->registry->addLogger('test', $mockLogger);
    }

    public function testRemoveNonexistentLogger(): void
    {
        $this->expectException(LoggerNotFoundException::class);
        $this->expectExceptionMessage('Logger with name "nonexistent" not found.');

        $this->registry->removeLogger('nonexistent');
    }
}
