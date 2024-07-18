<?php

declare(strict_types=1);

namespace KaririCode\Logging;

use KaririCode\Contract\Logging\Logger;

class LoggerRegistry
{
    private static array $loggers = [];

    public static function addLogger(string $name, Logger $logger): void
    {
        self::$loggers[$name] = $logger;
    }

    public static function getLogger(string $name): ?Logger
    {
        return self::$loggers[$name] ?? null;
    }

    public static function removeLogger(string $name): void
    {
        unset(self::$loggers[$name]);
    }
}
