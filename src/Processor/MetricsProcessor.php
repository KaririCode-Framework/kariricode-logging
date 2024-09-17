<?php

declare(strict_types=1);

namespace KaririCode\Logging\Processor;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Contract\Logging\LogProcessor;
use KaririCode\Logging\LogRecord;

class MetricsProcessor implements LogProcessor
{
    private array $processors;

    public function __construct(array $processors)
    {
        $this->processors = $processors;
    }

    public function process(ImmutableValue $record): ImmutableValue
    {
        foreach ($this->processors as $processor) {
            $record = $processor->process($record);
        }

        return $record;
    }
}
