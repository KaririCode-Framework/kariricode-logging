<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Logger;

use KaririCode\Contract\Logging\Logger;
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
        $this->assertNull($this->registry->getLogger('nonexistent'));
    }

    public function testRemoveLogger(): void
    {
        $mockLogger = $this->createMock(Logger::class);
        $this->registry->addLogger('test', $mockLogger);
        $this->registry->removeLogger('test');

        $this->assertNull($this->registry->getLogger('test'));
    }
}
