<?php

declare(strict_types=1);

namespace KaririCode\Logging\Formatter;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Contract\Logging\LogFormatter;

abstract class AbstractFormatter implements LogFormatter
{
    /**
     * @param string $dateFormat The date format pattern for timestamps
     * @param bool $includeContext Whether to include context in formatted output
     * @param bool $includeExtra Whether to include extra data in formatted output
     */
    public function __construct(
        public readonly string $dateFormat = 'Y-m-d H:i:s',
        public readonly bool $includeContext = true,
        public readonly bool $includeExtra = false
    ) {
    }

    /**
     * Format a single log record.
     */
    abstract public function format(ImmutableValue $record): string;

    /**
     * Format multiple log records.
     */
    public function formatBatch(array $records): string
    {
        return implode(PHP_EOL, array_map([$this, 'format'], $records));
    }

    /**
     * Create a new formatter with different date format.
     */
    public function withDateFormat(string $dateFormat): static
    {
        return new static(
            $dateFormat,
            $this->includeContext,
            $this->includeExtra
        );
    }

    /**
     * Create a new formatter with context inclusion setting.
     */
    public function withContextInclusion(bool $include): static
    {
        return new static(
            $this->dateFormat,
            $include,
            $this->includeExtra
        );
    }

    /**
     * Format the timestamp according to the configured format.
     */
    protected function formatTimestamp(\DateTimeImmutable $datetime): string
    {
        return $datetime->format($this->dateFormat);
    }

    /**
     * Check if the formatter should include context.
     */
    protected function shouldIncludeContext(array $context): bool
    {
        return $this->includeContext && !empty($context);
    }

    /**
     * Check if the formatter should include extra data.
     */
    protected function shouldIncludeExtra(array $extra): bool
    {
        return $this->includeExtra && !empty($extra);
    }
}
