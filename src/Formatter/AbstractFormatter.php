<?php

declare(strict_types=1);

namespace KaririCode\Logging\Formatter;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Contract\Logging\LogFormatter;
use KaririCode\Contract\Logging\Structural\FormatterAware;

abstract class AbstractFormatter implements LogFormatter, FormatterAware, ImmutableValue
{
    protected ImmutableValue $formatter;

    public function __construct(
        protected string $dateFormat = 'Y-m-d H:i:s'
    ) {
        $this->formatter = $this;
    }

    abstract public function format(ImmutableValue $record): string;

    public function formatBatch(array $records): string
    {
        return implode("\n", array_map([$this, 'format'], $records));
    }

    public function setFormatter(ImmutableValue $formatter): AbstractFormatter
    {
        $this->formatter = $formatter;

        return $this;
    }

    public function getFormatter(): ImmutableValue
    {
        return $this->formatter;
    }

    // Implement the toArray method required by ImmutableValue interface
    public function toArray(): array
    {
        return [
            'dateFormat' => $this->dateFormat,
            'formatter' => $this->formatter instanceof ImmutableValue ? $this->formatter->toArray() : (string) $this->formatter,
        ];
    }
}
