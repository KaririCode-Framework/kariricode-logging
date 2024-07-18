<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Resilience;

use KaririCode\Logging\LogLevel;
use KaririCode\Logging\LogRecord;
use KaririCode\Logging\Resilience\RateLimiter;
use PHPUnit\Framework\TestCase;

class RateLimiterTest extends TestCase
{
    private RateLimiter $rateLimiter;

    protected function setUp(): void
    {
        $this->rateLimiter = new RateLimiter(2, 1);
    }

    public function testAllowWithinLimit(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message');

        $this->assertTrue($this->rateLimiter->allow($record));
        $this->assertTrue($this->rateLimiter->allow($record));
    }

    public function testDenyBeyondLimit(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message');

        $this->assertTrue($this->rateLimiter->allow($record));
        $this->assertTrue($this->rateLimiter->allow($record));
        $this->assertFalse($this->rateLimiter->allow($record));
    }

    public function testAllowAfterInterval(): void
    {
        $record = new LogRecord(LogLevel::INFO, 'Test message');

        $this->assertTrue($this->rateLimiter->allow($record));
        $this->assertTrue($this->rateLimiter->allow($record));
        sleep(1); // Wait for interval
        $this->assertTrue($this->rateLimiter->allow($record));
    }
}
