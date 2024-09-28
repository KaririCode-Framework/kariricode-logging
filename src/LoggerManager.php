<?php

declare(strict_types=1);

namespace KaririCode\Logging;

use KaririCode\Contract\Logging\LogFormatter;
use KaririCode\Contract\Logging\Logger;
use KaririCode\Contract\Logging\LogLevel;
use KaririCode\Contract\Logging\Structural\HandlerAware;
use KaririCode\Contract\Logging\Structural\ProcessorAware;
use KaririCode\Logging\Formatter\LineFormatter;
use KaririCode\Logging\Trait\LoggerTrait;

class LoggerManager implements Logger
{
    use LoggerTrait;

    private array $thresholds = [];

    public function __construct(
        private readonly string $name,
        private array $handlers = [],
        private array $processors = [],
        private LogFormatter $formatter = new LineFormatter()
    ) {
    }

    public function setThreshold(string $key, int $value): void
    {
        $this->thresholds[$key] = $value;
    }

    public function log(LogLevel $level, string|\Stringable $message, array $context = []): void
    {
        if (!$this->passesThreshold($context)) {
            return;
        }

        $record = new LogRecord($level, $message, $context);

        foreach ($this->processors as $processor) {
            $record = $processor->process($record);
        }

        foreach ($this->handlers as $handler) {
            $handler->handle($record);
        }
    }

    private function passesThreshold(array $context): bool
    {
        foreach ($this->thresholds as $key => $threshold) {
            if ($this->contextBelowThreshold($context, $key, $threshold)) {
                return false;
            }
        }

        return true;
    }

    private function contextBelowThreshold(array $context, string $key, int $threshold): bool
    {
        return isset($context[$key]) && $context[$key] < $threshold;
    }

    public function addHandler(HandlerAware $handler): self
    {
        $this->handlers[] = $handler;

        return $this;
    }

    public function addProcessor(ProcessorAware $processor): self
    {
        $this->processors[] = $processor;

        return $this;
    }

    public function setFormatter(LogFormatter $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * @return HandlerInterface[]
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * @return ProcessorInterface[]
     */
    public function getProcessors(): array
    {
        return $this->processors;
    }

    public function getFormatter(): LogFormatter
    {
        return $this->formatter;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
