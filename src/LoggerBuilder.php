<?php

declare(strict_types=1);

namespace KaririCode\Logging;

use KaririCode\Contract\Logging\LogFormatter;
use KaririCode\Contract\Logging\Logger;
use KaririCode\Contract\Logging\Structural\FormatterAware;
use KaririCode\Contract\Logging\Structural\HandlerAware;
use KaririCode\Contract\Logging\Structural\ProcessorAware;
use KaririCode\Logging\Formatter\LineFormatter;

class LoggerBuilder
{
    private string $name;
    private array $handlers = [];
    private array $processors = [];

    public function __construct(
        string $name,
        private LogFormatter $formatter = new LineFormatter()
    ) {
        $this->name = $name;
    }

    public function withHandler(HandlerAware $handler): self
    {
        $this->handlers[] = $handler;

        return $this;
    }

    public function withProcessor(ProcessorAware $processor): self
    {
        $this->processors[] = $processor;

        return $this;
    }

    public function withFormatter(FormatterAware $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }

    public function build(): Logger
    {
        return new LoggerManager(
            $this->name,
            $this->handlers,
            $this->processors,
            $this->formatter
        );
    }
}
