<?php

declare(strict_types=1);

namespace KaririCode\Logging\Handler;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\Exception\LoggingException;
use KaririCode\Logging\LogLevel;

class RotatingFileHandler extends AbstractFileHandler
{
    private int $maxFiles;
    private int $maxFileSize;

    public function __construct(
        string $filePath,
        int $maxFiles = 5,
        int $maxFileSize = 5 * 1024 * 1024, // 5 MB
        LogLevel $minLevel = LogLevel::DEBUG
    ) {
        parent::__construct($filePath, $minLevel);
        $this->maxFiles = $maxFiles;
        $this->maxFileSize = $maxFileSize;
    }

    public function handle(ImmutableValue $record): void
    {
        if (!$this->isHandling($record)) {
            return;
        }

        if ($this->shouldRotate()) {
            $this->rotate();
        }

        $this->writeToFile($record);
    }

    private function shouldRotate(): bool
    {
        return file_exists($this->filePath) && filesize($this->filePath) >= $this->maxFileSize;
    }

    private function rotate(): void
    {
        for ($i = $this->maxFiles - 1; $i > 0; --$i) {
            $source = "{$this->filePath}.{$i}";
            $target = "{$this->filePath}." . ($i + 1);
            if (file_exists($source)) {
                if (!rename($source, $target)) {
                    throw new LoggingException("Failed to rotate log file from {$source} to {$target}");
                }
            }
        }

        // Close the current file handle and open a new one
        if (is_resource($this->fileHandle)) {
            fclose($this->fileHandle);
        }
        $this->openFile();
    }
}
