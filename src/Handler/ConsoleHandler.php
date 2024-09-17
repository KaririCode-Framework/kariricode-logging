<?php

declare(strict_types=1);

namespace KaririCode\Logging\Handler;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Contract\Logging\LogFormatter;
use KaririCode\Contract\Logging\LogLevel as LoggingLogLevel;
use KaririCode\Logging\Formatter\ConsoleColorFormatter;
use KaririCode\Logging\Formatter\LineFormatter;
use KaririCode\Logging\LogLevel;

class ConsoleHandler extends AbstractHandler
{
    private mixed $output;
    private ConsoleColorFormatter $colorFormatter;

    public function __construct(
        LoggingLogLevel $minLevel = LogLevel::DEBUG,
        private readonly bool $useColors = true,
        LogFormatter $formatter = new LineFormatter()
    ) {
        parent::__construct($minLevel, $formatter);
        $this->output = fopen('php://stdout', 'w');
        $this->setFormatter($formatter);
        $this->colorFormatter = new ConsoleColorFormatter();
    }

    public function handle(ImmutableValue $record): void
    {
        if (!$this->isHandling($record)) {
            return;
        }

        $message = $this->formatter->format($record);
        if ($this->useColors) {
            $message = $this->colorFormatter->format($record->level, $message);
        }
        fwrite($this->output, $message . PHP_EOL);
    }

    public function __destruct()
    {
        if (is_resource($this->output)) {
            fclose($this->output);
        }
    }
}
