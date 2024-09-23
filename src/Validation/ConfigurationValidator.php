<?php

declare(strict_types=1);

namespace KaririCode\Logging\Validation;

use KaririCode\Logging\Exception\InvalidConfigurationException;

class ConfigurationValidator
{
    private const REQUIRED_KEYS = [
        'default',
        'channels',
        'handlers',
        'processors',
        'formatters',
    ];
    private const CHANNEL_REQUIRED_KEYS = [
        'handlers',
    ];
    private const HANDLER_REQUIRED_KEYS = [
        'class',
    ];
    private const PROCESSOR_REQUIRED_KEYS = [
        'class',
    ];
    private const FORMATTER_REQUIRED_KEYS = [
        'class',
    ];
    private const OPTIONAL_LOG_KEYS = [
        'enabled',
        'channel',
    ];
    private const OPTIONAL_LOGS = [
        'query',
        'performance',
        'error',
    ];

    public function validate(array $config): void
    {
        $this->validateRequiredKeys($config, self::REQUIRED_KEYS);
        $this->validateChannels($config['channels']);
        $this->validateHandlers($config['handlers']);
        $this->validateProcessors($config['processors']);
        $this->validateFormatters($config['formatters']);
        $this->validateOptionalLogs($config);
    }

    private function validateRequiredKeys(array $config, array $requiredKeys, string $context = ''): void
    {
        foreach ($requiredKeys as $key) {
            if (!isset($config[$key])) {
                throw new InvalidConfigurationException("Missing required key '{$key}' in configuration" . ($context ? " for {$context}" : ''));
            }
        }
    }

    private function validateChannels(array $channels): void
    {
        foreach ($channels as $channelName => $channelConfig) {
            $this->validateRequiredKeys($channelConfig, self::CHANNEL_REQUIRED_KEYS, "channel '{$channelName}'");

            if (!is_array($channelConfig['handlers'])) {
                throw new InvalidConfigurationException("Handlers for channel '{$channelName}' must be an array");
            }
        }
    }

    private function validateHandlers(array $handlers): void
    {
        foreach ($handlers as $handlerName => $handlerConfig) {
            $this->validateRequiredKeys($handlerConfig, self::HANDLER_REQUIRED_KEYS, "handler '{$handlerName}'");

            if (!class_exists($handlerConfig['class'])) {
                throw new InvalidConfigurationException("Handler class '{$handlerConfig['class']}' for '{$handlerName}' does not exist");
            }
        }
    }

    private function validateProcessors(array $processors): void
    {
        foreach ($processors as $processorName => $processorConfig) {
            $this->validateRequiredKeys($processorConfig, self::PROCESSOR_REQUIRED_KEYS, "processor '{$processorName}'");

            if (!class_exists($processorConfig['class'])) {
                throw new InvalidConfigurationException("Processor class '{$processorConfig['class']}' for '{$processorName}' does not exist");
            }
        }
    }

    private function validateFormatters(array $formatters): void
    {
        foreach ($formatters as $formatterName => $formatterConfig) {
            $this->validateRequiredKeys($formatterConfig, self::FORMATTER_REQUIRED_KEYS, "formatter '{$formatterName}'");

            if (!class_exists($formatterConfig['class'])) {
                throw new InvalidConfigurationException("Formatter class '{$formatterConfig['class']}' for '{$formatterName}' does not exist");
            }
        }
    }

    private function validateOptionalLogs(array $config): void
    {
        foreach (self::OPTIONAL_LOGS as $log) {
            if (isset($config[$log])) {
                $this->validateRequiredKeys($config[$log], self::OPTIONAL_LOG_KEYS, "optional log '{$log}'");

                if (isset($config[$log]['handlers']) && !is_array($config[$log]['handlers'])) {
                    throw new InvalidConfigurationException("Handlers for optional log '{$log}' must be an array");
                }

                if (isset($config[$log]['processors']) && !is_array($config[$log]['processors'])) {
                    throw new InvalidConfigurationException("Processors for optional log '{$log}' must be an array");
                }
            }
        }
    }
}
