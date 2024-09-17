<?php

declare(strict_types=1);

namespace KaririCode\Logging\Util;

use KaririCode\Logging\Exception\LoggingException;
use KaririCode\Logging\Resilience\CircuitBreaker;
use KaririCode\Logging\Resilience\Fallback;
use KaririCode\Logging\Resilience\Retry;

class SlackClient
{
    private const SLACK_API_URL = 'https://slack.com/api/chat.postMessage';

    public function __construct(
        private string $botToken,
        private string $channel,
        private CircuitBreaker $circuitBreaker = new CircuitBreaker(3, 60),
        private Retry $retry = new Retry(3, 1000, 2, 100),
        private Fallback $fallback = new Fallback(),
        private CurlClient $curlClient = new CurlClient()
    ) {
    }

    public static function create(
        string $botToken,
        string $channel,
    ): self {
        return new self(
            $botToken,
            $channel
        );
    }

    public function sendMessage(string $message): void
    {
        $this->fallback->execute(
            primaryOperation: function () use ($message) {
                if ($this->circuitBreaker->isOpen()) {
                    throw new LoggingException('Circuit is open, not sending message to Slack');
                }

                $this->retry->execute(function () use ($message) {
                    $this->doSendMessage($message);
                });
            },
            fallbackOperation: function (\Throwable $e) {
                // Fallback operation, e.g., log to a local file
                error_log('Failed to send message to Slack: ' . $e->getMessage());
            }
        );
    }

    private function doSendMessage(string $message): void
    {
        $payload = $this->createPayload($message);
        $response = $this->sendRequest($payload);
        $this->handleResponse($response);
    }

    private function createPayload(string $message): array
    {
        return [
            'channel' => $this->channel,
            'text' => $message,
        ];
    }

    private function sendRequest(array $payload): array
    {
        $headers = [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Bearer ' . $this->botToken,
        ];

        try {
            return $this->curlClient->post(self::SLACK_API_URL, $payload, $headers);
        } catch (\JsonException $e) {
            $this->circuitBreaker->recordFailure();
            throw new LoggingException('Failed to encode message for Slack: ' . $e->getMessage(), 0, $e);
        } catch (\RuntimeException $e) {
            $this->circuitBreaker->recordFailure();
            throw new LoggingException('Failed to send message to Slack: ' . $e->getMessage(), 0, $e);
        }
    }

    private function handleResponse(array $response): void
    {
        $httpCode = $response['status'];
        $responseBody = json_decode($response['body'], true);

        if ($httpCode < 200 || $httpCode >= 300 || !$responseBody['ok']) {
            $this->circuitBreaker->recordFailure();
            $errorMessage = $responseBody['error'] ?? 'Unknown error';
            throw new LoggingException('Slack API responded with error: ' . $errorMessage);
        }

        $this->circuitBreaker->recordSuccess();
    }
}
