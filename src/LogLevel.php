<?php

declare(strict_types=1);

namespace KaririCode\Logging;

use KaririCode\Contract\Logging\LogLevel as LoggingLogLevel;

enum LogLevel: string implements LoggingLogLevel
{
    case EMERGENCY = 'emergency';
    case ALERT = 'alert';
    case CRITICAL = 'critical';
    case ERROR = 'error';
    case WARNING = 'warning';
    case NOTICE = 'notice';
    case INFO = 'info';
    case DEBUG = 'debug';

    public function getLevel(): string
    {
        return $this->value;
    }

    public function getValue(): int
    {
        return match ($this) {
            self::EMERGENCY => 800,
            self::ALERT => 700,
            self::CRITICAL => 600,
            self::ERROR => 500,
            self::WARNING => 400,
            self::NOTICE => 300,
            self::INFO => 200,
            self::DEBUG => 100,
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DEBUG => "\033[0;37m", // Light gray
            self::INFO => "\033[0;32m",  // Green
            self::NOTICE => "\033[1;34m", // Light blue
            self::WARNING => "\033[1;33m", // Yellow
            self::ERROR => "\033[0;31m", // Red
            self::CRITICAL => "\033[1;35m", // Magenta
            self::ALERT => "\033[1;31m", // Light red
            self::EMERGENCY => "\033[1;37m\033[41m", // White on red background
        };
    }
}
