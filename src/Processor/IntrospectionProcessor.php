<?php

declare(strict_types=1);

namespace KaririCode\Logging\Processor;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;

/**
 * Processor that adds introspection data to log records using immutable pattern.
 *
 * This processor enhances log records with debugging information such as file,
 * line number, class, and method for error-level logs. It follows the immutable
 * pattern to ensure thread safety and data integrity.
 *
 * @see https://www.php.net/manual/en/function.debug-backtrace.php
 * @see Clean Code: A Handbook of Agile Software Craftsmanship by Robert C. Martin
 */
final class IntrospectionProcessor extends AbstractProcessor
{
    /**
     * Log levels that should include introspection data for debugging purposes.
     * Only error-level and above logs include stack trace information to avoid
     * performance overhead on informational logs.
     */
    private const TRACKABLE_LEVELS = [
        LogLevel::ERROR,
        LogLevel::CRITICAL,
        LogLevel::ALERT,
        LogLevel::EMERGENCY,
    ];

    /**
     * Default stack depth to traverse when gathering introspection data.
     * This should be deep enough to skip framework code and reach user code.
     */
    private const DEFAULT_STACK_DEPTH = 6;

    /**
     * Additional frames to skip beyond the configured depth to account for
     * internal framework calls (processor chain, logger calls, etc.).
     */
    private const FRAMEWORK_STACK_OFFSET = 2;

    /**
     * Minimum allowed stack depth to prevent errors.
     */
    private const MIN_STACK_DEPTH = 1;

    /**
     * Maximum allowed stack depth to prevent performance issues.
     */
    private const MAX_STACK_DEPTH = 50;

    /**
     * @param int $stackDepth How deep to traverse the stack trace (1-50)
     * @param bool $includeArgs Whether to include function arguments in backtrace
     *
     * @throws \InvalidArgumentException when stackDepth is out of valid range
     */
    public function __construct(
        private readonly int $stackDepth = self::DEFAULT_STACK_DEPTH,
        private readonly bool $includeArgs = false
    ) {
        $this->validateStackDepth($stackDepth);
    }

    /**
     * Process the log record and add introspection data for trackable levels.
     *
     * @param ImmutableValue $record The log record to process
     *
     * @return ImmutableValue The processed record with introspection data
     */
    public function process(ImmutableValue $record): ImmutableValue
    {
        if (!$this->isProcessableRecord($record)) {
            return $record;
        }

        /** @var LogRecord $record */
        if (!$this->shouldAddIntrospectionData($record->level)) {
            return $record;
        }

        $introspectionData = $this->gatherIntrospectionData();

        if ($this->hasNoIntrospectionData($introspectionData)) {
            return $record;
        }

        return $this->createEnhancedRecord($record, $introspectionData);
    }

    /**
     * Validate that the stack depth is within acceptable bounds.
     *
     * @param int $stackDepth The stack depth to validate
     *
     * @throws \InvalidArgumentException when depth is invalid
     */
    private function validateStackDepth(int $stackDepth): void
    {
        if ($stackDepth < self::MIN_STACK_DEPTH || $stackDepth > self::MAX_STACK_DEPTH) {
            throw new \InvalidArgumentException(sprintf('Stack depth must be between %d and %d, got %d', self::MIN_STACK_DEPTH, self::MAX_STACK_DEPTH, $stackDepth));
        }
    }

    /**
     * Check if the record is processable (instance of LogRecord).
     *
     * @param ImmutableValue $record The record to check
     *
     * @return bool True if the record can be processed
     */
    private function isProcessableRecord(ImmutableValue $record): bool
    {
        return $record instanceof LogRecord;
    }

    /**
     * Determine if introspection data should be added for the given log level.
     *
     * @param LogLevel $level The log level to check
     *
     * @return bool True if introspection data should be added
     */
    private function shouldAddIntrospectionData(LogLevel $level): bool
    {
        return in_array($level, self::TRACKABLE_LEVELS, true);
    }

    /**
     * Check if introspection data is empty or invalid.
     *
     * @param array $introspectionData The introspection data to check
     *
     * @return bool True if data is empty
     */
    private function hasNoIntrospectionData(array $introspectionData): bool
    {
        return empty($introspectionData);
    }

