<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Resilience;

use KaririCode\Logging\Resilience\CircuitBreaker;
use PHPUnit\Framework\TestCase;

class CircuitBreakerTest extends TestCase
{
    private CircuitBreaker $circuitBreaker;

    protected function setUp(): void
    {
        $this->circuitBreaker = new CircuitBreaker(3, 2, 60); // Adding successThreshold
    }

    public function testInitialState(): void
    {
        $this->assertFalse($this->circuitBreaker->isOpen(), 'Circuit should be initially closed');
    }

    public function testCircuitRemainsClosedBelowThreshold(): void
    {
        $this->circuitBreaker->recordFailure();
        $this->circuitBreaker->recordFailure();
        $this->assertFalse($this->circuitBreaker->isOpen(), 'Circuit should remain closed below threshold');
    }

    public function testCircuitOpensAtThreshold(): void
    {
        $this->circuitBreaker->recordFailure();
        $this->circuitBreaker->recordFailure();
        $this->circuitBreaker->recordFailure();
        $this->assertTrue($this->circuitBreaker->isOpen(), 'Circuit should open at threshold');
    }

    public function testCircuitClosesAfterTimeout(): void
    {
        $circuitBreaker = new CircuitBreaker(3, 2, 1); // 1 second timeout for testing
        $circuitBreaker->recordFailure();
        $circuitBreaker->recordFailure();
        $circuitBreaker->recordFailure();
        $this->assertTrue($circuitBreaker->isOpen(), 'Circuit should be open');

        sleep(2); // Wait for timeout
        $this->assertFalse($circuitBreaker->isOpen(), 'Circuit should close after timeout');
    }

    public function testRecordSuccessResetsCircuitAfterSuccessThreshold(): void
    {
        $this->circuitBreaker->recordFailure();
        $this->circuitBreaker->recordFailure();
        $this->circuitBreaker->recordFailure();
        $this->assertTrue($this->circuitBreaker->isOpen(), 'Circuit should be open');

        $this->circuitBreaker->recordSuccess();
        $this->assertTrue($this->circuitBreaker->isOpen(), 'Circuit should still be open after one success');

        $this->circuitBreaker->recordSuccess();
        $this->assertFalse($this->circuitBreaker->isOpen(), 'Circuit should be closed after success threshold');
    }

    public function testForceOpen(): void
    {
        $this->circuitBreaker->forceOpen();
        $this->assertTrue($this->circuitBreaker->isOpen(), 'Circuit should be forcibly opened');
    }

    public function testForceClose(): void
    {
        $this->circuitBreaker->recordFailure();
        $this->circuitBreaker->recordFailure();
        $this->circuitBreaker->recordFailure();
        $this->circuitBreaker->forceClose();
        $this->assertFalse($this->circuitBreaker->isOpen(), 'Circuit should be forcibly closed');
    }

    public function testForceOpenOverridesFailureCount(): void
    {
        $this->circuitBreaker->forceOpen();
        $this->circuitBreaker->recordSuccess();
        $this->assertTrue($this->circuitBreaker->isOpen(), 'Forced open state should override success');
    }

    public function testCircuitRemainsOpenDuringTimeout(): void
    {
        $this->circuitBreaker->recordFailure();
        $this->circuitBreaker->recordFailure();
        $this->circuitBreaker->recordFailure();
        $this->assertTrue($this->circuitBreaker->isOpen(), 'Circuit should be open');

        // Check multiple times to ensure it stays open
        for ($i = 0; $i < 5; ++$i) {
            $this->assertTrue($this->circuitBreaker->isOpen(), 'Circuit should remain open during timeout');
            usleep(100000); // 0.1 second delay
        }
    }

    public function testFailureIncrementBehavior(): void
    {
        for ($i = 0; $i < 10; ++$i) {
            $this->circuitBreaker->recordFailure();
            $expectedState = $i >= 2; // Open after 3 failures (0-indexed)
            $this->assertEquals(
                $expectedState,
                $this->circuitBreaker->isOpen(),
                "Circuit state incorrect after $i failures"
            );
        }
    }

    public function testResetAfterTimeoutDoesNotImmediatelyReopen(): void
    {
        $circuitBreaker = new CircuitBreaker(3, 2, 1); // 1 second timeout
        $circuitBreaker->recordFailure();
        $circuitBreaker->recordFailure();
        $circuitBreaker->recordFailure();
        $this->assertTrue($circuitBreaker->isOpen(), 'Circuit should be open');

        sleep(2); // Wait for timeout
        $this->assertFalse($circuitBreaker->isOpen(), 'Circuit should close after timeout');

        $circuitBreaker->recordFailure();
        $this->assertFalse($circuitBreaker->isOpen(), 'Circuit should not immediately reopen after reset');
    }
}
