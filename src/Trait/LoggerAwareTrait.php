<?php

declare(strict_types=1);

namespace KaririCode\Logging\Trait;

use KaririCode\Contract\Logging\Logger;

trait LoggerAwareTrait
{
    protected Logger $logger;

    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }
}
