<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Logger;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\Formatter\JsonFormatter;
use KaririCode\Logging\LoggerFactory;
use PHPUnit\Framework\TestCase;

class LoggerFactoryTest extends TestCase
{
    public function testCreateLogger(): void
    {
        $config = [
            'formatter' => ['class' => JsonFormatter::class],
            'path' => '/tmp/test_log.log',
            'level' => 'debug',
        ];

        $logger = LoggerFactory::createLogger('test', $config);

        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateQueryLogger(): void
    {
        $logger = LoggerFactory::createQueryLogger('test_channel', 100);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreatePerformanceLogger(): void
    {
        $logger = LoggerFactory::createPerformanceLogger('test_channel', 1000);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateErrorLogger(): void
    {
        $logger = LoggerFactory::createErrorLogger('test_channel', ['error', 'critical']);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateAsyncLogger(): void
    {
        $logger = LoggerFactory::createAsyncLogger('async_driver', 10);
        $this->assertInstanceOf(Logger::class, $logger);
    }
}
