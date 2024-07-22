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
    private LoggerServiceProvider|MockObject $serviceProvider;
    private LoggerConfiguration|MockObject $mockConfig;
    private LoggerFactory|MockObject $mockFactory;
    private LoggerRegistry|MockObject $mockRegistry;

    protected function setUp(): void
    {
        $this->mockConfig = $this->createMock(LoggerConfiguration::class);
        $this->mockFactory = $this->createMock(LoggerFactory::class);
        $this->mockRegistry = $this->createMock(LoggerRegistry::class);

        $this->serviceProvider = new LoggerServiceProvider(
            $this->mockConfig,
            $this->mockFactory,
            $this->mockRegistry
        );
    }

    public function testRegister(): void
    {
        $this->mockConfig->method('get')
            ->willReturnMap([
                ['default', null, 'file'],
                ['channels', [], ['file' => ['path' => '/var/log/app.log']]],
                ['emergency_logger', [], ['path' => '/var/log/emergency.log']],
                ['query_logger.enabled', false, true],
                ['query_logger', [], ['path' => '/var/log/query.log']],
                ['performance_logger.enabled', false, false],
                ['error_logger.enabled', true, true],
                ['error_logger', [], ['path' => '/var/log/error.log']],
                ['async.enabled', false, true],
                ['async.batch_size', 10, 20],
            ]);

        $mockLogger = $this->createMock(Logger::class);

        $this->mockFactory->method('createLogger')->willReturn($mockLogger);
        $this->mockFactory->method('createQueryLogger')->willReturn($mockLogger);
        $this->mockFactory->method('createErrorLogger')->willReturn($mockLogger);
        $this->mockFactory->method('createAsyncLogger')->willReturn($mockLogger);

        $this->mockRegistry->method('getLogger')->willReturn($mockLogger);

        $expectedCalls = [
            ['file', $mockLogger],
            ['default', $mockLogger],
            ['emergency', $mockLogger],
            ['query', $mockLogger],
            ['error', $mockLogger],
            ['async', $mockLogger]
        ];

        $actualCalls = [];
        $this->mockRegistry->method('addLogger')
            ->willReturnCallback(function ($name, $logger) use (&$actualCalls) {
                $actualCalls[] = [$name, $logger];
            });

        $this->serviceProvider->register();

        $this->assertEquals($expectedCalls, $actualCalls);
    }
}
