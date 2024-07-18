<?php

declare(strict_types=1);

namespace KaririCode\Logging\Decorator;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Contract\Logging\LogLevel;
use KaririCode\Logging\Trait\LoggerTrait;

abstract class BaseLoggerDecorator implements Logger
{
    use LoggerTrait;

    protected Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function log(LogLevel $level, \Stringable|string $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }

    public function getName(): string
    {
        return $this->logger->getName();
    }
}
