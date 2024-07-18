<?php

declare(strict_types=1);

namespace KaririCode\Logging\Resilience;

class CircuitBreaker
{
    private int $failures = 0;
    private int $successes = 0;
    private bool $forcedOpen = false;
    private ?\DateTime $lastFailureTime = null;

    public function __construct(
        private readonly int $failureThreshold = 5,
        private readonly int $successThreshold = 2,
        private readonly int $resetTimeout = 60
    ) {
    }

    public function isOpen(): bool
    {
        if ($this->forcedOpen) {
            return true;
        }

        if ($this->failures >= $this->failureThreshold) {
            if ($this->lastFailureTime === null) {
                return true;
            }

            $elapsedTime = time() - $this->lastFailureTime->getTimestamp();
            if ($elapsedTime > $this->resetTimeout) {
                $this->resetFailures();
                return false;
            }

            return true;
        }

        return false;
    }

    public function recordFailure(): void
    {
        ++$this->failures;
        $this->successes = 0;
        $this->lastFailureTime = new \DateTime();
    }

    public function recordSuccess(): void
    {
        if (!$this->forcedOpen) {
            ++$this->successes;
            if ($this->successes >= $this->successThreshold) {
                $this->resetFailures();
            }
        }
    }

    private function resetFailures(): void
    {
        $this->failures = 0;
        $this->successes = 0;
        $this->lastFailureTime = null;
    }

    public function forceOpen(): void
    {
        $this->forcedOpen = true;
    }

    public function forceClose(): void
    {
        $this->forcedOpen = false;
        $this->resetFailures();
    }
}
