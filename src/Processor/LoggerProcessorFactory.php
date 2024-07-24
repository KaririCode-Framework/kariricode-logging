<?php

declare(strict_types=1);

namespace KaririCode\Logging\Processor;

use KaririCode\Contract\Logging\Structural\ProcessorAware;
use KaririCode\Logging\Contract\Logging\LoggerConfigurableFactory;
use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\Util\ReflectionFactoryTrait;

class LoggerProcessorFactory implements LoggerConfigurableFactory
{
    use ReflectionFactoryTrait;

    private array $processorMap = [];
    private array $channelConfigs = [];

    public function initializeFromConfiguration(LoggerConfiguration $config): void
    {
        $this->processorMap = $config->get('processors', [
            'introspection' => IntrospectionProcessor::class,
            'memory_usage_processor' => Metric\MemoryUsageProcessor::class,
            'execution_time_processor' => Metric\ExecutionTimeProcessor::class,
            'cpu_usage_processor' => Metric\CpuUsageProcessor::class,
            'metrics_processor' => MetricsProcessor::class,
            'web_processor' => WebProcessor::class,
        ]);

        $this->channelConfigs = $config->get('channels', []);
    }

    public function createProcessors(string $channelName): array
    {
        $channelConfig = $this->channelConfigs[$channelName] ?? [];
        $processorsConfig = $channelConfig['processors'] ?? [];
        $processors = [];
        foreach ($processorsConfig as $key => $value) {
            [$processorName, $processorOptions] = $this->extractMergedConfig($key, $value);

            $processors[] = $this->createProcessor($processorName, $processorOptions);
        }

        return $processors;
    }

    private function createProcessor(string $processorName, array $processorOptions): ProcessorAware
    {
        $processorClass = $this->getClassFromMap($this->processorMap, $processorName);
        $processorConfig = $this->getProcessorConfig($processorName, $processorOptions);

        return $this->createInstance($processorClass, $processorConfig);
    }

    private function getProcessorConfig(string $processorName, array $channelConfig): array
    {
        $defaultConfig = $this->processorMap[$processorName]['with'] ?? [];

        return array_merge($defaultConfig, $channelConfig);
    }
}
