<?php

declare(strict_types=1);

namespace KaririCode\Logging\Metric;

class Monitor
{
    private array $metrics = [];

    public function recordMetric(string $name, float $value): void
    {
        if (!isset($this->metrics[$name])) {
            $this->metrics[$name] = [];
        }
        $this->metrics[$name][] = $value;
    }

    public function getMetrics(): array
    {
        return array_map(function ($values) {
            return [
                'count' => count($values),
                'sum' => array_sum($values),
                'avg' => array_sum($values) / count($values),
                'min' => min($values),
                'max' => max($values),
            ];
        }, $this->metrics);
    }
}