    /**
     * Create a new log record with enhanced introspection context.
     *
     * @param LogRecord $originalRecord The original log record
     * @param array $introspectionData The introspection data to add
     *
     * @return LogRecord The enhanced log record
     */
    private function createEnhancedRecord(LogRecord $originalRecord, array $introspectionData): LogRecord
    {
        return new LogRecord(
            level: $originalRecord->level,
            message: $originalRecord->message,
            context: array_merge($originalRecord->context, $introspectionData),
            datetime: $originalRecord->datetime,
            extra: $originalRecord->extra
        );
    }

    /**
     * Gather introspection data from the call stack.
     *
     * This method analyzes the debug backtrace to extract relevant debugging
     * information such as file, line, class, and method where the log was called.
     *
     * @return array The introspection data containing file, line, class, function, and type
     */
    private function gatherIntrospectionData(): array
    {
        $backtraceFlags = $this->determineBacktraceFlags();
        $stackTrace = $this->getStackTrace($backtraceFlags);
        $targetFrame = $this->findTargetFrame($stackTrace);

        if (!$this->isValidFrame($targetFrame)) {
            return [];
        }

        return $this->extractIntrospectionDataFromFrame($targetFrame);
    }

    /**
     * Determine the appropriate flags for debug_backtrace based on configuration.
     *
     * @return int The backtrace flags
     */
    private function determineBacktraceFlags(): int
    {
        return $this->includeArgs ? 0 : DEBUG_BACKTRACE_IGNORE_ARGS;
    }

    /**
     * Get the debug backtrace with appropriate depth and flags.
     *
     * @param int $flags The backtrace flags
     *
     * @return array The stack trace
     */
    private function getStackTrace(int $flags): array
    {
        $maxDepth = $this->calculateMaxBacktraceDepth();

        return debug_backtrace($flags, $maxDepth);
    }

    /**
     * Calculate the maximum depth for the backtrace including framework offset.
     *
     * We need to capture enough frames to skip framework code and reach user code.
     * The limit should be generous to accommodate deep call stacks.
     *
     * @return int The maximum backtrace depth
     */
    private function calculateMaxBacktraceDepth(): int
    {
        // Use a generous limit to ensure we capture the target frame
        return max($this->stackDepth + self::FRAMEWORK_STACK_OFFSET, 15);
    }

    /**
     * Find the target frame from the stack trace at the configured depth.
     *
     * @param array $stackTrace The complete stack trace
     *
     * @return array|null The target frame or null if not found
     */
    private function findTargetFrame(array $stackTrace): ?array
    {
        $targetDepth = $this->calculateTargetDepth($stackTrace);

        return $stackTrace[$targetDepth] ?? null;
    }

    /**
     * Calculate the actual target depth, ensuring it doesn't exceed the trace length.
     *
     * The target depth should skip framework calls and reach user code.
     * We add the framework offset to the configured stack depth.
     *
     * @param array $stackTrace The stack trace
     *
     * @return int The calculated target depth
     */
    private function calculateTargetDepth(array $stackTrace): int
    {
        $desiredDepth = $this->stackDepth + self::FRAMEWORK_STACK_OFFSET;
        $maxAvailableDepth = count($stackTrace) - 1;

        return min($desiredDepth, $maxAvailableDepth);
    }

    /**
     * Check if the frame contains valid introspection data.
     *
     * @param array|null $frame The frame to validate
     *
     * @return bool True if the frame is valid
     */
    private function isValidFrame(?array $frame): bool
    {
        return null !== $frame && is_array($frame);
    }

    /**
     * Extract and filter introspection data from a backtrace frame.
     *
     * @param array $frame The backtrace frame
     *
     * @return array The filtered introspection data
     */
    private function extractIntrospectionDataFromFrame(array $frame): array
    {
        $introspectionData = [
            'file' => $frame['file'] ?? null,
            'line' => $frame['line'] ?? null,
            'class' => $frame['class'] ?? null,
            'function' => $frame['function'] ?? null,
            'type' => $frame['type'] ?? null,
        ];

        return $this->filterNullValues($introspectionData);
    }

    /**
     * Filter out null values from the introspection data array.
     *
     * This ensures that only meaningful introspection data is included in the log context,
     * reducing noise and improving log readability.
     *
     * @param array $data The data to filter
     *
     * @return array The filtered data without null values
     */
    private function filterNullValues(array $data): array
    {
        return array_filter($data, static fn ($value): bool => null !== $value);
    }
}
