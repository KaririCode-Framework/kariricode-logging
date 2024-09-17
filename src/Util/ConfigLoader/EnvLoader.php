<?php

declare(strict_types=1);

namespace KaririCode\Logging\Util\ConfigLoader;

class EnvLoader
{
    private const ENV_FILE = '.env';

    public function load(): void
    {
        $envPath = $this->findRootPath() . DIRECTORY_SEPARATOR . self::ENV_FILE;
        if (!file_exists($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if ($this->isCommentLine($line)) {
                continue;
            }
            [$name, $value] = $this->parseEnvLine($line);
            $sanitizedValue = $this->sanitizeValue($value);
            putenv(sprintf('%s=%s', $name, $sanitizedValue));
        }
    }

    public function findRootPath(): string
    {
        $dir = __DIR__;
        while (!file_exists($dir . DIRECTORY_SEPARATOR . self::ENV_FILE) && '/' !== $dir) {
            $dir = dirname($dir);
        }

        if (file_exists($dir . DIRECTORY_SEPARATOR . self::ENV_FILE)) {
            return $dir;
        }

        throw new \RuntimeException('Root path with .env file not found.');
    }

    private function isCommentLine(string $line): bool
    {
        return 0 === strpos(trim($line), '#');
    }

    private function parseEnvLine(string $line): array
    {
        [$name, $value] = explode('=', $line, 2);

        return [trim($name), trim($value)];
    }

    private function sanitizeValue(string $value): string
    {
        // Remove any potentially harmful characters
        $value = preg_replace('/[^a-zA-Z0-9_\-\.,@\/\\\\:;]/', '', $value);

        // Ensure the value doesn't start with a dash (which could be interpreted as a command line option)
        $value = ltrim($value, '-');

        // Limit the length of the value to prevent buffer overflow attacks
        $maxLength = 1000; // Adjust this value based on your requirements
        if (strlen($value) > $maxLength) {
            $value = substr($value, 0, $maxLength);
        }

        return $value;
    }
}
