<?php

declare(strict_types=1);

namespace KaririCode\Logging;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Contract\Logging\LogLevel;

class LogRecord implements ImmutableValue
{
    public function __construct(
        public readonly LogLevel $level,
        public readonly string|\Stringable $message,
        public readonly array $context = [],
        public readonly \DateTimeImmutable $datetime = new \DateTimeImmutable(),
        public readonly array $extra = []
    ) {
    }

    public function toArray(): array
    {
        return [
            'level' => $this->level->value,
            'message' => $this->message,
            'context' => $this->context,
            'datetime' => $this->datetime,
            'extra' => $this->extra,
        ];
    }
}
