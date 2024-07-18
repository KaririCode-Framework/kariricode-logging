<?php

declare(strict_types=1);

namespace KaririCode\Logging\Security;

class SimpleMutex implements Mutex
{
    private bool $locked = false;

    public function lock(): void
    {
        while ($this->locked) {
            usleep(100);
        }
        $this->locked = true;
    }

    public function unlock(): void
    {
        $this->locked = false;
    }
}
