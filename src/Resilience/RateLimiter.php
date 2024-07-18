<?php

declare(strict_types=1);

namespace KaririCode\Logging\Resilience;

use KaririCode\Logging\LogRecord;

class RateLimiter
{
    private array $buckets = [];

    public function __construct(
        private readonly int $limit,
        private readonly int $interval
    ) {
    }

    public function allow(LogRecord $record): bool
    {
        $key = $record->level->value . $record->message;
        $now = time();

        if (!isset($this->buckets[$key])) {
            $this->buckets[$key] = ['count' => 0, 'reset' => $now + $this->interval];
        }

        if ($now >= $this->buckets[$key]['reset']) {
            $this->buckets[$key] = ['count' => 0, 'reset' => $now + $this->interval];
        }

        if ($this->buckets[$key]['count'] < $this->limit) {
            ++$this->buckets[$key]['count'];

            return true;
        }

        return false;
    }
}
