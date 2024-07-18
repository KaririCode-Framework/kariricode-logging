<?php

declare(strict_types=1);

namespace KaririCode\Logging;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\Formatter\JsonFormatter;
use KaririCode\Logging\Formatter\LineFormatter;
use KaririCode\Logging\Handler\FileHandler;
use KaririCode\Logging\Handler\SlackHandler;
use KaririCode\Logging\Handler\SyslogUdpHandler;

class LoggerFactory
{
    public static function createLogger(string $name, array $config): Logger
    {
        $handlers = [];
        $processors = [];
        $formatterClass = $config['formatter']['class'] ?? LineFormatter::class;
        $formatter = new $formatterClass();

        if (isset($config['path'])) {
            $handlers[] = new FileHandler(
                $config['path'],
                LogLevel::from($config['level']->value ?? 'debug'),
                $formatter
            );
        }

        if (isset($config['url'])) {
            $handlers[] = new SlackHandler($config['url'], LogLevel::from($config['level']->value ?? 'critical'), $formatter);
        }

        if (isset($config['handler'])) {
            $handlerClass = $config['handler'];
            if (SyslogUdpHandler::class === $handlerClass) {
                $handlers[] = new $handlerClass(
                    $config['handler_with']['host'] ?? null,
                    (int) $config['handler_with']['port'] ?? 0
                );
            } else {
                $handlerParams = $config['handler_with'] ?? [];
                $handlers[] = new $handlerClass(...$handlerParams);
            }
        }

        $processorsConfig = $config['processors'] ?? [];
        foreach ($processorsConfig as $processorConfig) {
            if (class_exists($processorConfig['class'])) {
                $processors[] = new $processorConfig['class']();
            }
        }

        return new LoggerManager($name, $handlers, $processors, $formatter);
    }

    public static function createQueryLogger(string $channel, int $threshold): Logger
    {
        $config = [
            'channel' => $channel,
            'threshold' => $threshold,
            'formatter' => ['class' => JsonFormatter::class],
        ];

        return self::createLogger('query', $config);
    }

    public static function createPerformanceLogger(string $channel, int $threshold): Logger
    {
        $config = [
            'channel' => $channel,
            'threshold' => $threshold,
            'formatter' => ['class' => JsonFormatter::class],
        ];

        return self::createLogger('performance', $config);
    }

    public static function createErrorLogger(string $channel, array $levels): Logger
    {
        $config = [
            'channel' => $channel,
            'levels' => $levels,
            'formatter' => ['class' => LineFormatter::class],
        ];

        return self::createLogger('error', $config);
    }

    public static function createAsyncLogger(string $driver, int $batchSize): Logger
    {
        $config = [
            'driver' => $driver,
            'batch_size' => $batchSize,
            'formatter' => ['class' => JsonFormatter::class],
        ];

        return self::createLogger('async', $config);
    }
}
