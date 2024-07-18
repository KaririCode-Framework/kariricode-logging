<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Decorator;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\Decorator\BaseLoggerDecorator;
use KaririCode\Logging\LogLevel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BaseLoggerDecoratorTest extends TestCase
{
    private BaseLoggerDecorator $baseLoggerDecorator;
    private Logger|MockObject $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(Logger::class);
        $this->baseLoggerDecorator = new class($this->logger) extends BaseLoggerDecorator {
        };
    }

    public function testLogHappyPath(): void
    {
        $level = LogLevel::INFO;
        $message = 'Test message';
        $context = ['key' => 'value'];

        $this->logger->expects($this->once())
            ->method('log')
            ->with($level, $message, $context);

        $this->baseLoggerDecorator->log($level, $message, $context);
    }

    public function testLogThrowsException(): void
    {
        $this->expectException(\Exception::class);

        $level = LogLevel::INFO;
        $message = 'Test message';
        $context = ['key' => 'value'];

        $this->logger->method('log')
            ->willThrowException(new \Exception());

        $this->baseLoggerDecorator->log($level, $message, $context);
    }
}
