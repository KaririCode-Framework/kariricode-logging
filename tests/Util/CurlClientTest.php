<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Logging\Util;

use KaririCode\Logging\Util\CurlClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CurlClientTest extends TestCase
{
    private CurlClient|MockObject $curlClientMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->curlClientMock = $this->createCurlClientMock();
    }

    private function createCurlClientMock(): CurlClient|MockObject
    {
        return $this->createMock(CurlClient::class);
    }

    public function testPostSuccessful(): void
    {
        $url = 'https://api.example.com/endpoint';
        $data = ['key' => 'value'];
        $headers = ['Authorization: Bearer token'];
        $expectedResponse = ['status' => 200, 'body' => '{"success": true}'];

        $this->curlClientMock->method('post')->willReturn($expectedResponse);

        $response = $this->curlClientMock->post($url, $data, $headers);

        $this->assertSame($expectedResponse, $response);
    }

    public function testPostWithCurlInitializationError(): void
    {
        $url = 'https://api.example.com/endpoint';
        $data = ['key' => 'value'];
        $headers = [];

        $this->curlClientMock->method('post')
            ->will($this->throwException(new \RuntimeException('Failed to initialize cURL')));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to initialize cURL');

        $this->curlClientMock->post($url, $data, $headers);
    }

    public function testPostWithCurlExecutionError(): void
    {
        $url = 'https://api.example.com/endpoint';
        $data = ['key' => 'value'];
        $headers = [];
        $errorMessage = 'Connection timed out';

        $this->curlClientMock->method('post')
            ->will($this->throwException(new \RuntimeException('Failed to send request: ' . $errorMessage)));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to send request: ' . $errorMessage);

        $this->curlClientMock->post($url, $data, $headers);
    }

    public function testPostWithCurlError(): void
    {
        $url = 'https://api.example.com/endpoint';
        $data = ['key' => 'value'];
        $headers = [];

        $this->curlClientMock->method('post')
            ->willReturn(['status' => 500, 'body' => 'Internal Server Error']);

        $response = $this->curlClientMock->post($url, $data, $headers);

        $this->assertEquals(500, $response['status']);
        $this->assertEquals('Internal Server Error', $response['body']);
    }

    public function testPostWithSuccessAndHeaders(): void
    {
        $url = 'https://api.example.com/endpoint';
        $data = ['key' => 'value'];
        $headers = ['Authorization: Bearer token'];
        $expectedResponse = ['status' => 200, 'body' => '{"success": true}'];

        $this->curlClientMock->method('post')->willReturn($expectedResponse);

        $response = $this->curlClientMock->post($url, $data, $headers);

        $this->assertSame($expectedResponse, $response);
    }
}
