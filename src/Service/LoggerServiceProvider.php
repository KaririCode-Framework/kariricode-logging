<?php

declare(strict_types=1);

namespace KaririCode\Logging\Service;

use KaririCode\Logging\Decorator\AsyncLogger;
use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\LoggerFactory;
use KaririCode\Logging\LoggerRegistry;
use KaririCode\Logging\LogLevel;

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
            $queryLogger = LoggerFactory::createQueryLogger(
                'query',
                $config->get('query_logger.threshold', 100)
            );
            LoggerRegistry::addLogger('query', $queryLogger);
        }

        // Register performance logger
        if ($config->get('performance_logger.enabled', false)) {
            $performanceLogger = LoggerFactory::createPerformanceLogger(
                'performance',
                $config->get('performance_logger.threshold', 1000)
            );
            LoggerRegistry::addLogger('performance', $performanceLogger);
        }

        // Register error logger
        if ($config->get('error_logger.enabled', true)) {
            $errorLogger = LoggerFactory::createErrorLogger(
                'error',
                $config->get('error_logger.levels', [
                    LogLevel::ERROR,
                    LogLevel::CRITICAL,
                    LogLevel::ALERT,
                    LogLevel::EMERGENCY,
                ])
            );
            LoggerRegistry::addLogger('error', $errorLogger);
        }

        // Register async logger if enabled
        if ($config->get('async.enabled', true)) {
            $asyncLogger = LoggerFactory::createAsyncLogger(
                $config->get('async.driver', AsyncLogger::class),
                $config->get('async.batch_size', 10)
            );
            LoggerRegistry::addLogger('async', $asyncLogger);
        }
    }
}
