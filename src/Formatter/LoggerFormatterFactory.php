<?php

declare(strict_types=1);

namespace KaririCode\Logging\Formatter;

use KaririCode\Contract\Logging\LogFormatter;
use KaririCode\Logging\Contract\LoggerConfigurableFactory;
use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\Util\ReflectionFactoryTrait;

class LoggerFormatterFactory implements LoggerConfigurableFactory
{
    use ReflectionFactoryTrait;

    private array $formatterMap = [];
    private array $channelConfigs = [];

    public function initializeFromConfiguration(LoggerConfiguration $config): void
    {
        $this->formatterMap = $config->get('formatters', [
            'line' => LineFormatter::class,
            'json' => JsonFormatter::class,
        ]);
        $this->channelConfigs = $config->get('channels', []);
    }

    public function createFormatter(string $channelName): LogFormatter
    {
        $channelConfig = $this->channelConfigs[$channelName] ?? [];
        $formatterConfig = $channelConfig['formatter'] ?? 'line';

        [$formatterType, $formatterOptions] = $this->extractMergedConfig($formatterConfig);

        $formatterClass = $this->getClassFromMap($this->formatterMap, $formatterType);

        return $this->createInstance($formatterClass, $formatterOptions);
    }

    private function extractMergedConfig($config): array
    {
        if (is_string($config)) {
            return [$config, []];
        }

        $type = key($config);
        $options = $config[$type]['with'] ?? [];

        return [$type, $options];
    }
}
