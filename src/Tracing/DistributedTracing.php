<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tracing;

class DistributedTracing
{
    private string $traceId;
    private string $spanId;

    public function __construct()
    {
        $this->traceId = bin2hex(random_bytes(16));
        $this->spanId = bin2hex(random_bytes(8));
    }

    public function getTraceId(): string
    {
        return $this->traceId;
    }

    public function getSpanId(): string
    {
        return $this->spanId;
    }

    public function createChildSpan(): self
    {
        $child = new self();
        $child->traceId = $this->traceId;

        return $child;
    }
}
