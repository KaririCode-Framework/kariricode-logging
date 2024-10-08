<?php

declare(strict_types=1);

namespace KaririCode\Logging\Handler;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Contract\Logging\LogFormatter;
use KaririCode\Contract\Logging\LogLevel as LoggingLogLevel;
use KaririCode\Logging\Formatter\LineFormatter;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\Util\SlackClient;

class SlackHandler extends AbstractHandler
{
    public function __construct(
        private SlackClient $slackClient,
        protected LoggingLogLevel $minLevel = LogLevel::CRITICAL,
        protected LogFormatter $formatter = new LineFormatter(),
    ) {
        parent::__construct($minLevel, $formatter);
    }

    public function handle(ImmutableValue $record): void
    {
        if (!$this->isHandling($record)) {
            return;
        }

        $message = $this->formatter->format($record);
        $this->slackClient->sendMessage($message);
    }
}
