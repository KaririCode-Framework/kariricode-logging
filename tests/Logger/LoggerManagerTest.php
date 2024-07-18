<?php

declare(strict_types=1);

namespace KaririCode\Logging\Test\Logger;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Contract\Logging\LogFormatter;
use KaririCode\Contract\Logging\LogHandler;
use KaririCode\Contract\Logging\Structural\FormatterAware;
use KaririCode\Contract\Logging\Structural\HandlerAware;
use KaririCode\Contract\Logging\Structural\ProcessorAware;
use KaririCode\Logging\Formatter\LineFormatter;
use KaririCode\Logging\Handler\AbstractHandler;
use KaririCode\Logging\Handler\ConsoleHandler;
use KaririCode\Logging\Handler\NullHandler;
use KaririCode\Logging\LoggerManager;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Processor\AbstractProcessor;
use PHPUnit\Framework\TestCase;

class LoggerManagerTest extends TestCase
{
    private LoggerManager $loggerManager;

    protected function setUp(): void
    {
        $this->loggerManager = new LoggerManager('test_logger');
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(LoggerManager::class, $this->loggerManager);
        $this->assertEquals('test_logger', $this->loggerManager->getName());
        $this->assertInstanceOf(LineFormatter::class, $this->loggerManager->getFormatter());
    }

    public function testAddHandler(): void
    {
        $handler = $this->createMock(HandlerAware::class);
        $this->loggerManager->addHandler($handler);
        $this->assertCount(1, $this->loggerManager->getHandlers());
        $this->assertSame($handler, $this->loggerManager->getHandlers()[0]);
    }

    public function testAddProcessor(): void
    {
        $processor = $this->createMock(ProcessorAware::class);
        $this->loggerManager->addProcessor($processor);
        $this->assertCount(1, $this->loggerManager->getProcessors());
        $this->assertSame($processor, $this->loggerManager->getProcessors()[0]);
    }

    public function testSetFormatter(): void
    {
        $formatter = $this->createMock(LogFormatter::class);
        $this->loggerManager->setFormatter($formatter);
        $this->assertSame($formatter, $this->loggerManager->getFormatter());
    }

    public function testLog(): void
    {
        // Mock the ConsoleHandler
        $mockHandler = $this->createMock(AbstractHandler::class);
        // Set expectations for the handle method
        $mockHandler->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(ImmutableValue::class));


        $processor = $this->createMock(AbstractProcessor::class);
        $processor->expects($this->once())
            ->method('process')
            ->willReturnCallback(function (LogRecord $record) {
                return $record;
            });

        $this->loggerManager->addHandler($mockHandler);
        $this->loggerManager->addProcessor($processor);
        $this->loggerManager->log(LogLevel::INFO, 'Test message', ['context' => 'test']);
    }
}
