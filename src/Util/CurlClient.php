<?php

declare(strict_types=1);

namespace KaririCode\Logging\Util;

class CurlClient
{
    private const TIMEOUT = 30;
    private const DEFAULT_HEADERS = ['Content-Type: application/json'];

    public function post(string $url, array $data, array $headers = []): array
    {
        $ch = $this->initializeCurl($url);
        $this->setPostOptions($ch, $data, $headers);

        $response = $this->executeRequest($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return [
            'status' => $httpCode,
            'body' => $response,
        ];
    }

    /**
     * Set POST options for the cURL session.
     *
     * @param \CurlHandle $ch the cURL handle
     * @param array $data the data to be sent in the request body
     * @param array $headers additional headers to be sent with the request
     *
     * @throws \JsonException if there's an error encoding the data
     */
    private function setPostOptions(\CurlHandle $ch, array $data, array $headers): void
    {
        try {
            $payload = json_encode($data, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \JsonException('Failed to encode data: ' . $e->getMessage(), $e->getCode(), $e);
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::TIMEOUT,
        ]);
    }

    /**
     * Initialize a new cURL session.
     *
     * @param string $url the URL to initialize the cURL session with
     *
     * @throws \RuntimeException if cURL initialization fails
     *
     * @return \CurlHandle the cURL handle
     */
    private function initializeCurl(string $url): \CurlHandle
    {
        $ch = curl_init($url);
        if (false === $ch) {
            throw new \RuntimeException('Failed to initialize cURL');
        }

        return $ch;
    }

    /**
     * Execute the cURL request.
     *
     * @param \CurlHandle $ch the cURL handle
     *
     * @throws \RuntimeException if the request fails
     *
     * @return string the response body
     */
    private function executeRequest(\CurlHandle $ch): string
    {
        $response = curl_exec($ch);
        if (false === $response) {
            $error = curl_error($ch);
            throw new \RuntimeException('Failed to send request: ' . $error);
        }

        return $response;
    }
}
