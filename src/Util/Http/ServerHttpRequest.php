<?php

declare(strict_types=1);

namespace KaririCode\Logging\Util\Http;

use KaririCode\Logging\Util\Http\Contract\HttpRequest;

class ServerHttpRequest implements HttpRequest
{
    private array $serverParams;

    public function __construct(array $serverParams = [])
    {
        $this->serverParams = array_merge($_SERVER, $serverParams);
    }

    public function getUrl(): string
    {
        $scheme = ($this->serverParams['HTTPS'] ?? 'off') === 'on' ? 'https://' : 'http://';
        $host = $this->serverParams['HTTP_HOST'] ?? 'localhost';
        $uri = $this->serverParams['REQUEST_URI'] ?? '/';

        return $scheme . $host . $uri;
    }

    public function getIp(): ?string
    {
        return $this->serverParams['REMOTE_ADDR'] ?? null;
    }

    public function getMethod(): string
    {
        return $this->serverParams['REQUEST_METHOD'] ?? 'GET';
    }

    public function getServerName(): ?string
    {
        return $this->serverParams['SERVER_NAME'] ?? null;
    }

    public function getReferrer(): ?string
    {
        return $this->serverParams['HTTP_REFERER'] ?? null;
    }
}
