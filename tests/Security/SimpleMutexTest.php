<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Security;

use KaririCode\Logging\Security\SimpleMutex;
use PHPUnit\Framework\TestCase;

final class SimpleMutexTest extends TestCase
{
    private SimpleMutex $simpleMutex;

    protected function setUp(): void
    {
        $this->simpleMutex = new SimpleMutex();
    }

    public function testLockUnlock(): void
    {
        $this->simpleMutex->lock();
        $this->assertTrue(true); // If no deadlock occurs, test passes

        $this->simpleMutex->unlock();
        $this->assertTrue(true); // If unlock works without error, test passes
    }
}
