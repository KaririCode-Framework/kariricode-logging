<?php

declare(strict_types=1);

namespace KaririCode\Logging\Util;

class ContextPropagator
{
    private static array $context = [];

    public static function set(string $key, mixed $value): void
    {
        self::$context[$key] = $value;
    }

    public static function get(string $key): mixed
    {
        return self::$context[$key] ?? null;
    }

    public static function remove(string $key): void
    {
        unset(self::$context[$key]);
    }

    public static function clear(): void
    {
        self::$context = [];
    }

    public static function all(): array
    {
        return self::$context;
    }
}
