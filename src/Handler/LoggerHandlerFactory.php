<?php

declare(strict_types=1);

namespace KaririCode\Logging\Handler;

use KaririCode\Contract\Logging\LogHandler;
use KaririCode\Logging\Contract\LoggerConfigurableFactory;
use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\Util\ReflectionFactoryTrait;

class LoggerHandlerFactory implements LoggerConfigurableFactory
{
    use ReflectionFactoryTrait;

    private array $handlerMap = [];
    private LoggerConfiguration $config;

    public function initializeFromConfiguration(LoggerConfiguration $config): void
    {
        $this->handlerMap = $config->get('handlers', []);

        $this->config = $config;
    }

    public function createHandlers(string $handlerName): array
    {
        $handlersConfig = $this->getHandlersConfig($handlerName);

        $handlers = [];
        foreach ($handlersConfig as $key => $value) {
            [$handlerName, $handlerOptions] = $this->extractMergedConfig($key, $value);
            $handlers[] = $this->createHandler($handlerName, $handlerOptions);
        }

        return $handlers;
    }

    private function getHandlersConfig(string $handlerName): array
    {
        $channelHandlerConfig = $this->getChannelHandlersConfig($handlerName);
        $optionalHandlerConfig = $this->getOptionalHandlersConfig($handlerName);

        return $channelHandlerConfig ?? $optionalHandlerConfig ?? [];
    }

    private function getChannelHandlersConfig(string $handlerName): ?array
    {
        $channelConfigs = $this->config->get('channels', []);

        return $channelConfigs[$handlerName]['handlers'] ?? null;
    }

    private function getOptionalHandlersConfig(string $handlerName): ?array
    {
        $optionalHandlerConfigs = $this->config->get($handlerName, []);

        if (!self::isOptionalHandlerEnabled($optionalHandlerConfigs)) {
            return [];
        }

        return $optionalHandlerConfigs['handlers'] ?? $this->getChannelHandlersConfig(
            $optionalHandlerConfigs['channel'] ?? 'file'
        );
    }

    private static function isOptionalHandlerEnabled(array $optionalHandlerConfigs): bool
    {
        return isset($optionalHandlerConfigs['enabled']) && $optionalHandlerConfigs['enabled'];
    }

    private function createHandler(string $handlerName, array $handlerOptions): LogHandler
    {
        $handlerClass = $this->getClassFromMap($this->handlerMap, $handlerName);
        $handlerConfig = $this->getHandlerConfig($handlerName, $handlerOptions);

        $channelConfig = $this->config->get("channels.$handlerName", []);
        $handlerConfig['minLevel'] = LogLevel::from($channelConfig['minLevel'] ?? LogLevel::DEBUG->value);

        return $this->createInstance($handlerClass, $handlerConfig);
    }

    private function getHandlerConfig(string $handlerName, array $handlerOptions): array
    {
        $defaultConfig = $this->handlerMap[$handlerName]['with'] ?? [];

        return array_merge($defaultConfig, $handlerOptions);
    }
}
