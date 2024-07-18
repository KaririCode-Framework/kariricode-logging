<?php

declare(strict_types=1);

namespace KaririCode\Logging\Formatter;

use KaririCode\Contract\Logging\LogLevel as LoggingLogLevel;
use KaririCode\Logging\LogLevel;

class ConsoleColorFormatter
{
    private array $colors = [
        LogLevel::DEBUG->value => "\033[0;37m", // Light gray
        LogLevel::INFO->value => "\033[0;32m",  // Green
        LogLevel::NOTICE->value => "\033[1;34m", // Light blue
        LogLevel::WARNING->value => "\033[1;33m", // Yellow
        LogLevel::ERROR->value => "\033[0;31m", // Red
        LogLevel::CRITICAL->value => "\033[1;35m", // Magenta
        LogLevel::ALERT->value => "\033[1;31m", // Light red
        LogLevel::EMERGENCY->value => "\033[1;37m\033[41m", // White on red background
    ];
    private string $resetColor = "\033[0m";

    public function format(LoggingLogLevel $level, string $message): string
    {
        return $this->colors[$level->value] . $message . $this->resetColor;
    }
}
