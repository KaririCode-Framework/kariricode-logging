<?php

declare(strict_types=1);

namespace KaririCode\Logging;

use KaririCode\Logging\Exception\LoggingException;

class LoggerConfiguration
{
    private array $config = [];

    public function set(string $key, mixed $value): void
    {
        $this->config[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new LoggingException("Configuration file not found: {$path}");
        }
        $this->config = require $path;
    }
}
