<?php

declare(strict_types=1);

namespace KaririCode\Logging\Contract\Logging;

use KaririCode\Logging\LoggerConfiguration;

interface LoggerConfigurableFactory
{
    public function initializeFromConfiguration(LoggerConfiguration $config): void;
}
