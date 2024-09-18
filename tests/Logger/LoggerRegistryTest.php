<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Logger;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\Exception\LoggerNotFoundException;
use KaririCode\Logging\LoggerRegistry;
use PHPUnit\Framework\TestCase;

final class LoggerRegistryTest extends TestCase
{
    private LoggerRegistry $registry;
    protected Logger $mockLogger;

    protected function setUp(): void
    {
        $this->registry = new LoggerRegistry();
        $this->mockLogger = $this->createMock(Logger::class);
    }

    public function testAddAndGetLogger(): void
    {
        $this->registry->addLogger('test', $this->mockLogger);

        $this->assertSame($this->mockLogger, $this->registry->getLogger('test'));
    }

    public function testGetNonexistentLogger(): void
    {
        $this->expectException(LoggerNotFoundException::class);
        $this->expectExceptionMessage('Logger with name "nonexistent" not found.');

        $this->registry->getLogger('nonexistent');
    }

    public function testRemoveLogger(): void
    {
        $this->registry->addLogger('test', $this->mockLogger);
        $this->registry->removeLogger('test');

        $this->expectException(LoggerNotFoundException::class);
        $this->expectExceptionMessage('Logger with name "test" not found.');

        $this->registry->getLogger('test');
    }

    public function testCannotAddLoggerWithSameNameTwice(): void
    {
        $this->registry->addLogger('test', $this->mockLogger);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Logger with name "test" already exists.');

        $this->registry->addLogger('test', $this->mockLogger);
    }

    public function testRemoveNonexistentLogger(): void
    {
        $this->expectException(LoggerNotFoundException::class);
        $this->expectExceptionMessage('Logger with name "nonexistent" not found.');

        $this->registry->removeLogger('nonexistent');
    }
}
