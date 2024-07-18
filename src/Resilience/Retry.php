<?php

declare(strict_types=1);

namespace KaririCode\Logging\Resilience;

class Retry
{
    public function __construct(
        private readonly int $maxAttempts = 3,
        private readonly int $delay = 1000,
        private readonly int $multiplier = 1,
        private readonly int $jitter = 0
    ) {
    }

    public function execute(callable $operation): mixed
    {
        $attempts = 0;
        $lastException = null;
        $currentDelay = $this->delay;

        while ($attempts < $this->maxAttempts) {
            try {
                return $operation();
            } catch (\Exception $e) {
                $lastException = $e;
                ++$attempts;
                if ($attempts < $this->maxAttempts) {
                    $jitterValue = random_int(0, $this->jitter);
                    usleep(($currentDelay + $jitterValue) * 1000);
                    $currentDelay *= $this->multiplier;
                }
            }
        }

        throw $lastException;
    }
}
