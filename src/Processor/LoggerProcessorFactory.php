<?php

declare(strict_types=1);

namespace KaririCode\Logging\Processor;

use KaririCode\Contract\Logging\Structural\ProcessorAware;
use KaririCode\Logging\Contract\LoggerConfigurableFactory;
use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\Util\ReflectionFactoryTrait;

class LoggerProcessorFactory implements LoggerConfigurableFactory
{
    use ReflectionFactoryTrait;

    private array $processorMap = [];
    private LoggerConfiguration $config;

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

        $this->config = $config;
    }

    public function createProcessors(string $channelName): array
    {
        $processorsConfig = $this->getProcessorsConfig($channelName);
        $processors = [];
        foreach ($processorsConfig as $key => $value) {
            [$processorName, $processorOptions] = $this->extractMergedConfig($key, $value);
            $processors[] = $this->createProcessor($processorName, $processorOptions);
        }

        return $processors;
    }

    private function getProcessorsConfig(string $channelName): array
    {
        $channelProcessorConfig = $this->getChannelProcessorsConfig($channelName);
        $optionalProcessorConfig = $this->getOptionalProcessorsConfig($channelName);

        return array_merge($channelProcessorConfig ?? [], $optionalProcessorConfig ?? []);
    }

    private function getChannelProcessorsConfig(string $channelName): ?array
    {
        $channelConfigs = $this->config->get('channels', []);

        return $channelConfigs[$channelName]['processors'] ?? null;
    }

    private function getOptionalProcessorsConfig(string $channelName): ?array
    {
        $optionalProcessorConfigs = $this->config->get($channelName, []);

        if (!self::isOptionalProcessorEnabled($optionalProcessorConfigs)) {
            return [];
        }

        return $optionalProcessorConfigs['processors'] ?? $this->getChannelProcessorsConfig(
            $optionalProcessorConfigs['channel'] ?? 'file'
        );
    }

    private static function isOptionalProcessorEnabled(array $optionalProcessorConfigs): bool
    {
        return isset($optionalProcessorConfigs['enabled']) && $optionalProcessorConfigs['enabled'];
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
