<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Service;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\LoggerFactory;
use KaririCode\Logging\LoggerRegistry;
use KaririCode\Logging\Service\LoggerServiceProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoggerServiceProviderTest extends TestCase
{
    private LoggerConfiguration|MockObject $config;
    private LoggerFactory|MockObject $loggerFactory;
    private LoggerRegistry|MockObject $loggerRegistry;
    private LoggerServiceProvider|MockObject $serviceProvider;

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

    public function testRegisterAllLoggers(): void
    {
        // Arrange
        $configMap = [
            ['default', null, 'default'],
            ['channels', [], [
                'default' => ['path' => '/var/log/default.log', 'level' => 'debug'],
                'error' => ['path' => '/var/log/error.log', 'level' => 'error']
            ]],
            ['emergency_logger', [], ['path' => '/var/log/emergency.log', 'level' => 'emergency']],
            ['query_logger.enabled', false, true],
            ['query_logger', [], ['path' => '/var/log/query.log', 'level' => 'debug']],
            ['performance_logger.enabled', false, true],
            ['performance_logger', [], ['path' => '/var/log/performance.log', 'level' => 'info']],
            ['error_logger.enabled', true, true],
            ['error_logger', [], ['path' => '/var/log/error.log', 'level' => 'error']],
            ['async.enabled', true, true],
            ['async.batch_size', 10, 10],
        ];
        $this->config->method('get')->willReturnMap($configMap);

        $defaultLogger = $this->createMock(Logger::class);
        $errorLogger = $this->createMock(Logger::class);
        $emergencyLogger = $this->createMock(Logger::class);
        $queryLogger = $this->createMock(Logger::class);
        $performanceLogger = $this->createMock(Logger::class);
        $asyncLogger = $this->createMock(Logger::class);

        $this->loggerFactory->method('createLogger')
            ->willReturnMap([
                ['default', ['path' => '/var/log/default.log', 'level' => 'debug'], $defaultLogger],
                ['error', ['path' => '/var/log/error.log', 'level' => 'error'], $errorLogger],
                ['emergency', ['path' => '/var/log/emergency.log', 'level' => 'emergency'], $emergencyLogger],
            ]);

        $this->loggerFactory->method('createQueryLogger')
            ->willReturn($queryLogger);

        $this->loggerFactory->method('createPerformanceLogger')
            ->willReturn($performanceLogger);

        $this->loggerFactory->method('createErrorLogger')
            ->willReturn($errorLogger);

        $this->loggerFactory->method('createAsyncLogger')
            ->willReturn($asyncLogger);

        $expectedAddLoggerCalls = [
            ['default', $defaultLogger],
            ['default', $defaultLogger],
            ['error', $errorLogger],
            ['emergency', $emergencyLogger],
            ['query', $queryLogger],
            ['performance', $performanceLogger],
            ['error', $errorLogger],
            ['async', $asyncLogger]
        ];

        $this->loggerRegistry->expects($this->exactly(count($expectedAddLoggerCalls)))
            ->method('addLogger')
            ->willReturnCallback(function ($channel, $logger) use (&$expectedAddLoggerCalls) {
                $expectedCall = array_shift($expectedAddLoggerCalls);
                $this->assertEquals($expectedCall[0], $channel);
                $this->assertSame($expectedCall[1], $logger);
            });

        $this->loggerRegistry->method('getLogger')
            ->with('default')
            ->willReturn($defaultLogger);

        // Act
        $this->serviceProvider->register();

        // Assert
        $this->assertEmpty($expectedAddLoggerCalls, 'Not all expected addLogger calls were made');
    }

    public function testRegisterWithoutOptionalLoggers(): void
    {
        // Arrange
        $configMap = [
            ['default', null, 'default'],
            ['channels', [], [
                'default' => ['path' => '/var/log/default.log', 'level' => 'debug'],
                'error' => ['path' => '/var/log/error.log', 'level' => 'error']
            ]],
            ['emergency_logger', [], ['path' => '/var/log/emergency.log', 'level' => 'emergency']],
            ['query_logger.enabled', false, false],
            ['performance_logger.enabled', false, false],
            ['error_logger.enabled', true, false],
            ['async.enabled', true, false],
        ];
        $this->config->method('get')->willReturnMap($configMap);

        $defaultLogger = $this->createMock(Logger::class);
        $errorLogger = $this->createMock(Logger::class);
        $emergencyLogger = $this->createMock(Logger::class);

        $this->loggerFactory->method('createLogger')
            ->willReturnMap([
                ['default', ['path' => '/var/log/default.log', 'level' => 'debug'], $defaultLogger],
                ['error', ['path' => '/var/log/error.log', 'level' => 'error'], $errorLogger],
                ['emergency', ['path' => '/var/log/emergency.log', 'level' => 'emergency'], $emergencyLogger],
            ]);

        $expectedAddLoggerCalls = [
            ['default', $defaultLogger],
            ['default', $defaultLogger],
            ['error', $errorLogger],
            ['emergency', $emergencyLogger],
        ];

        $this->loggerRegistry->expects($this->exactly(count($expectedAddLoggerCalls)))
            ->method('addLogger')
            ->willReturnCallback(function ($channel, $logger) use (&$expectedAddLoggerCalls) {
                $expectedCall = array_shift($expectedAddLoggerCalls);
                $this->assertEquals($expectedCall[0], $channel);
                $this->assertSame($expectedCall[1], $logger);
            });

        // Act
        $this->serviceProvider->register();

        // Assert
        $this->assertEmpty($expectedAddLoggerCalls, 'Not all expected addLogger calls were made');
    }

    public function testRegisterWithoutDefaultChannel(): void
    {
        // Arrange
        $configMap = [
            ['default', null, null],
            ['channels', [], [
                'custom' => ['path' => '/var/log/custom.log', 'level' => 'debug'],
            ]],
            ['emergency_logger', [], ['path' => '/var/log/emergency.log', 'level' => 'emergency']],
            ['query_logger.enabled', false, false],
            ['performance_logger.enabled', false, false],
            ['error_logger.enabled', true, false],
            ['async.enabled', true, false],
        ];
        $this->config->method('get')->willReturnMap($configMap);

        $customLogger = $this->createMock(Logger::class);
        $emergencyLogger = $this->createMock(Logger::class);

        $this->loggerFactory->method('createLogger')
            ->willReturnMap([
                ['custom', ['path' => '/var/log/custom.log', 'level' => 'debug'], $customLogger],
                ['emergency', ['path' => '/var/log/emergency.log', 'level' => 'emergency'], $emergencyLogger],
            ]);

        $expectedAddLoggerCalls = [
            ['custom', $customLogger],
            ['emergency', $emergencyLogger],
        ];

        $this->loggerRegistry->expects($this->exactly(count($expectedAddLoggerCalls)))
            ->method('addLogger')
            ->willReturnCallback(function ($channel, $logger) use (&$expectedAddLoggerCalls) {
                $expectedCall = array_shift($expectedAddLoggerCalls);
                $this->assertEquals($expectedCall[0], $channel);
                $this->assertSame($expectedCall[1], $logger);
            });

        // Act
        $this->serviceProvider->register();

        // Assert
        $this->assertEmpty($expectedAddLoggerCalls, 'Not all expected addLogger calls were made');
    }

    public function testRegisterWithEmptyChannels(): void
    {
        // Arrange
        $configMap = [
            ['default', null, null],
            ['channels', [], []],
            ['emergency_logger', [], ['path' => '/var/log/emergency.log', 'level' => 'emergency']],
            ['query_logger.enabled', false, false],
            ['performance_logger.enabled', false, false],
            ['error_logger.enabled', true, false],
            ['async.enabled', true, false],
        ];
        $this->config->method('get')->willReturnMap($configMap);

        $emergencyLogger = $this->createMock(Logger::class);

        $this->loggerFactory->method('createLogger')
            ->willReturnMap([
                ['emergency', ['path' => '/var/log/emergency.log', 'level' => 'emergency'], $emergencyLogger],
            ]);

        $expectedAddLoggerCalls = [
            ['emergency', $emergencyLogger],
        ];

        $this->loggerRegistry->expects($this->exactly(count($expectedAddLoggerCalls)))
            ->method('addLogger')
            ->willReturnCallback(function ($channel, $logger) use (&$expectedAddLoggerCalls) {
                $expectedCall = array_shift($expectedAddLoggerCalls);
                $this->assertEquals($expectedCall[0], $channel);
                $this->assertSame($expectedCall[1], $logger);
            });

        // Act
        $this->serviceProvider->register();

        // Assert
        $this->assertEmpty($expectedAddLoggerCalls, 'Not all expected addLogger calls were made');
    }
}
