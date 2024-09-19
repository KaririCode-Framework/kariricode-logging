<?php

declare(strict_types=1);

namespace Tests\KaririCode\Logging\Handler;

use KaririCode\Contract\Logging\LogFormatter;
use KaririCode\Logging\Handler\SlackHandler;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Util\SlackClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SlackHandlerTest extends TestCase
{
    private SlackClient|MockObject $slackClient;
    private LogFormatter|MockObject $formatter;
    private SlackHandler $handler;

    protected function setUp(): void
    {
        $this->slackClient = $this->createMock(SlackClient::class);
        $this->formatter = $this->createMock(LogFormatter::class);
        $this->handler = new SlackHandler(
            $this->slackClient,
            LogLevel::CRITICAL,
            $this->formatter
        );
    }

    public function testHandleWithCriticalLevel(): void
    {
        $record = new LogRecord(LogLevel::CRITICAL, 'Critical error occurred');
        $formattedMessage = 'Formatted: Critical error occurred';

        $this->formatter->expects($this->once())
            ->method('format')
            ->with($record)
            ->willReturn($formattedMessage);

        $this->slackClient->expects($this->once())
            ->method('sendMessage')
            ->with($formattedMessage);

        $this->handler->handle($record);
    }

    public function testHandleWithLowerLevel(): void
    {
        $record = new LogRecord(LogLevel::WARNING, 'Warning message');

        $this->formatter->expects($this->never())
            ->method('format');

        $this->slackClient->expects($this->never())
            ->method('sendMessage');

        $this->handler->handle($record);
    }

    public function testHandleWithCustomMinLevel(): void
    {
        $handler = new SlackHandler($this->slackClient, LogLevel::INFO, $this->formatter);
        $record = new LogRecord(LogLevel::INFO, 'Test message');
        $formattedMessage = 'Formatted: Test message';

        $this->formatter->expects($this->once())
            ->method('format')
            ->with($record)
            ->willReturn($formattedMessage);

        $this->slackClient->expects($this->once())
            ->method('sendMessage')
            ->with($formattedMessage);

        $handler->handle($record);
    }

    public function testHandleWithExactMinLevel(): void
    {
        $record = new LogRecord(LogLevel::CRITICAL, 'Exact min level message');
        $formattedMessage = 'Formatted: Exact min level message';

        $this->formatter->expects($this->once())
            ->method('format')
            ->with($record)
            ->willReturn($formattedMessage);

        $this->slackClient->expects($this->once())
            ->method('sendMessage')
            ->with($formattedMessage);

        $this->handler->handle($record);
    }

    public function testHandleWithHigherLevel(): void
    {
        $handler = new SlackHandler($this->slackClient, LogLevel::WARNING, $this->formatter);
        $record = new LogRecord(LogLevel::ERROR, 'Error message');
        $formattedMessage = 'Formatted: Error message';

        $this->formatter->expects($this->once())
            ->method('format')
            ->with($record)
            ->willReturn($formattedMessage);

        $this->slackClient->expects($this->once())
            ->method('sendMessage')
            ->with($formattedMessage);

        $handler->handle($record);
    }
}
