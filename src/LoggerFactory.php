<?php

declare(strict_types=1);

namespace KaririCode\Logging;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\Decorator\AsyncLogger;
use KaririCode\Logging\Formatter\LoggerFormatterFactory;
use KaririCode\Logging\Handler\LoggerHandlerFactory;
use KaririCode\Logging\Processor\LoggerProcessorFactory;

class LoggerFactory
{
    public function __construct(
        private LoggerConfiguration $config,
        private LoggerHandlerFactory $handlerFactory = new LoggerHandlerFactory(),
        private LoggerProcessorFactory $processorFactory = new LoggerProcessorFactory(),
        private LoggerFormatterFactory $formatterFactory = new LoggerFormatterFactory()
    ) {
        $this->handlerFactory->initializeFromConfiguration($config);
        $this->processorFactory->initializeFromConfiguration($config);
        $this->formatterFactory->initializeFromConfiguration($config);
    }

    public function createLogger(string $name): Logger
    {
        $handlers = $this->handlerFactory->createHandlers($name);
        $processors = $this->processorFactory->createProcessors($name);
        $formatter = $this->formatterFactory->createFormatter($name);

        return new LoggerManager($name, $handlers, $processors, $formatter);
    }

    public function createPerformanceLogger(): Logger
    {
        /** @var LoggerManager $logger */
        $logger = $this->createLogger('performance');
        $threshold = $this->config->get('performance.threshold', 1000);
        $logger->setThreshold('execution_time', $threshold);

        return $logger;
    }

    public function createQueryLogger(): Logger
    {
        /** @var LoggerManager $logger */
        $logger = $this->createLogger('query');
        $threshold = $this->config->get('query.threshold', 100);
        $logger->setThreshold('time', $threshold);

        return $logger;
    }

    public function createErrorLogger(): Logger
    {
        return $this->createLogger('error');
    }

    public function createAsyncLogger(Logger $logger, int $batchSize): Logger
    {
        return new AsyncLogger($logger, $batchSize);
    }
}
