<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Logging\Util;

use KaririCode\Logging\Exception\LoggingException;
use KaririCode\Logging\Resilience\CircuitBreaker;
use KaririCode\Logging\Resilience\Fallback;
use KaririCode\Logging\Resilience\Retry;
use KaririCode\Logging\Util\CurlClient;
use KaririCode\Logging\Util\SlackClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SlackClientTest extends TestCase
{
    private SlackClient $slackClient;
    private CircuitBreaker|MockObject $circuitBreaker;
    private Retry|MockObject $retry;
    private Fallback|MockObject $fallback;
    private CurlClient|MockObject $curlClient;

    protected function setUp(): void
    {
        /** @var CircuitBreaker */
        $this->circuitBreaker = $this->createMock(CircuitBreaker::class);
        /** @var Retry */
        $this->retry = $this->createMock(Retry::class);
        /** @var Fallback */
        $this->fallback = $this->createMock(Fallback::class);
        /** @var CurlClient */
        $this->curlClient = $this->createMock(CurlClient::class);

        $this->slackClient = new SlackClient(
            'fake_bot_token',
            '#general',
            $this->circuitBreaker,
            $this->retry,
            $this->fallback,
            $this->curlClient
        );
    }

    public function testSendMessageSuccess(): void
    {
        $message = 'Test message';
        $payload = ['channel' => '#general', 'text' => $message];
        $response = ['status' => 200, 'body' => '{"ok": true}'];

        $this->circuitBreaker->expects($this->once())->method('isOpen')->willReturn(false);
        $this->circuitBreaker->expects($this->once())->method('recordSuccess');

        $this->curlClient->expects($this->once())->method('post')->with(
            'https://slack.com/api/chat.postMessage', // Hardcoded URL
            $payload,
            $this->callback(function ($headers) {
                return in_array('Content-Type: application/json; charset=utf-8', $headers, true)
                    && in_array('Authorization: Bearer fake_bot_token', $headers, true);
            })
        )->willReturn($response);

        $this->retry->expects($this->once())->method('execute')->willReturnCallback(function ($callback) {
            return $callback();
        });

        $this->fallback->expects($this->once())->method('execute')->willReturnCallback(function ($primary, $fallback) {
            return $primary();
        });

        $this->slackClient->sendMessage($message);
    }

    public function testSendMessageCircuitOpen(): void
    {
        $message = 'Test message';

        $this->circuitBreaker->expects($this->once())->method('isOpen')->willReturn(true);

        $this->fallback->expects($this->once())->method('execute')->willReturnCallback(function ($primary, $fallback) {
            $this->expectException(LoggingException::class);
            $this->expectExceptionMessage('Circuit is open, not sending message to Slack');
            $primary();
        });

        $this->slackClient->sendMessage($message);
    }

    public function testSendMessageCurlClientThrowsJsonException(): void
    {
        $message = 'Test message';
        $payload = ['channel' => '#general', 'text' => $message];

        $this->circuitBreaker->expects($this->once())->method('isOpen')->willReturn(false);
        $this->circuitBreaker->expects($this->once())->method('recordFailure');
        $this->curlClient->expects($this->once())->method('post')->with(
            'https://slack.com/api/chat.postMessage',
            $payload,
            $this->anything()
        )->willThrowException(new \JsonException('JSON encoding error'));

        $this->retry->expects($this->once())->method('execute')->willReturnCallback(function ($callback) {
            return $callback();
        });

        $this->fallback->expects($this->once())->method('execute')->willReturnCallback(function ($primary, $fallback) {
            $this->expectException(LoggingException::class);
            $this->expectExceptionMessage('Failed to encode message for Slack: JSON encoding error');
            $primary();
        });

        $this->slackClient->sendMessage($message);
    }

    public function testSendMessageCurlClientThrowsRuntimeException(): void
    {
        $message = 'Test message';
        $payload = ['channel' => '#general', 'text' => $message];

        $this->circuitBreaker->expects($this->once())->method('isOpen')->willReturn(false);
        $this->circuitBreaker->expects($this->once())->method('recordFailure');
        $this->curlClient->expects($this->once())->method('post')->with(
            'https://slack.com/api/chat.postMessage',
            $payload,
            $this->anything()
        )->willThrowException(new \RuntimeException('Curl error'));

        $this->retry->expects($this->once())->method('execute')->willReturnCallback(function ($callback) {
            return $callback();
        });

        $this->fallback->expects($this->once())->method('execute')->willReturnCallback(function ($primary, $fallback) {
            $this->expectException(LoggingException::class);
            $this->expectExceptionMessage('Failed to send message to Slack: Curl error');
            $primary();
        });

        $this->slackClient->sendMessage($message);
    }

    public function testSendMessageFallbackOperation(): void
    {
        $message = 'Test message';
        $exception = new LoggingException('Primary operation failed');

        $this->circuitBreaker->expects($this->once())->method('isOpen')->willReturn(false);
        $this->curlClient->expects($this->once())->method('post')->willThrowException($exception);
        $this->retry->expects($this->once())->method('execute')->willReturnCallback(function ($callback) {
            return $callback();
        });

        $fallbackCalled = false;
        $this->fallback->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function ($primary, $fallback) use (&$fallbackCalled) {
                try {
                    $primary();
                } catch (\Throwable $e) {
                    $this->assertInstanceOf(LoggingException::class, $e);
                    $this->assertEquals('Primary operation failed', $e->getMessage());
                    $fallbackCalled = true;
                    $fallback($e);
                }
            });

        $this->slackClient->sendMessage($message);

        $this->assertTrue($fallbackCalled, 'Fallback operation was not called');
    }
}
