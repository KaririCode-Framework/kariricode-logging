<?php

declare(strict_types=1);

namespace KaririCode\Logging;

use KaririCode\Contract\Logging\Logger;

class LoggerRegistry
{
    private array $loggers = [];

    public function addLogger(string $name, Logger $logger): void
    {
        $this->loggers[$name] = $logger;
    }

    public function getLogger(string $name): ?Logger
    {
        return $this->loggers[$name] ?? null;
    }

    public function removeLogger(string $name): void
    {
        unset($this->loggers[$name]);
    }
}
