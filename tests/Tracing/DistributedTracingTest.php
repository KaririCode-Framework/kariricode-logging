<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Tracing;

use KaririCode\Logging\Tracing\DistributedTracing;
use PHPUnit\Framework\TestCase;

class DistributedTracingTest extends TestCase
{
    private DistributedTracing $distributedTracing;

    protected function setUp(): void
    {
        $this->distributedTracing = new DistributedTracing();
    }

    public function testGetTraceId(): void
    {
        $traceId = $this->distributedTracing->getTraceId();
        $this->assertNotEmpty($traceId);
    }

    public function testGetSpanId(): void
    {
        $spanId = $this->distributedTracing->getSpanId();
        $this->assertNotEmpty($spanId);
    }

    public function testCreateChildSpan(): void
    {
        $childSpan = $this->distributedTracing->createChildSpan();

        $this->assertEquals($this->distributedTracing->getTraceId(), $childSpan->getTraceId());
        $this->assertNotEquals($this->distributedTracing->getSpanId(), $childSpan->getSpanId());
    }
}
