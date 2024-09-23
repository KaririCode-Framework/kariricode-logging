<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Util;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\Installer\PackageEvent;
use Composer\Package\PackageInterface;
use Composer\Script\Event;
use KaririCode\Logging\Util\AssetPublisher;
use KaririCode\Logging\Util\ComposerScripts;
use KaririCode\Logging\Util\ConfigGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TestConfigGenerator extends ConfigGenerator
{
    public static bool $generateConfigCalled = false;

    public static function generateConfig(Event $event): void
    {
        self::$generateConfigCalled = true;
    }
}

class TestAssetPublisher extends AssetPublisher
{
    public static bool $publishAssetsCalled = false;

    public static function publishAssets(Event $event): void
    {
        self::$publishAssetsCalled = true;
    }
}

final class ComposerScriptsTest extends TestCase
{
    private PackageEvent|MockObject $packageEventMock;
    private InstallOperation|MockObject $operationMock;
    private PackageInterface|MockObject $packageMock;

    protected function setUp(): void
    {
        $this->packageEventMock = $this->createMock(PackageEvent::class);
        $this->operationMock = $this->createMock(InstallOperation::class);
        $this->packageMock = $this->createMock(PackageInterface::class);

        $this->packageEventMock->method('getOperation')->willReturn($this->operationMock);
        $this->operationMock->method('getPackage')->willReturn($this->packageMock);

        TestConfigGenerator::$generateConfigCalled = false;
        TestAssetPublisher::$publishAssetsCalled = false;

        $this->mockClassInNamespace(ComposerScripts::class, 'ConfigGenerator', TestConfigGenerator::class);
        $this->mockClassInNamespace(ComposerScripts::class, 'AssetPublisher', TestAssetPublisher::class);
    }

    public function testPostPackageInstallForOtherPackages(): void
    {
        $this->packageMock->method('getName')->willReturn('some/other-package');

        ComposerScripts::postPackageInstall($this->packageEventMock);

        $this->assertFalse(TestConfigGenerator::$generateConfigCalled, 'ConfigGenerator::generateConfig should not have been called');
        $this->assertFalse(TestAssetPublisher::$publishAssetsCalled, 'AssetPublisher::publishAssets should not have been called');
    }

    private function mockClassInNamespace(string $namespace, string $className, string $mockClass): void
    {
        $reflector = new \ReflectionClass($namespace);
        $namespaceName = $reflector->getNamespaceName();
        $alias = "{$namespaceName}\\{$className}";
        if (!class_exists($alias)) {
            class_alias($mockClass, $alias);
        }
    }

    protected function tearDown(): void
    {
        // Reset the mocked classes
        $this->mockClassInNamespace(ComposerScripts::class, 'ConfigGenerator', ConfigGenerator::class);
        $this->mockClassInNamespace(ComposerScripts::class, 'AssetPublisher', AssetPublisher::class);
    }
}
