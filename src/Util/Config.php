<?php

declare(strict_types=1);

namespace KaririCode\Logging\Util;

use KaririCode\Logging\Util\ConfigLoader\EnvLoader;
use KaririCode\Logging\Util\ConfigLoader\EnvParser;

class Config
{
    private static ?EnvLoader $envLoader = null;
    private static ?EnvParser $envParser = null;

    public static function loadEnv(): void
    {
        self::getEnvLoader()->load();
    }

    public static function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);
        if (false === $value) {
            return $default;
        }

        return self::getEnvParser()->parse($value);
    }

    public static function storagePath(string $path = ''): string
    {
        $rootPath = self::getEnvLoader()->findRootPath();

        return $rootPath . DIRECTORY_SEPARATOR . 'storage' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    public static function parseIntValue(string $value): int
    {
        return self::getEnvParser()->parseIntValue($value);
    }

    public static function parseFloatValue(string $value): float
    {
        return self::getEnvParser()->parseFloatValue($value);
    }

    public static function parseBooleanValue(string $value): bool
    {
        return self::getEnvParser()->parseBooleanValue($value);
    }

    public static function parseStringValue(string $value): string
    {
        return self::getEnvParser()->parseStringValue($value);
    }

    private static function getEnvLoader(): EnvLoader
    {
        if (null === self::$envLoader) {
            self::$envLoader = new EnvLoader();
        }

        return self::$envLoader;
    }

    private static function getEnvParser(): EnvParser
    {
        if (null === self::$envParser) {
            self::$envParser = new EnvParser();
        }

        return self::$envParser;
    }
}
