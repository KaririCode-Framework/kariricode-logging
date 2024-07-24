<?php

declare(strict_types=1);

namespace KaririCode\Logging\Processor\Metric;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Processor\AbstractProcessor;

class ExecutionTimeProcessor extends AbstractProcessor
{
    /**
     * @param LogRecord $record
     */
    public function process(ImmutableValue $record): ImmutableValue
    {
        $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        $context = array_merge($record->context, ['execution_time' => $executionTime]);

        return new LogRecord(
            $record->level,
            $record->message,
            $context,
            $record->datetime,
            $record->extra
        );
    }
}
