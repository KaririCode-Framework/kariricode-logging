<?php

declare(strict_types=1);

namespace KaririCode\Logging\Decorator;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Contract\Logging\LogLevel;

class ContextualLogger extends BaseLoggerDecorator
{
    private array $additionalContext;

    public function __construct(Logger $logger, array $additionalContext)
    {
        parent::__construct($logger);
        $this->additionalContext = $additionalContext;
    }

    public function log(LogLevel $level, \Stringable|string $message, array $context = []): void
    {
        $mergedContext = array_merge($this->additionalContext, $context);
        $this->logger->log($level, $message, $mergedContext);
    }
}
