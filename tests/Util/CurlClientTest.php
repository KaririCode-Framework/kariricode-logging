<?php

declare(strict_types=1);

namespace Tests\KaririCode\Logging\Util;

use KaririCode\Logging\Util\CurlClient;
use PHPUnit\Framework\TestCase;

final class CurlClientTest extends TestCase
{
    private CurlClient $curlClient;

    protected function setUp(): void
    {
        $this->curlClient = new CurlClient();
    }

    public function testPostSuccessful(): void
    {
        // Arrange
        $url = 'https://api.example.com/endpoint';
        $data = ['key' => 'value'];
        $headers = ['Authorization: Bearer token'];
        $expectedResponse = ['status' => 200, 'body' => '{"success": true}'];

        // Mock the CurlClient methods
        $curlClientMock = $this->createMock(CurlClient::class);
        $curlClientMock->method('post')->willReturn($expectedResponse);

        // Act
        $response = $curlClientMock->post($url, $data, $headers);

        // Assert
        $this->assertSame($expectedResponse, $response);
    }

    public function testPostWithJsonEncodingError(): void
    {
        // Arrange
        $url = 'https://api.example.com/endpoint';
        $data = ['key' => INF]; // INF cannot be JSON encoded
        $headers = [];

        // Assert
        $this->expectException(\JsonException::class);
        $this->expectExceptionMessage('Failed to encode data: Inf and NaN cannot be JSON encoded');

        // Act
        $this->curlClient->post($url, $data, $headers);
    }

    public function testPostWithCurlInitializationError(): void
    {
        // Arrange
        $url = 'https://api.example.com/endpoint';
        $data = ['key' => 'value'];
        $headers = [];

        // Mock the CurlClient methods
        $curlClientMock = $this->createMock(CurlClient::class);
        $curlClientMock->method('post')
            ->will($this->throwException(new \RuntimeException('Failed to initialize cURL')));

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to initialize cURL');

        // Act
        $curlClientMock->post($url, $data, $headers);
    }

    public function testPostWithCurlExecutionError(): void
    {
        // Arrange
        $url = 'https://api.example.com/endpoint';
        $data = ['key' => 'value'];
        $headers = [];
        $errorMessage = 'Connection timed out';

        // Mock the CurlClient methods
        $curlClientMock = $this->createMock(CurlClient::class);
        $curlClientMock->method('post')
            ->will($this->throwException(new \RuntimeException('Failed to send request: ' . $errorMessage)));

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to send request: ' . $errorMessage);

        // Act
        $curlClientMock->post($url, $data, $headers);
    }
}
