<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Util;

use Composer\Script\Event;
use KaririCode\Logging\Util\ConfigGenerator;
use PHPUnit\Framework\TestCase;

class ConfigGeneratorTest extends TestCase
{
    public function testGenerateConfig(): void
    {
        $event = $this->createMock(Event::class);
        $config = $this->createMock(\Composer\Config::class);

        $event->method('getComposer')->willReturnSelf();
        $event->method('getConfig')->willReturn($config);
        $config->method('get')->with('vendor-dir')->willReturn(__DIR__);

        ConfigGenerator::generateConfig($event);

        $this->assertFileExists(__DIR__ . '/config/logging.php');
    }
}
