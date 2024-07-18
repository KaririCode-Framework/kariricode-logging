<?php

declare(strict_types=1);

namespace KaririCode\Logging\Metric;

class MetricsCollector
{
    private array $metrics = [];

    public function increment(string $metric, int $value = 1): void
    {
        if (!isset($this->metrics[$metric])) {
            $this->metrics[$metric] = 0;
        }
        $this->metrics[$metric] += $value;
    }

    public function gauge(string $metric, float $value): void
    {
        $this->metrics[$metric] = $value;
    }

    public function getMetrics(): array
    {
        return $this->metrics;
    }

    public function reset(): void
    {
        $this->metrics = [];
    }
}
