<?php

declare(strict_types=1);

namespace KaririCode\Logging\Handler;

use KaririCode\Contract\Logging\Logger;

class ExceptionHandler
{
    public function __construct(private readonly Logger $logger)
    {
    }

    public function handle(\Throwable $exception): void
    {
        $this->logger->error(
            $exception->getMessage(),
            [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]
        );
    }
}
