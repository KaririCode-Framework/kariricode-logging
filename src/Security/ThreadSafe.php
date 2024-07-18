<?php

declare(strict_types=1);

namespace KaririCode\Logging\Security;

class ThreadSafe
{
    public function __construct(private Mutex $mutex)
    {
    }

    public function synchronize(callable $callback): mixed
    {
        $this->mutex->lock();
        try {
            return $callback();
        } finally {
            $this->mutex->unlock();
        }
    }
}
