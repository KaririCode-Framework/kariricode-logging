<?php

declare(strict_types=1);

namespace KaririCode\Logging\Handler;

use InvalidArgumentException;
use KaririCode\Contract\Logging\LogHandler;
use KaririCode\Logging\Contract\Logging\LoggerConfigurableFactory;
use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\Util\ReflectionFactoryTrait;

class LoggerHandlerFactory implements LoggerConfigurableFactory
{
    use ReflectionFactoryTrait;

    private array $handlerMap = [];
    private LoggerConfiguration $config;


    public function initializeFromConfiguration(LoggerConfiguration $config): void
    {
        $this->handlerMap = $config->get('handlers', [
            'file' => FileHandler::class,
            'console' => ConsoleHandler::class,
            'slack' => SlackHandler::class,
            'syslog' => SyslogUdpHandler::class,
        ]);

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

    private function getHandlersConfig(string $channelName): array
    {
        $channelHandlerConfig = $this->getChannelHandlersConfig($channelName);
        $optionalHandlerConfig = $this->getOptionalHandlersConfig($channelName);

        return $channelHandlerConfig ?? $optionalHandlerConfig ?? [];
    }

    private function getChannelHandlersConfig(string $channelName): ?array
    {
        $channelConfigs = $this->config->get('channels', []);
        return $channelConfigs[$channelName]['handlers'] ?? null;
    }

    private function getOptionalHandlersConfig(string $channelName): ?array
    {
        $optionalHandlerConfigs = $this->config->get($channelName, []);
        return $optionalHandlerConfigs['handlers'] ?? $this->getChannelHandlersConfig(
            $optionalHandlerConfigs['channel'] ?? 'file'
        );
    }

    private function createHandler(string $handlerName, array $handlerOptions): LogHandler
    {
        $handlerClass = $this->getClassFromMap($this->handlerMap, $handlerName);
        $handlerConfig = $this->getHandlerConfig($handlerName, $handlerOptions);

        return $this->createInstance($handlerClass, $handlerConfig);
    }

    private function getHandlerConfig(string $handlerName, array $handlerOptions): array
    {
        $defaultConfig = $this->handlerMap[$handlerName]['with'] ?? [];
        return array_merge($defaultConfig, $handlerOptions);
    }
}
