<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Util;

use KaririCode\Logging\Util\ConfigHelper;
use PHPUnit\Framework\TestCase;

class ConfigHelperTest extends TestCase
{
    public function testEnv(): void
    {
        putenv('TEST_ENV=true');
        $this->assertTrue(ConfigHelper::env('TEST_ENV'));
        putenv('TEST_ENV=false');
        $this->assertFalse(ConfigHelper::env('TEST_ENV'));
        putenv('TEST_ENV');
    }

    public function testStoragePath(): void
    {
        $this->assertStringContainsString('storage', ConfigHelper::storagePath());
    }

    public function testFindRootPath(): void
    {
        $this->assertStringContainsString('/app', ConfigHelper::findRootPath());
    }
}
