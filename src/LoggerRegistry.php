<?php

declare(strict_types=1);

namespace KaririCode\Logging;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\Exception\LoggerNotFoundException;

class LoggerRegistry
{
    /** @var array<string, Logger> */
    private array $loggers = [];

    public function addLogger(string $name, Logger $logger): void
    {
        if (isset($this->loggers[$name])) {
            throw new \InvalidArgumentException('Logger with name "' . $name . '" already exists.');
        }

        $this->loggers[$name] = $logger;
    }

    public function getLogger(string $name): ?Logger
    {
        if (!isset($this->loggers[$name])) {
            throw new LoggerNotFoundException('Logger with name "' . $name . '" not found.');
        }

        return $this->loggers[$name];
    }

    public function removeLogger(string $name): void
    {
        if (!isset($this->loggers[$name])) {
            throw new LoggerNotFoundException('Logger with name "' . $name . '" not found.');
        }

        unset($this->loggers[$name]);
    }
}
