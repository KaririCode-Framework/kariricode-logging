<?php

declare(strict_types=1);

namespace KaririCode\Logging\Security;

interface Mutex
{
    public function lock(): void;

    public function unlock(): void;
}
