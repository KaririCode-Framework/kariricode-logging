<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Util;

use Composer\Installer\PackageEvent;
use KaririCode\Logging\Util\ComposerScripts;
use PHPUnit\Framework\TestCase;

class ComposerScriptsTest extends TestCase
{
    public function testPostPackageInstall(): void
    {
        $event = $this->createMock(PackageEvent::class);

        $operation = $this->createMock(\Composer\DependencyResolver\Operation\InstallOperation::class);
        $package = $this->createMock(\Composer\Package\Package::class);
        $package->method('getName')->willReturn('kariricode/logging');
        $operation->method('getPackage')->willReturn($package);
        $event->method('getOperation')->willReturn($operation);

        ComposerScripts::postPackageInstall($event);
        $this->assertTrue(true); // If no exception occurs, test passes
    }
}
