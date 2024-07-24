<?php

declare(strict_types=1);

namespace KaririCode\Logging;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\Exception\LoggerNotFoundException;

class LoggerRegistry
{
    private array $loggers = [];

    public function addLogger(string $name, Logger $logger): void
    {
        $this->loggers[$name] = $logger;
    }

    public function getLogger(string $name): Logger
    {
        if (!isset($this->loggers[$name])) {
            throw new LoggerNotFoundException("Logger with name '$name' not found.");
        }

        return $this->loggers[$name];
    }

    public function removeLogger(string $name): void
    {
        unset($this->loggers[$name]);
    }
}
