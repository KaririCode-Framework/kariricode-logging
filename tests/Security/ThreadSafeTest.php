<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Security;

use KaririCode\Logging\Security\Mutex;
use KaririCode\Logging\Security\ThreadSafe;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ThreadSafeTest extends TestCase
{
    private ThreadSafe $threadSafe;
    private Mutex|MockObject $mutex;

    protected function setUp(): void
    {
        $this->mutex = $this->createMock(Mutex::class);
        $this->threadSafe = new ThreadSafe($this->mutex);
    }

    public function testSynchronize(): void
    {
        $this->mutex->expects($this->once())->method('lock');
        $this->mutex->expects($this->once())->method('unlock');

        $result = $this->threadSafe->synchronize(function () {
            return 'test';
        });

        $this->assertEquals('test', $result);
    }
}
