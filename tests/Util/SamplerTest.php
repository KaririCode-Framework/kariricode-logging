<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Util;

use KaririCode\Logging\Util\Sampler;
use PHPUnit\Framework\TestCase;

class SamplerTest extends TestCase
{
    public function testShouldSample(): void
    {
        $sampler = new Sampler(1.0);
        $this->assertTrue($sampler->shouldSample());

        $sampler = new Sampler(0.0);
        $this->assertFalse($sampler->shouldSample());
    }

    public function testInvalidSampleRate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Sampler(1.5);
    }
}
