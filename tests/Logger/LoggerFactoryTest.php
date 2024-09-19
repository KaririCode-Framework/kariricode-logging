<?php

declare(strict_types=1);

namespace Tests\KaririCode\Logging;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\Decorator\AsyncLogger;
use KaririCode\Logging\Formatter\LoggerFormatterFactory;
use KaririCode\Logging\Handler\LoggerHandlerFactory;
use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\LoggerFactory;
use KaririCode\Logging\LoggerManager;
use KaririCode\Logging\Processor\LoggerProcessorFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LoggerFactoryTest extends TestCase
{
    private LoggerConfiguration|MockObject $config;
    private LoggerHandlerFactory|MockObject $handlerFactory;
    private LoggerProcessorFactory|MockObject $processorFactory;
    private LoggerFormatterFactory|MockObject $formatterFactory;
    private LoggerFactory $loggerFactory;

    protected function setUp(): void
    {
        /** @var LoggerConfiguration */
        $this->config = $this->createMock(LoggerConfiguration::class);
        /** @var LoggerHandlerFactory */
        $this->handlerFactory = $this->createMock(LoggerHandlerFactory::class);
        /** @var LoggerProcessorFactory */
        $this->processorFactory = $this->createMock(LoggerProcessorFactory::class);
        /** @var LoggerFormatterFactory */
        $this->formatterFactory = $this->createMock(LoggerFormatterFactory::class);

        $this->loggerFactory = new LoggerFactory(
            $this->config,
            $this->handlerFactory,
            $this->processorFactory,
            $this->formatterFactory
        );
    }

    public function testCreateLogger(): void
    {
        $channelName = 'test_channel';

        $this->handlerFactory->expects($this->once())
            ->method('createHandlers')
            ->with($channelName)
            ->willReturn([]);

        $this->processorFactory->expects($this->once())
            ->method('createProcessors')
            ->with($channelName)
            ->willReturn([]);

        $this->formatterFactory->expects($this->once())
            ->method('createFormatter')
            ->with($channelName)
            ->willReturn($this->createMock(\KaririCode\Contract\Logging\LogFormatter::class));

        $logger = $this->loggerFactory->createLogger($channelName);

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertInstanceOf(LoggerManager::class, $logger);
    }

    public function testCreatePerformanceLogger(): void
    {
        $this->config->expects($this->once())
            ->method('get')
            ->with('performance.threshold', 1000)
            ->willReturn(500);

        $this->handlerFactory->method('createHandlers')->willReturn([]);
        $this->processorFactory->method('createProcessors')->willReturn([]);
        $this->formatterFactory->method('createFormatter')->willReturn($this->createMock(\KaririCode\Contract\Logging\LogFormatter::class));

        $logger = $this->loggerFactory->createPerformanceLogger();

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertInstanceOf(LoggerManager::class, $logger);
    }

    public function testCreateQueryLogger(): void
    {
        $this->config->expects($this->once())
            ->method('get')
            ->with('query.threshold', 100)
            ->willReturn(50);

        $this->handlerFactory->method('createHandlers')->willReturn([]);
        $this->processorFactory->method('createProcessors')->willReturn([]);
        $this->formatterFactory->method('createFormatter')->willReturn($this->createMock(\KaririCode\Contract\Logging\LogFormatter::class));

        $logger = $this->loggerFactory->createQueryLogger();

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertInstanceOf(LoggerManager::class, $logger);
    }

    public function testCreateErrorLogger(): void
    {
        $this->handlerFactory->method('createHandlers')->willReturn([]);
        $this->processorFactory->method('createProcessors')->willReturn([]);
        $this->formatterFactory->method('createFormatter')->willReturn($this->createMock(\KaririCode\Contract\Logging\LogFormatter::class));

        $logger = $this->loggerFactory->createErrorLogger();

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertInstanceOf(LoggerManager::class, $logger);
    }

    public function testCreateAsyncLogger(): void
    {
        /** @var Logger */
        $baseLogger = $this->createMock(Logger::class);
        $batchSize = 10;

        $asyncLogger = $this->loggerFactory->createAsyncLogger(
            $baseLogger,
            $batchSize
        );

        $this->assertInstanceOf(AsyncLogger::class, $asyncLogger);
    }

    public function testCreateLoggerWithInvalidChannel(): void
    {
        $this->handlerFactory->method('createHandlers')->willThrowException(new \InvalidArgumentException('Invalid channel'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid channel');

        $this->loggerFactory->createLogger('invalid_channel');
    }
}
