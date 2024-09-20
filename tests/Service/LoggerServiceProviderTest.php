<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Logging\Service;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\Exception\InvalidConfigurationException;
use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\LoggerFactory;
use KaririCode\Logging\LoggerRegistry;
use KaririCode\Logging\Service\LoggerServiceProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LoggerServiceProviderTest extends TestCase
{
    private LoggerConfiguration|MockObject $config;
    private LoggerFactory|MockObject $loggerFactory;
    private LoggerRegistry|MockObject $loggerRegistry;
    private LoggerServiceProvider $serviceProvider;

    protected function setUp(): void
    {
        $this->config = $this->createMock(LoggerConfiguration::class);
        $this->loggerFactory = $this->createMock(LoggerFactory::class);
        $this->loggerRegistry = $this->createMock(LoggerRegistry::class);

        $this->serviceProvider = new LoggerServiceProvider(
            $this->config,
            $this->loggerFactory,
            $this->loggerRegistry
        );
    }

    public function testRegister(): void
    {
        $this->config->method('get')
            ->willReturnMap([
                ['default', null, 'default_channel'],
                ['channels', null, ['channel1' => [], 'channel2' => []]],
                ['emergency_logger', [], []],
                ['query', [], []],
                ['performance', [], []],
                ['error', [], []],
                ['async.batch_size', 10, 10],
            ]);

        $mockLogger = $this->createMock(Logger::class);
        $this->loggerFactory->method('createLogger')->willReturn($mockLogger);
        $this->loggerFactory->method('createQueryLogger')->willReturn($mockLogger);
        $this->loggerFactory->method('createPerformanceLogger')->willReturn($mockLogger);
        $this->loggerFactory->method('createErrorLogger')->willReturn($mockLogger);
        $this->loggerFactory->method('createAsyncLogger')->willReturn($mockLogger);

        $this->loggerRegistry->method('getLogger')->willReturn($mockLogger);

        $this->loggerRegistry->expects($this->atLeastOnce())
            ->method('addLogger');

        $this->serviceProvider->register();
    }

    public function testRegisterThrowsExceptionWhenDefaultChannelIsMissing(): void
    {
        $this->config->method('get')
            ->willReturnMap([
                ['default', null, null],
                ['channels', null, ['channel1' => []]],
            ]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage("The 'default' and 'channels' configurations are required.");

        $this->serviceProvider->register();
    }

    public function testRegisterThrowsExceptionWhenChannelsConfigIsMissing(): void
    {
        $this->config->method('get')
            ->willReturnMap([
                ['default', null, 'default_channel'],
                ['channels', null, null],
            ]);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage("The 'default' and 'channels' configurations are required.");

        $this->serviceProvider->register();
    }

    public function testRegisterDefaultLoggers(): void
    {
        $this->config->method('get')
            ->willReturnMap([
                ['default', null, 'default_channel'],
                ['channels', null, ['channel1' => [], 'default_channel' => []]],
            ]);

        $logger = $this->createMock(Logger::class);
        $this->loggerFactory->method('createLogger')->willReturn($logger);

        $this->loggerRegistry->expects($this->exactly(3))
            ->method('addLogger');

        $method = new \ReflectionMethod(LoggerServiceProvider::class, 'registerDefaultLoggers');
        $method->setAccessible(true);
        $method->invoke($this->serviceProvider);
    }

    public function testRegisterEmergencyLogger(): void
    {
        $this->config->method('get')
            ->willReturnMap([
                ['emergency_logger', [], ['config' => 'value']],
            ]);

        $emergencyLogger = $this->createMock(Logger::class);
        $this->loggerFactory->method('createLogger')
            ->with('emergency', ['config' => 'value'])
            ->willReturn($emergencyLogger);

        $this->loggerRegistry->expects($this->once())
            ->method('addLogger')
            ->with('emergency', $emergencyLogger);

        $method = new \ReflectionMethod(LoggerServiceProvider::class, 'registerEmergencyLogger');
        $method->setAccessible(true);
        $method->invoke($this->serviceProvider);
    }

    public function testRegisterOptionalLoggers(): void
    {
        $this->config->method('get')
            ->willReturnMap([
                ['query', [], ['query_config' => 'value']],
                ['performance', [], ['performance_config' => 'value']],
                ['error', [], ['error_config' => 'value']],
                ['async.batch_size', 10, 20],
            ]);

        $mockLogger = $this->createMock(Logger::class);
        $this->loggerFactory->method('createQueryLogger')->willReturn($mockLogger);
        $this->loggerFactory->method('createPerformanceLogger')->willReturn($mockLogger);
        $this->loggerFactory->method('createErrorLogger')->willReturn($mockLogger);

        // Mock the getLogger method to return a Logger instance
        $this->loggerRegistry->method('getLogger')
            ->with('default')
            ->willReturn($mockLogger);

        // Expect createAsyncLogger to be called with a Logger instance and batch size
        $this->loggerFactory->expects($this->once())
            ->method('createAsyncLogger')
            ->with($this->isInstanceOf(Logger::class), 20)
            ->willReturn($mockLogger);

        $this->loggerRegistry->expects($this->exactly(4))
            ->method('addLogger');

        $method = new \ReflectionMethod(LoggerServiceProvider::class, 'registerOptionalLoggers');
        $method->setAccessible(true);
        $method->invoke($this->serviceProvider);
    }

    public function testRegisterLogger(): void
    {
        $this->config->method('get')
            ->willReturnMap([
                ['test_logger', [], ['config' => 'value']],
            ]);

        $logger = $this->createMock(Logger::class);
        $this->loggerFactory->method('createQueryLogger')
            ->with(['config' => 'value'])
            ->willReturn($logger);

        $this->loggerRegistry->expects($this->once())
            ->method('addLogger')
            ->with('test_logger', $logger);

        $method = new \ReflectionMethod(LoggerServiceProvider::class, 'registerLogger');
        $method->setAccessible(true);
        $method->invoke($this->serviceProvider, 'test_logger', 'createQueryLogger');
    }

    public function testRegisterAsyncLoggerIfEnabled(): void
    {
        $defaultLogger = $this->createMock(Logger::class);
        $this->loggerRegistry->method('getLogger')
            ->with('default')
            ->willReturn($defaultLogger);

        $this->config->method('get')
            ->with('async.batch_size', 10)
            ->willReturn(20);

        $asyncLogger = $this->createMock(Logger::class);
        $this->loggerFactory->method('createAsyncLogger')
            ->with($defaultLogger, 20)
            ->willReturn($asyncLogger);

        $this->loggerRegistry->expects($this->once())
            ->method('addLogger')
            ->with('async', $asyncLogger);

        $method = new \ReflectionMethod(LoggerServiceProvider::class, 'registerAsyncLoggerIfEnabled');
        $method->setAccessible(true);
        $method->invoke($this->serviceProvider);
    }
}
