<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Security;

use KaririCode\Logging\Security\Mutex;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MutexTest extends TestCase
{
    private Mutex|MockObject $mutex;

    protected function setUp(): void
    {
        $this->mutex = $this->createMock(Mutex::class);
    }

    public function testLockUnlock(): void
    {
        $this->mutex->expects($this->once())->method('lock');
        $this->mutex->expects($this->once())->method('unlock');

        $this->mutex->lock();
        $this->mutex->unlock();
    }
}
