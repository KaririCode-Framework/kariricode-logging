<?php

namespace KaririCode\Logging\Tests\Logging\Processor;

use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\Processor\IntrospectionProcessor;
use KaririCode\Logging\Processor\LoggerProcessorFactory;
use KaririCode\Logging\Processor\Metric\CpuUsageProcessor;
use KaririCode\Logging\Processor\Metric\ExecutionTimeProcessor;
use KaririCode\Logging\Processor\Metric\MemoryUsageProcessor;
use KaririCode\Logging\Processor\MetricsProcessor;
use KaririCode\Logging\Processor\WebProcessor;
use PHPUnit\Framework\TestCase;

class LoggerProcessorFactoryTest extends TestCase
{
    private LoggerProcessorFactory $factory;
    private LoggerConfiguration $config;

    protected function setUp(): void
    {
        $this->config = $this->createMock(LoggerConfiguration::class);
        $this->factory = new LoggerProcessorFactory();
    }

    public function testInitializeFromConfiguration(): void
    {
        $processorMap = [
            'introspection' => IntrospectionProcessor::class,
            'memory_usage_processor' => MemoryUsageProcessor::class,
            'execution_time_processor' => ExecutionTimeProcessor::class,
            'cpu_usage_processor' => CpuUsageProcessor::class,
            'metrics_processor' => MetricsProcessor::class,
            'web_processor' => WebProcessor::class,
        ];

        $this->config->method('get')
            ->willReturnMap([
                ['processors', [], $processorMap],
            ]);

        $this->factory->initializeFromConfiguration($this->config);

        $reflection = new \ReflectionClass($this->factory);
        $property = $reflection->getProperty('processorMap');
        $property->setAccessible(true);

        $actualProcessorMap = $property->getValue($this->factory);
        $this->assertSame($processorMap, $actualProcessorMap);
    }

    public function testCreateProcessors(): void
    {
        $channelName = 'test_channel';
        $processorConfig = [
            'introspection' => [],
            'memory_usage_processor' => ['with' => ['threshold' => 1000]],
        ];

        $this->config->method('get')
            ->willReturnMap([
                ['processors', [], [
                    'introspection' => IntrospectionProcessor::class,
                    'memory_usage_processor' => MemoryUsageProcessor::class,
                ]],
                ['channels', [], [$channelName => ['processors' => $processorConfig]]],
                [$channelName, [], []],
            ]);

        $this->factory->initializeFromConfiguration($this->config);
        $processors = $this->factory->createProcessors($channelName);

        $this->assertCount(2, $processors);
        $this->assertInstanceOf(IntrospectionProcessor::class, $processors[0]);
        $this->assertInstanceOf(MemoryUsageProcessor::class, $processors[1]);
    }

    public function testCreateProcessorsWithOptionalConfig(): void
    {
        $channelName = 'optional_channel';
        $processorConfig = [
            'web_processor' => [],
        ];

        $this->config->method('get')
            ->willReturnMap([
                ['processors', [], ['web_processor' => WebProcessor::class]],
                ['channels', [], []],
                [$channelName, [], ['enabled' => true, 'processors' => $processorConfig]],
            ]);

        $this->factory->initializeFromConfiguration($this->config);
        $processors = $this->factory->createProcessors($channelName);

        $this->assertCount(1, $processors);
        $this->assertInstanceOf(WebProcessor::class, $processors[0]);
    }

    public function testGetProcessorMap(): void
    {
        $processorMap = [
            'introspection' => IntrospectionProcessor::class,
            'memory_usage_processor' => MemoryUsageProcessor::class,
        ];

        $this->config->method('get')
            ->willReturnMap([
                ['processors', [], $processorMap],
            ]);

        $this->factory->initializeFromConfiguration($this->config);

        $this->assertSame($processorMap, $this->factory->getProcessorMap());
    }
}
