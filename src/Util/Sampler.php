<?php

declare(strict_types=1);

namespace KaririCode\Logging\Util;

class Sampler
{
    public function __construct(private readonly float $sampleRate)
    {
        if ($sampleRate <= 0 || $sampleRate > 1) {
            throw new \InvalidArgumentException('Sample rate must be between 0 and 1');
        }
    }

    public function shouldSample(): bool
    {
        return (mt_rand() / mt_getrandmax()) <= $this->sampleRate;
    }
}
