<?php

declare(strict_types=1);

namespace KaririCode\Logging;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\Decorator\AsyncLogger;
use KaririCode\Logging\Formatter\LineFormatter;
use KaririCode\Logging\Handler\FileHandler;
use KaririCode\Logging\Handler\SlackHandler;
use KaririCode\Logging\Handler\SyslogUdpHandler;
use KaririCode\Logging\Util\SlackClient;

class LoggerFactory
{
    public function createLogger(string $name, array $config): Logger
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
            $slackClient = SlackClient::create($config['url']);
            $handlers[] = new SlackHandler(
                $slackClient,
                LogLevel::from($config['level'] ?? 'critical'),
                $formatter
            );
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

    public function createQueryLogger(array $config): Logger
    {
        return $this->createLogger('query', $config);
    }

    public function createPerformanceLogger(array $config): Logger
    {
        return $this->createLogger('performance', $config);
    }

    public function createErrorLogger(array $config): Logger
    {
        return $this->createLogger('error', $config);
    }

    public function createAsyncLogger(Logger $logger, int $batchSize): Logger
    {
        return new AsyncLogger($logger, $batchSize);
    }
}
