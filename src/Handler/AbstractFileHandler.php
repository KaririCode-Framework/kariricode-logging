<?php

declare(strict_types=1);

namespace KaririCode\Logging\Handler;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\Exception\LoggingException;
use KaririCode\Logging\LogLevel;

abstract class AbstractFileHandler extends AbstractHandler
{
    protected mixed $fileHandle;

    public function __construct(
        protected readonly string $filePath,
        LogLevel $minLevel = LogLevel::DEBUG
    ) {
        parent::__construct($minLevel);
        $this->ensureDirectoryExists();
        $this->openFile();
    }

    protected function ensureDirectoryExists(): void
    {
        $directory = dirname($this->filePath);

        if ($this->directoryDoesNotExist($directory) && !$this->createDirectory($directory)) {
            throw new LoggingException("Unable to create log directory: $directory");
        }

        if (!$this->isDirectoryWritable($directory)) {
            throw new LoggingException("Log directory is not writable: $directory");
        }
    }

    private function directoryDoesNotExist(string $directory): bool
    {
        return !is_dir($directory);
    }

    protected function createDirectory($path)
    {
        return mkdir($path, 0777, true);
    }

    protected function isDirectoryWritable($path)
    {
        return is_writable($path);
    }

    protected function openFile(): void
    {
        $this->fileHandle = fopen($this->filePath, 'a');
        if (!$this->fileHandle) {
            throw new LoggingException("Unable to open log file: {$this->filePath}");
        }
    }

    abstract public function handle(ImmutableValue $record): void;

    protected function writeToFile(ImmutableValue $record): void
    {
        $formatted = $this->formatter->format($record);
        if (false === fwrite($this->fileHandle, $formatted . PHP_EOL)) {
            throw new LoggingException('Failed to write to log file');
        }
    }

    public function __destruct()
    {
        if (is_resource($this->fileHandle)) {
            fclose($this->fileHandle);
        }
    }
}
