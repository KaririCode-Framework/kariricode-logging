<?php

declare(strict_types=1);

namespace KaririCode\Logging\Processor;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;

class IntrospectionProcessor extends AbstractProcessor
{
    public function __construct(private readonly int $stackDepth = 6)
    {
    }

    public function process(ImmutableValue $record): ImmutableValue
    {
        if ($this->shouldTrack($record->level)) {
            $trace = $this->getDebugBacktrace();
            $maxDepth = $this->getMaxDepth($trace);

            $context = $this->isValidTraceDepth($trace, $maxDepth)
                ? $this->createContext($trace[$maxDepth], $record->context)
                : $record->context;

            return new LogRecord(
                $record->level,
                $record->message,
                $context,
                $record->datetime
            );
        }

        return $record;
    }

    private function getDebugBacktrace(): array
    {
        return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    }

    private function getMaxDepth(array $trace): int
    {
        return min($this->stackDepth, count($trace) - 1);
    }

    private function isValidTraceDepth(array $trace, int $depth): bool
    {
        return isset($trace[$depth]);
    }

    private function createContext(array $frame, array $originalContext): array
    {
        return array_merge(
            $originalContext,
            [
                'file' => $frame['file'] ?? null,
                'line' => $frame['line'] ?? null,
                'class' => $frame['class'] ?? null,
                'function' => $frame['function'] ?? null,
            ]
        );
    }

    private function shouldTrack(LogLevel $level): bool
    {
        $levelsToTrack = [
            LogLevel::ERROR,
            LogLevel::CRITICAL,
            LogLevel::ALERT,
            LogLevel::EMERGENCY,
        ];

        return in_array($level, $levelsToTrack, true);
    }
}
