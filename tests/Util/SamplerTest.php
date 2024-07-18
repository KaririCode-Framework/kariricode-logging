<?php

declare(strict_types=1);

namespace Tests\KaririCode\Logging\Util;

use KaririCode\Logging\Util\Sampler;
use PHPUnit\Framework\TestCase;

class SamplerTest extends TestCase
{
    public function testConstructorWithValidSampleRate(): void
    {
        $sampler = new Sampler(0.5);
        $this->assertInstanceOf(Sampler::class, $sampler);
    }

    public function testConstructorWithInvalidSampleRate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample rate must be between 0 and 1');
        new Sampler(1.5);
    }

    public function testShouldSampleWithZeroRate(): void
    {

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sample rate must be between 0 and 1');
        $sampler = new Sampler(0);
    }

    public function testShouldSampleWithFullRate(): void
    {
        $sampler = new Sampler(1);
        $this->assertTrue($sampler->shouldSample());
    }

    public function testShouldSampleDistribution(): void
    {
        $sampler = new Sampler(0.5);
        $samples = 10000;
        $trueCount = 0;

        for ($i = 0; $i < $samples; $i++) {
            if ($sampler->shouldSample()) {
                $trueCount++;
            }
        }

        $actualRate = $trueCount / $samples;
        $this->assertEqualsWithDelta(0.5, $actualRate, 0.05);
    }
}
