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
        $this->registerLogger('query', 'createQueryLogger');
        $this->registerLogger('performance', 'createPerformanceLogger');
        $this->registerLogger('error', 'createErrorLogger');
        $this->registerAsyncLoggerIfEnabled();
    }

    private function registerLogger(
        string $configKey,
        string $factoryMethod,
    ): void {
        $loggerConfig = $this->config->get($configKey, []);
        $logger = $this->loggerFactory->$factoryMethod($loggerConfig);
        $this->loggerRegistry->addLogger($configKey, $logger);
    }

    private function registerAsyncLoggerIfEnabled(): void
    {
        $asyncLogger = $this->loggerFactory->createAsyncLogger(
            $this->loggerRegistry->getLogger('default'),
            (int) $this->config->get('async.batch_size', 10)
        );
        $this->loggerRegistry->addLogger('async', $asyncLogger);
    }
}
