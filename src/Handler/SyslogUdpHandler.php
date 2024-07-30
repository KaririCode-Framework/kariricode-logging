<?php

namespace KaririCode\Logging\Handler;

use KaririCode\Contract\ImmutableValue;
use KaririCode\Contract\Logging\LogLevel as LoggingLogLevel;
use KaririCode\Logging\LogLevel;

class SyslogUdpHandler extends AbstractHandler
{
    private \Socket|false $socket;

    public function __construct(
        private string $host,
        private int $port,
        LoggingLogLevel $minLevel = LogLevel::DEBUG
    ) {
        parent::__construct($minLevel);
        $this->initializeSocket();
    }

    protected function createSocket(): \Socket|false
    {
        return \socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    }

    private function initializeSocket(): void
    {
        $this->socket = $this->createSocket();

        if (false === $this->socket) {
            $this->handleSocketError('Failed to create socket');
        }
    }

    protected function sendToSocket(string $packet): bool
    {
        return \socket_sendto($this->socket, $packet, strlen($packet), 0, $this->host, $this->port);
    }

    public function handle(ImmutableValue $record): void
    {
        if (!$this->isHandling($record)) {
            return;
        }

        $message = $this->formatter->format($record);
        $packet = '<' . $this->getSyslogPriority($record->level) . '>' . $message;

        $sendResult = $this->sendToSocket($packet);
        if (false === $sendResult) {
            $this->handleSocketError('Socket sendto failed');
        }
    }

    private function handleSocketError(string $message): void
    {
        $errorCode = \socket_last_error();
        $errorMessage = \socket_strerror($errorCode);
        \error_log("$message with error: $errorMessage");
        throw new \RuntimeException(sprintf('%s: [%d] %s', $message, $errorCode, $errorMessage));
    }

    private function getSyslogPriority(LoggingLogLevel $level): int
    {
        $severityMap = [
            LogLevel::DEBUG->value => 7,
            LogLevel::INFO->value => 6,
            LogLevel::NOTICE->value => 5,
            LogLevel::WARNING->value => 4,
            LogLevel::ERROR->value => 3,
            LogLevel::CRITICAL->value => 2,
            LogLevel::ALERT->value => 1,
            LogLevel::EMERGENCY->value => 0,
        ];

        return 16 * 8 + $severityMap[$level->value] ?? 7; // 16 is the "local0" facility
    }

    public function __destruct()
    {
        if (is_resource($this->socket)) {
            \socket_close($this->socket);
        }
    }
}
