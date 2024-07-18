<?php

declare(strict_types=1);

namespace KaririCode\Logging\Handler;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Contract\Logging\LogRotator;
use KaririCode\Logging\Exception\LoggingException;
use KaririCode\Logging\LogLevel;

class RotatingFileHandler extends AbstractFileHandler
{
    private LogRotator $rotator;

    public function __construct(
        string $filePath,
        LogRotator $rotator,
        LogLevel $minLevel = LogLevel::DEBUG
    ) {
        parent::__construct($filePath, $minLevel);
        $this->rotator = $rotator;
    }

    /**
     * @throws LoggingException
     */
    public function handle(ImmutableValue $record): void
    {
        if (!$this->isHandling($record)) {
            return;
        }

        try {
            $this->rotateIfNecessary();
            $this->writeToFile($record);
        } catch (\Exception $e) {
            throw new LoggingException("Error handling log record: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws \Exception
     */
    private function rotateIfNecessary(): void
    {
        if ($this->rotator->shouldRotate($this->filePath)) {
            $this->rotator->rotate($this->filePath);
            $this->reopenFile();
        }
    }

    private function reopenFile(): void
    {
        if (is_resource($this->fileHandle)) {
            fclose($this->fileHandle);
        }
        $this->fileHandle = null;
        $this->openFile();
    }
}
