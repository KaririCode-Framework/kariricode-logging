<?php

declare(strict_types=1);

namespace KaririCode\Logging\Rotator;

use KaririCode\Contract\Logging\LogRotator;

class SizeBasedRotator implements LogRotator
{
    public function __construct(
        private int $maxFiles = 5,
        private  int $maxFileSize = 5 * 1024 * 1024
    ) {

    }

    public function shouldRotate(string $filePath): bool
    {
        return file_exists($filePath) && filesize($filePath) >= $this->maxFileSize;
    }

    public function rotate(string $filePath): void
    {
        for ($i = $this->maxFiles - 1; $i > 0; --$i) {
            $source = "{$filePath}." . ($i - 1);
            $target = "{$filePath}.{$i}";
            if (file_exists($source)) {
                $this->safeRename($source, $target);
            }
        }

        if (file_exists($filePath)) {
            $this->safeRename($filePath, "{$filePath}.1");
        }
    }

    private function safeRename(string $source, string $target): void
    {
        if (!rename($source, $target)) {
            throw new \RuntimeException("Failed to rotate log file from {$source} to {$target}");
        }
    }
}
