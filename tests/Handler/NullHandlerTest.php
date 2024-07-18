<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Handler;

use KaririCode\Logging\Handler\NullHandler;
use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use PHPUnit\Framework\TestCase;

class NullHandlerTest extends TestCase
{
    private NullHandler $nullHandler;

    protected function setUp(): void
    {
        $this->nullHandler = new NullHandler();
    }

    public function testHandle(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message');

        $this->nullHandler->handle($record);
        $this->assertTrue(true); // No exceptions mean the test passed
    }
}
