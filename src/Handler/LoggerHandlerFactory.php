<?php

declare(strict_types=1);

namespace KaririCode\Logging\Handler;

use KaririCode\Contract\Logging\LogHandler;
use KaririCode\Logging\Contract\Logging\LoggerConfigurableFactory;
use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\Util\ReflectionFactoryTrait;

class LoggerHandlerFactory implements LoggerConfigurableFactory
{
    use ReflectionFactoryTrait;

    private array $handlerMap = [];
    private array $channelConfigs = [];

    public function initializeFromConfiguration(LoggerConfiguration $config): void
    {
        $this->handlerMap = $config->get('handlers', [
            'file' => FileHandler::class,
            'console' => ConsoleHandler::class,
            'slack' => SlackHandler::class,
            'syslog' => SyslogUdpHandler::class,
        ]);

        $this->channelConfigs = $config->get('channels', []);
    }

    public function createHandlers(string $channelName): array
    {
        $channelConfig = $this->channelConfigs[$channelName] ?? [];
        $handlersConfig = $channelConfig['handlers'] ?? [];

        $handlers = [];
        foreach ($handlersConfig as $key => $value) {
            [$handlerName, $handlerOptions] = $this->extractMergedConfig($key, $value);
            $handlers[] = $this->createHandler($handlerName, $handlerOptions);
        }

        return $handlers;
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
