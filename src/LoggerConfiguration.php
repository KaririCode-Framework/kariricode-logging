<?php

declare(strict_types=1);

namespace KaririCode\Logging;

use KaririCode\Logging\Exception\LoggingException;
use KaririCode\Logging\Validation\ConfigurationValidator;

class LoggerConfiguration
{
    private array $config = [];

    public function __construct(
        private ConfigurationValidator $validator = new ConfigurationValidator()
    ) {
    }

    public function set(string $key, mixed $value): void
    {
        $this->setNestedValue($this->config, $this->parseKey($key), $value);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getNestedValue($this->config, $this->parseKey($key)) ?? $default;
    }

    public function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new LoggingException("Configuration file not found: {$path}");
        }

        $loadedConfig = require $path;

        if (!is_array($loadedConfig)) {
            throw new LoggingException("Invalid configuration file: {$path}. Expected an array.");
        }

        $this->config = $loadedConfig;
        $this->validate();
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function validate(): void
    {
        $this->validator->validate($this->config);
    }

    private function setNestedValue(array &$array, array $keys, mixed $value): void
    {
        $key = array_shift($keys);
        if (empty($keys)) {
            $array[$key] = $value;
        } else {
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $this->setNestedValue($array[$key], $keys, $value);
        }
    }

    private function getNestedValue(array $array, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (!is_array($array) || !array_key_exists($key, $array)) {
                return null;
            }
            $array = $array[$key];
        }

        return $array;
    }

    private function parseKey(string $key): array
    {
        return preg_split('/(?<!\\\)\./', $key, -1, PREG_SPLIT_NO_EMPTY);
    }
}
