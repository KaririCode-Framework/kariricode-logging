<?php

declare(strict_types=1);

namespace KaririCode\Logging\Decorator;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Contract\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Processor\AsyncLogProcessor;

class AsyncLogger extends BaseLoggerDecorator
{
    private AsyncLogProcessor $processor;

    public function __construct(Logger $logger, int $batchSize = 10)
    {
        parent::__construct($logger);
        $this->processor = new AsyncLogProcessor($logger, $batchSize);

        // Register shutdown function to ensure logs are processed
        register_shutdown_function([$this, 'shutdown']);

    }

    public function log(LogLevel $level, \Stringable|string $message, array $context = []): void
    {
        $record = new LogRecord($level, $message, $context);
        $this->processor->enqueue($record);
    }

    public function shutdown(): void
    {
        $this->processor->processRemaining();
    }

    public function __destruct()
    {
        $this->shutdown();
    }
}
