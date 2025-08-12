<?php

declare(strict_types=1);

namespace KaririCode\Logging;

use KaririCode\Contract\Logging\Logger;
use KaririCode\Contract\Logging\LogLevel;
use KaririCode\Logging\Trait\LoggerTrait;

/**
 * Implements the Null Object Pattern for the Logger interface using a semantic name.
 *
 * This logger silently discards all log records it receives. It can be used
 * as a default or fallback logger to disable logging for a specific channel
 * or to prevent errors when a logger instance is not available, completely
 * eliminating the need for conditional checks (`if ($logger)`).
 */
final class SilentLogger implements Logger
{
    use LoggerTrait;

    public function __construct(private readonly string $name = 'silent')
    {
    }

    /**
     * Silently discards any log message sent to it.
     *
     * This method is the core of the Null Object Pattern for this class.
     * It accepts all the parameters required by the Logger interface but
     * intentionally has no implementation.
     */
    public function log(LogLevel $level, \Stringable|string $message, array $context = []): void
    {
        // This logger does nothing, by design.
    }

    /**
     * Returns the name of this logger instance.
     */
    public function getName(): string
    {
        return $this->name;
    }
}
