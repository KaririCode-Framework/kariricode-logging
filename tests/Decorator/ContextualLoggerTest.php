<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Decorator;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\Decorator\ContextualLogger;
use KaririCode\Logging\LogLevel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ContextualLoggerTest extends TestCase
{
    private ContextualLogger $contextualLogger;
    private Logger|MockObject $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(Logger::class);
        $additionalContext = ['app' => 'testApp'];
        $this->contextualLogger = new ContextualLogger($this->logger, $additionalContext);
    }

    public function testLogHappyPath(): void
    {
        $level = LogLevel::INFO;
        $message = 'Test message';
        $context = ['key' => 'value'];
        $expectedContext = ['app' => 'testApp', 'key' => 'value'];

        $this->logger->expects($this->once())
            ->method('log')
            ->with($level, $message, $expectedContext);

        $this->contextualLogger->log($level, $message, $context);
    }

    public function testLogThrowsException(): void
    {
        $this->expectException(\Exception::class);

        $level = LogLevel::INFO;
        $message = 'Test message';
        $context = ['key' => 'value'];

        $this->logger->method('log')
            ->willThrowException(new \Exception());

        $this->contextualLogger->log($level, $message, $context);
    }
}
