<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Exception;

use KaririCode\Logging\Exception\LoggingException;
use PHPUnit\Framework\TestCase;

class LoggingExceptionTest extends TestCase
{
    public function testExceptionMessage(): void
    {
        $exception = new LoggingException('Test exception message');
        $this->assertEquals('Test exception message', $exception->getMessage());
    }

    public function testExceptionCode(): void
    {
        $exception = new LoggingException('Test exception message', 123);
        $this->assertEquals(123, $exception->getCode());
    }
}
