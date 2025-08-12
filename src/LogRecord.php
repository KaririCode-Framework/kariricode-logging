<?php

declare(strict_types=1);

namespace KaririCode\Logging;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Contract\Logging\LogLevel;

final class LogRecord implements ImmutableValue
{
    /**
     * @param LogLevel $level The severity level of the log
     * @param string|\Stringable $message The log message
     * @param array<string, mixed> $context Additional contextual data
     * @param \DateTimeImmutable $datetime Timestamp of the log record
     * @param array<string, mixed> $extra Extra metadata
     */
    public function __construct(
        public readonly LogLevel $level,
        public readonly string|\Stringable $message,
        public readonly array $context = [],
        public readonly \DateTimeImmutable $datetime = new \DateTimeImmutable(),
        public readonly array $extra = []
    ) {
    }

    /**
     * Create a new instance with modified context
     * Following the immutability pattern.
     */
    public function withContext(array $context): self
    {
        return new self(
            $this->level,
            $this->message,
            array_merge($this->context, $context),
            $this->datetime,
            $this->extra
        );
    }

    /**
     * Create a new instance with modified extra data.
     */
    public function withExtra(array $extra): self
    {
        return new self(
            $this->level,
            $this->message,
            $this->context,
            $this->datetime,
            array_merge($this->extra, $extra)
        );
    }

    /**
     * Get the string representation of the message.
     */
    public function getMessageAsString(): string
    {
        return (string) $this->message;
    }

    /**
     * Check if the record has context data.
     */
    public function hasContext(): bool
    {
        return !empty($this->context);
    }

    /**
     * Check if the record has extra data.
     */
    public function hasExtra(): bool
    {
        return !empty($this->extra);
    }
}
