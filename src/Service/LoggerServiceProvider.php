<?php

declare(strict_types=1);

namespace KaririCode\Logging\Service;

use KaririCode\Logging\Exception\InvalidConfigurationException;
use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\LoggerFactory;
use KaririCode\Logging\LoggerRegistry;

class LoggerServiceProvider
{
    public function __construct(
        private LoggerConfiguration $config,
        private LoggerFactory $loggerFactory,
        private LoggerRegistry $loggerRegistry
    ) {
    }

    public function register(): void
    {
        $this->registerDefaultLoggers();
        $this->registerEmergencyLogger();
        $this->registerOptionalLoggers();
    }

    private function registerDefaultLoggers(): void
    {
        $defaultChannel = $this->config->get('default');
        $channelsConfig = $this->config->get('channels');

        if (null === $defaultChannel || null === $channelsConfig) {
            throw new InvalidConfigurationException("The 'default' and 'channels' configurations are required.");
        }

        foreach ($channelsConfig as $channelName => $channelConfig) {
            $logger = $this->loggerFactory->createLogger($channelName, $channelConfig);
            $this->loggerRegistry->addLogger($channelName, $logger);

            if ($channelName === $defaultChannel) {
                $this->loggerRegistry->addLogger('default', $logger);
            }
        }
    }

    private function registerEmergencyLogger(): void
    {
        $emergencyLoggerConfig = $this->config->get('emergency_logger', []);
        $emergencyLogger = $this->loggerFactory->createLogger(
            'emergency',
            $emergencyLoggerConfig
        );
        $this->loggerRegistry->addLogger('emergency', $emergencyLogger);
    }

    private function registerOptionalLoggers(): void
    {
        $this->registerLoggerIfEnabled('query_logger', 'createQueryLogger');
        $this->registerLoggerIfEnabled('performance_logger', 'createPerformanceLogger');
        $this->registerLoggerIfEnabled('error_logger', 'createErrorLogger', true);
        $this->registerAsyncLoggerIfEnabled();
    }

    private function registerLoggerIfEnabled(
        string $configKey,
        string $factoryMethod,
        bool $defaultEnabled = false
    ): void {
        if ($this->config->get("$configKey.enabled", $defaultEnabled)) {
            $loggerConfig = $this->config->get($configKey, []);
            $logger = $this->loggerFactory->$factoryMethod($loggerConfig);
            $this->loggerRegistry->addLogger(explode('_', $configKey)[0], $logger);
        }
    }

    private function registerAsyncLoggerIfEnabled(): void
    {
        if ($this->config->get('async.enabled', true)) {
            $asyncLogger = $this->loggerFactory->createAsyncLogger(
                $this->loggerRegistry->getLogger('default'),
                (int) $this->config->get('async.batch_size', 10)
            );
            $this->loggerRegistry->addLogger('async', $asyncLogger);
        }
    }
}
