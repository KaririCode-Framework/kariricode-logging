<?php

declare(strict_types=1);

namespace KaririCode\Logging\Formatter;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Logging\LogRecord;

/**
 * JSON formatter optimized for direct property access.
 */
final class JsonFormatter extends AbstractFormatter
{
    private const JSON_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;

    /**
     * @param string $dateFormat Date format pattern
     * @param bool $includeContext Include context in output
     * @param bool $includeExtra Include extra data in output
     * @param bool $prettyPrint Pretty print JSON output
     * @param bool $includeStacktraces Include stack traces for exceptions
     */
    public function __construct(
        string $dateFormat = 'Y-m-d H:i:s',
        bool $includeContext = true,
        bool $includeExtra = false,
        public readonly bool $prettyPrint = false,
        public readonly bool $includeStacktraces = false
    ) {
        parent::__construct($dateFormat, $includeContext, $includeExtra);
    }

    /**
     * Format log record to JSON using direct property access.
     */
    public function format(ImmutableValue $record): string
    {
        if (!$record instanceof LogRecord) {
            throw new \InvalidArgumentException('Record must be an instance of LogRecord');
        }

        // Direct property access - no toArray() needed
        $data = [
            'datetime' => $this->formatTimestamp($record->datetime),
            'level' => $record->level->value,
            'message' => $record->getMessageAsString(),
        ];

        // Conditionally add context and extra
        if ($this->shouldIncludeContext($record->context)) {
            $data['context'] = $this->processContext($record->context);
        }

        if ($this->shouldIncludeExtra($record->extra)) {
            $data['extra'] = $record->extra;
        }

        return $this->encodeJson($data);
    }

    /**
     * Format batch of records.
     */
    public function formatBatch(array $records): string
    {
        $formattedRecords = array_map(
            fn ($record) => $this->prepareData($record),
            $records
        );

        return $this->encodeJson($formattedRecords);
    }

    /**
     * Prepare data from record using direct property access.
     */
    private function prepareData(ImmutableValue $record): array
    {
        if (!$record instanceof LogRecord) {
            throw new \InvalidArgumentException('Record must be an instance of LogRecord');
        }

        return [
            'datetime' => $this->formatTimestamp($record->datetime),
            'level' => $record->level->value,
            'message' => $record->getMessageAsString(),
            'context' => $record->hasContext() ? $this->processContext($record->context) : null,
            'extra' => $record->hasExtra() ? $record->extra : null,
        ];
    }

    /**
     * Process context to handle exceptions if needed.
     */
    private function processContext(array $context): array
    {
        if (!$this->includeStacktraces) {
            return $context;
        }

        // Handle exceptions in context
        foreach ($context as $key => $value) {
            if ($value instanceof \Throwable) {
                $context[$key] = $this->formatException($value);
            }
        }

        return $context;
    }

    /**
     * Format exception for JSON output.
     */
    private function formatException(\Throwable $exception): array
    {
        $formatted = [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];

        if ($this->includeStacktraces) {
            $formatted['trace'] = $exception->getTraceAsString();
        }

        if ($exception->getPrevious()) {
            $formatted['previous'] = $this->formatException($exception->getPrevious());
        }

        return $formatted;
    }

    /**
     * Encode data to JSON with configured options.
     */
    private function encodeJson(mixed $data): string
    {
        $options = self::JSON_OPTIONS;

        if ($this->prettyPrint) {
            $options |= JSON_PRETTY_PRINT;
        }

        return json_encode($data, $options);
    }
}
