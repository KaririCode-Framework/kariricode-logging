<?php

declare(strict_types=1);

namespace KaririCode\Logging\Formatter;

use KaririCode\Contract\Logging\LogLevel;

class ConsoleColorFormatter
{
    private string $resetColor = "\033[0m";

    public function format(LogLevel $level, string $message): string
    {
        return $level->getColor() . $message . $this->resetColor;
    }
}
