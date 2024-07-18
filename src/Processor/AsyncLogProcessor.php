<?php

declare(strict_types=1);

namespace KaririCode\Logging\Processor;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\LogRecord;

class AsyncLogProcessor
{
    private array $queue = [];
    private ?\Fiber $processingFiber = null;

    public function __construct(private Logger $logger, private int $batchSize = 10)
    {
    }

    public function enqueue(LogRecord $record): void
    {
        $this->queue[] = $record;
        $this->ensureProcessingStarted();
    }

    private function ensureProcessingStarted(): void
    {
        if (null === $this->processingFiber || $this->processingFiber->isTerminated()) {
            $this->processingFiber = new \Fiber(function (): void {
                while (!empty($this->queue)) {
                    $batch = array_splice($this->queue, 0, $this->batchSize);
                    foreach ($batch as $record) {
                        $this->logger->log($record->level, $record->message, $record->context);
                        \Fiber::suspend(); // Cooperatively yield control
                    }
                }
            });
            $this->processingFiber->start();
        } elseif ($this->processingFiber->isSuspended()) {
            $this->processingFiber->resume();
        }
    }

    public function __destruct()
    {
        while (!empty($this->queue)) {
            $this->ensureProcessingStarted();
        }
    }
}
