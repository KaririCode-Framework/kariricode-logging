<?php

declare(strict_types=1);

namespace KaririCode\Logging\Util;

use KaririCode\Logging\Exception\LoggingException;
use KaririCode\Logging\Resilience\CircuitBreaker;
use KaririCode\Logging\Resilience\Fallback;
use KaririCode\Logging\Resilience\Retry;

class SlackClient
{
    private readonly string $webhookUrl;
    private readonly CircuitBreaker $circuitBreaker;
    private readonly Retry $retry;
    private readonly Fallback $fallback;
    private readonly CurlClient $curlClient;

    public function __construct(
        string $webhookUrl,
        CircuitBreaker $circuitBreaker,
        Retry $retry,
        Fallback $fallback,
        CurlClient $curlClient
    ) {
        $this->setWebhookUrl($webhookUrl);
        $this->circuitBreaker = $circuitBreaker;
        $this->retry = $retry;
        $this->fallback = $fallback;
        $this->curlClient = $curlClient;
    }

    public static function create(
        string $webhookUrl,
        ?CircuitBreaker $circuitBreaker = null,
        ?Retry $retry = null,
        ?Fallback $fallback = null,
        ?CurlClient $curlClient = null
    ): self {
        return new self(
            $webhookUrl,
            $circuitBreaker ?? new CircuitBreaker(3, 60),
            $retry ?? new Retry(3, 1000, 2, 100),
            $fallback ?? new Fallback(),
            $curlClient ?? new CurlClient()
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
        return ['text' => $message];
    }

    private function sendRequest(array $payload): array
    {
        try {
            return $this->curlClient->post($this->webhookUrl, $payload);
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
        $responseBody = $response['body'];

        if ($httpCode < 200 || $httpCode >= 300) {
            $this->circuitBreaker->recordFailure();
            throw new LoggingException('Slack API responded with HTTP code ' . $httpCode . ': ' . $responseBody);
        }

        $this->circuitBreaker->recordSuccess();
    }

    private function setWebhookUrl(string $webhookUrl): void
    {
        if (false === filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid webhook URL');
        }
        $this->webhookUrl = $webhookUrl;
    }

    public function getWebhookUrl(): string
    {
        return $this->webhookUrl;
    }
}
