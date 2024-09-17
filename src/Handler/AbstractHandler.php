<?php

declare(strict_types=1);

namespace KaririCode\Logging\Handler;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Contract\Logging\LogFormatter;
use KaririCode\Contract\Logging\LogHandler;
use KaririCode\Contract\Logging\LogLevel as LoggingLogLevel;
use KaririCode\Contract\Logging\Structural\HandlerAware;
use KaririCode\Logging\LogLevel;

abstract class AbstractHandler implements LogHandler, HandlerAware
{
    protected array $handlers = [];
    protected LogFormatter $formatter;

    public function __construct(
        protected LoggingLogLevel $minLevel = LogLevel::DEBUG,
    ) {
    }

    public function setFormatter(LogFormatter $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }

    public function isHandling(ImmutableValue $record): bool
    {
        return $record->level->getValue() >= $this->minLevel->getValue();
    }

    public function addHandler(
        LogHandler $handler,
        ?LoggingLogLevel $level = null
    ): HandlerAware {
        $this->handlers[] = [
            'handler' => $handler,
            'level' => $level ?? $this->minLevel,
        ];

        return $this;
    }

    public function pushHandler(LogHandler $handler): HandlerAware
    {
        array_unshift($this->handlers, [
            'handler' => $handler,
            'level' => $this->minLevel,
        ]);

        return $this;
    }

    public function popHandler(): ?LogHandler
    {
        if (!empty($this->handlers)) {
            return array_shift($this->handlers)['handler'];
        }

        return null;
    }

    public function getHandlers(): array
    {
        return array_column($this->handlers, 'handler');
    }
}
