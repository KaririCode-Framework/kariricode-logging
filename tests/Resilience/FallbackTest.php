<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Resilience;

use KaririCode\Logging\Resilience\Fallback;
use PHPUnit\Framework\TestCase;

final class FallbackTest extends TestCase
{
    public function testPrimaryOperationSucceeds(): void
    {
        $fallback = new Fallback();
        $result = $fallback->execute(
            primaryOperation: fn () => 'primary',
            fallbackOperation: fn () => 'fallback'
        );

        $this->assertEquals('primary', $result);
    }

    public function testFallbackOperationExecutesOnFailure(): void
    {
        $fallback = new Fallback();
        $result = $fallback->execute(
            primaryOperation: fn () => throw new \Exception('Primary failed'),
            fallbackOperation: fn (\Throwable $e) => 'fallback: ' . $e->getMessage()
        );

        $this->assertEquals('fallback: Primary failed', $result);
    }

    public function testFallbackOperationReceivesException(): void
    {
        $fallback = new Fallback();
        $result = $fallback->execute(
            primaryOperation: fn () => throw new \RuntimeException('Specific error'),
            fallbackOperation: fn (\Throwable $e) => $e
        );

        $this->assertInstanceOf(\RuntimeException::class, $result);
        $this->assertEquals('Specific error', $result->getMessage());
    }

    public function testFallbackOperationNotSet(): void
    {
        $fallback = new Fallback();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Primary failed');

        $fallback->execute(
            primaryOperation: fn () => throw new \Exception('Primary failed')
        );
    }
}
