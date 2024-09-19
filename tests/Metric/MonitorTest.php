<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Metric;

use KaririCode\Logging\Metric\Monitor;
use PHPUnit\Framework\TestCase;

final class MonitorTest extends TestCase
{
    private Monitor $monitor;

    protected function setUp(): void
    {
        $this->monitor = new Monitor();
    }

    public function testRecordMetric(): void
    {
        $this->monitor->recordMetric('metric1', 5.5);
        $this->monitor->recordMetric('metric1', 4.5);

        $metrics = $this->monitor->getMetrics();
        $this->assertEquals(2, $metrics['metric1']['count']);
        $this->assertEquals(10, $metrics['metric1']['sum']);
        $this->assertEquals(5, $metrics['metric1']['avg']);
        $this->assertEquals(4.5, $metrics['metric1']['min']);
        $this->assertEquals(5.5, $metrics['metric1']['max']);
    }
}
