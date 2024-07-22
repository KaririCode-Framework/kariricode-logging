<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Logger;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\LoggerFactory;
use KaririCode\Logging\LogLevel;
use PHPUnit\Framework\TestCase;

class LoggerFactoryTest extends TestCase
{
    private LoggerFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new LoggerFactory();
    }

    public function testCreateLogger(): void
    {
        $config = [
            'path' => '/var/log/app.log',
            'level' => LogLevel::DEBUG,
        ];

        $logger = $this->factory->createLogger('test', $config);

        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateQueryLogger(): void
    {
        $config = [
            'path' => '/var/log/query.log',
            'level' => LogLevel::INFO,
        ];

        $logger = $this->factory->createQueryLogger($config);

        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateAsyncLogger(): void
    {
        $mockLogger = $this->createMock(Logger::class);
        $asyncLogger = $this->factory->createAsyncLogger($mockLogger, 10);

        $this->assertInstanceOf(Logger::class, $asyncLogger);
    }
}
