<?php

declare(strict_types=1);

namespace KaririCode\Logging\Util;

class ConfigHelper
{
    /**
     * Carrega o arquivo .env se existir.
     */
    public static function loadEnv(): void
    {
        $rootPath = self::findRootPath();
        if (file_exists($rootPath . '/.env')) {
            $lines = file($rootPath . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (0 === strpos(trim($line), '#')) {
                    continue;
                }
                list($name, $value) = explode('=', $line, 2);
                putenv(trim($name) . '=' . trim($value));
            }
        }
    }

    /**
     * Retorna o valor de uma variável de ambiente ou um valor padrão.
     */
    public static function env(string $key, $default = null)
    {
        $value = getenv($key);
        if (false === $value) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        return $value;
    }

    /**
     * Retorna o caminho do diretório de armazenamento de logs.
     */
    public static function storagePath(string $path = ''): string
    {
        $rootPath = self::findRootPath();

        return $rootPath . '/storage' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Encontra a raiz do projeto subindo os diretórios até encontrar o .env.
     */
    public static function findRootPath(): string
    {
        $dir = __DIR__;
        while (!file_exists($dir . '/.env') && '/' !== $dir) {
            $dir = dirname($dir);
        }

        if (file_exists($dir . '/.env')) {
            return $dir;
        }

        throw new \RuntimeException('Root path with .env file not found.');
    }
}
