<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Logger;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\LoggerRegistry;
use PHPUnit\Framework\TestCase;

class LoggerRegistryTest extends TestCase
{
    public function testAddAndRetrieveLogger(): void
    {
        $logger = $this->createMock(Logger::class);
        LoggerRegistry::addLogger('test', $logger);

        $retrievedLogger = LoggerRegistry::getLogger('test');
        $this->assertSame($logger, $retrievedLogger);
    }

    public function testRemoveLogger(): void
    {
        $logger = $this->createMock(Logger::class);
        LoggerRegistry::addLogger('test', $logger);
        LoggerRegistry::removeLogger('test');

        $retrievedLogger = LoggerRegistry::getLogger('test');
        $this->assertNull($retrievedLogger);
    }
}
