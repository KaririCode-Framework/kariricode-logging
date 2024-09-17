<?php

declare(strict_types=1);

namespace KaririCode\Logging\Processor\Metric;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Processor\AbstractProcessor;

class ExecutionTimeProcessor extends AbstractProcessor
{
    public function __construct(private float $threshold = 1000)
    {
    }

    public function process(ImmutableValue $record): ImmutableValue
    {
        $executionTime = (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000; // Convert to milliseconds

        if ($executionTime > $this->threshold) {
            $context = array_merge($record->context, ['execution_time' => $executionTime]);

            return new LogRecord(
                $record->level,
                $record->message,
                $context,
                $record->datetime,
                $record->extra
            );
        }

        return $record;
    }
}
