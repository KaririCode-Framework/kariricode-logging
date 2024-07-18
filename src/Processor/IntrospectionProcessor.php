<?php

declare(strict_types=1);

namespace KaririCode\Logging\Processor;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\LogRecord;

class IntrospectionProcessor extends AbstractProcessor
{
    public function __construct(private int $stackDepth = 6)
    {
    }

    public function process(ImmutableValue $record): ImmutableValue
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 7);

        $context = [];

        if (!empty($trace) && isset($trace[$this->stackDepth])) {
            $frame = $trace[$this->stackDepth];
            if ($this->hasValidContext($frame)) {
                $context = array_merge(
                    $record->context,
                    $context = [
                        'file' => $frame['file'],
                        'line' => $frame['line'],
                        'class' => $frame['class'],
                        'function' => $frame['function'],
                    ]
                );
            }
        }

        return new LogRecord(
            $record->level,
            $record->message,
            $context
        );
    }
}
