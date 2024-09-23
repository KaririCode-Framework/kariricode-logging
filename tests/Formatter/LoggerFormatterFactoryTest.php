<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Formatter;

use KaririCode\Logging\Formatter\JsonFormatter;
use KaririCode\Logging\Formatter\LineFormatter;
use KaririCode\Logging\Formatter\LoggerFormatterFactory;
use KaririCode\Logging\LoggerConfiguration;
use PHPUnit\Framework\TestCase;

final class LoggerFormatterFactoryTest extends TestCase
{
    private LoggerFormatterFactory $factory;
    private LoggerConfiguration|\PHPUnit\Framework\MockObject\MockObject $configMock;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(LoggerConfiguration::class);
        $this->factory = new LoggerFormatterFactory();
    }

    public function testInitializeFromConfiguration(): void
    {
        $config = [
            'formatters' => [
                'line' => LineFormatter::class,
                'json' => JsonFormatter::class,
                'custom' => 'CustomFormatter',
            ],
            'channels' => [
                'default' => ['formatter' => 'line'],
                'api' => ['formatter' => ['json' => ['with' => ['prettyPrint' => true]]]],
            ],
        ];

        $this->configMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['formatters', ['line' => LineFormatter::class, 'json' => JsonFormatter::class], $config['formatters']],
                ['channels', [], $config['channels']],
            ]);

        $this->factory->initializeFromConfiguration($this->configMock);

        $this->assertInstanceOf(LoggerFormatterFactory::class, $this->factory);
    }

    public function testCreateFormatterWithDefaultFormatter(): void
    {
        $config = [
            'formatters' => [
                'line' => LineFormatter::class,
            ],
            'channels' => [
                'default' => ['formatter' => 'line'],
            ],
        ];

        $this->configMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['formatters', ['line' => LineFormatter::class, 'json' => JsonFormatter::class], $config['formatters']],
                ['channels', [], $config['channels']],
            ]);

        $this->factory->initializeFromConfiguration($this->configMock);

        $formatter = $this->factory->createFormatter('default');
        $this->assertInstanceOf(LineFormatter::class, $formatter);
    }

    public function testCreateFormatterWithCustomFormatter(): void
    {
        $config = [
            'formatters' => [
                'json' => JsonFormatter::class,
            ],
            'channels' => [
                'api' => ['formatter' => ['json' => ['with' => ['prettyPrint' => true]]]],
            ],
        ];

        $this->configMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['formatters', ['line' => LineFormatter::class, 'json' => JsonFormatter::class], $config['formatters']],
                ['channels', [], $config['channels']],
            ]);

        $this->factory->initializeFromConfiguration($this->configMock);

        $formatter = $this->factory->createFormatter('api');
        $this->assertInstanceOf(JsonFormatter::class, $formatter);
    }

    public function testCreateFormatterWithNonExistentFormatter(): void
    {
        $config = [
            'formatters' => [
                'line' => LineFormatter::class,
                'json' => JsonFormatter::class,
            ],
            'channels' => [
                'unknown' => ['formatter' => 'non_existent'],
            ],
        ];

        $this->configMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['formatters', ['line' => LineFormatter::class, 'json' => JsonFormatter::class], $config['formatters']],
                ['channels', [], $config['channels']],
            ]);

        $this->factory->initializeFromConfiguration($this->configMock);

        $this->expectException(\KaririCode\Logging\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('Configuration not found for key: non_existent');

        $this->factory->createFormatter('unknown');
    }

    public function testCreateFormatterWithFallbackToDefaultFormatter(): void
    {
        $config = [
            'formatters' => [
                'line' => LineFormatter::class,
            ],
            'channels' => [
                'fallback' => [],
            ],
        ];

        $this->configMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['formatters', ['line' => LineFormatter::class, 'json' => JsonFormatter::class], $config['formatters']],
                ['channels', [], $config['channels']],
            ]);

        $this->factory->initializeFromConfiguration($this->configMock);

        $formatter = $this->factory->createFormatter('fallback');
        $this->assertInstanceOf(LineFormatter::class, $formatter);
    }
}
