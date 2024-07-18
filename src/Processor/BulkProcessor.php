<?php

declare(strict_types=1);

namespace KaririCode\Logging\Processor;

use KaririCode\Contract\ImmutableValue;

class BulkProcessor
{
    private array $buffer = [];
    private \Closure $flushCallback;

    public function __construct(
        private int $batchSize,
        callable $flushCallback
    ) {
        $this->flushCallback = $flushCallback;
    }

    public function process(ImmutableValue $record): void
    {
        $this->buffer[] = $record;

        if (count($this->buffer) >= $this->batchSize) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        if (!empty($this->buffer)) {
            call_user_func($this->flushCallback, $this->buffer);
            $this->buffer = [];
        }
    }

    public function __destruct()
    {
        $this->flush();
    }
}
