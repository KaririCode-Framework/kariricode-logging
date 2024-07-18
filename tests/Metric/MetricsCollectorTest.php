<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Metric;

use KaririCode\Logging\Metric\MetricsCollector;
use PHPUnit\Framework\TestCase;

class MetricsCollectorTest extends TestCase
{
    private MetricsCollector $metricsCollector;

    protected function setUp(): void
    {
        $this->metricsCollector = new MetricsCollector();
    }

    public function testIncrement(): void
    {
        $this->metricsCollector->increment('metric1');
        $this->metricsCollector->increment('metric1', 2);

        $metrics = $this->metricsCollector->getMetrics();
        $this->assertEquals(3, $metrics['metric1']);
    }

    public function testGauge(): void
    {
        $this->metricsCollector->gauge('metric2', 5.5);
        $metrics = $this->metricsCollector->getMetrics();
        $this->assertEquals(5.5, $metrics['metric2']);
    }

    public function testReset(): void
    {
        $this->metricsCollector->increment('metric1');
        $this->metricsCollector->reset();

        $metrics = $this->metricsCollector->getMetrics();
        $this->assertEmpty($metrics);
    }
}
