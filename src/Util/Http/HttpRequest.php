<?php

declare(strict_types=1);

namespace KaririCode\Logging\Util\Http\Contract;

interface HttpRequest
{
    public function getUrl(): string;
    public function getIp(): ?string;
    public function getMethod(): string;
    public function getServerName(): ?string;
    public function getReferrer(): ?string;
}
