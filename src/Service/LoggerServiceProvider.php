<?php

declare(strict_types=1);

namespace KaririCode\Logging\Service;

use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\LoggerFactory;
use KaririCode\Logging\LoggerRegistry;

class LoggerServiceProvider
{
    public function register(LoggerConfiguration $config): void
    {
        $defaultChannel = $config->get('default');
        $channelsConfig = $config->get('channels', []);

        foreach ($channelsConfig as $channelName => $channelConfig) {
            $logger = LoggerFactory::createLogger($channelName, $channelConfig);
            LoggerRegistry::addLogger($channelName, $logger);

            if ($channelName === $defaultChannel) {
                LoggerRegistry::addLogger('default', $logger);
            }
        }

        // Register emergency logger
        $emergencyLoggerConfig = $config->get('emergency_logger', []);
        $emergencyLogger = LoggerFactory::createLogger('emergency', $emergencyLoggerConfig);
        LoggerRegistry::addLogger('emergency', $emergencyLogger);

        // Register query logger
        if ($config->get('query_logger.enabled', false)) {
            $queryLoggerConfig = $config->get('query_logger', []);
            $queryLogger = LoggerFactory::createQueryLogger($queryLoggerConfig);
            LoggerRegistry::addLogger('query', $queryLogger);
        }

        // Register performance logger
        if ($config->get('performance_logger.enabled', false)) {
            $performanceLoggerConfig = $config->get('performance_logger', []);
            $performanceLogger = LoggerFactory::createPerformanceLogger($performanceLoggerConfig);
            LoggerRegistry::addLogger('performance', $performanceLogger);
        }

        // Register error logger
        if ($config->get('error_logger.enabled', true)) {
            $errorLoggerConfig = $config->get('error_logger', []);
            $errorLogger = LoggerFactory::createErrorLogger($errorLoggerConfig);
            LoggerRegistry::addLogger('error', $errorLogger);
        }

        // Register async logger if enabled
        if ($config->get('async.enabled', true)) {
            $asyncLogger = LoggerFactory::createAsyncLogger(
                LoggerRegistry::getLogger('default'),
                (int) $config->get('async.batch_size', 10)
            );
            LoggerRegistry::addLogger('async', $asyncLogger);
        }
    }
}
