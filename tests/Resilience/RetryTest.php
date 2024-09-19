<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Resilience;

use KaririCode\Logging\Resilience\Retry;
use PHPUnit\Framework\TestCase;

final class RetryTest extends TestCase
{
    public function testExecuteHappyPath(): void
    {
        $retry = new Retry(maxAttempts: 3, delay: 1, multiplier: 2, jitter: 100);

        $result = $retry->execute(function () {
            return 'success';
        });

        $this->assertEquals('success', $result);
    }

    public function testExecuteWithRetries(): void
    {
        $retry = new Retry(maxAttempts: 3, delay: 1, multiplier: 2, jitter: 100);
        $attempts = 0;

        $result = $retry->execute(function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 3) {
                throw new \Exception('Fail');
            }

            return 'success';
        });

        $this->assertEquals('success', $result);
        $this->assertEquals(3, $attempts);
    }

    public function testThrowsExceptionAfterMaxAttempts(): void
    {
        $retry = new Retry(maxAttempts: 3, delay: 100, multiplier: 2, jitter: 100);
        $this->expectException(\Exception::class);
        $retry->execute(function () {
            throw new \Exception('Persistent failure');
        });
    }

    public function testExponentialBackoffWithJitter(): void
    {
        $retry = new Retry(maxAttempts: 3, delay: 100, multiplier: 2, jitter: 50);
        $attempts = 0;

        $start = microtime(true);

        try {
            $retry->execute(function () use (&$attempts) {
                ++$attempts;
                throw new \Exception('Fail');
            });
        } catch (\Exception $e) {
            // Expected exception
        }

        $end = microtime(true);

        $this->assertEquals(3, $attempts);

        // Calculate the minimum expected delay taking jitter into account
        $minimumExpectedDelay = (100 + 0) + (100 * 2 + 0); // Jitter is random, so we calculate minimum
        $elapsedTime = $end - $start;

        // Allow some tolerance for timing variations
        $this->assertGreaterThanOrEqual($minimumExpectedDelay / 1000, $elapsedTime, 'Elapsed time is less than expected minimum delay');
    }
}
