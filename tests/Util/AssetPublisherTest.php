<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\KaririCode\Logging\Util;

use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Script\Event;
use KaririCode\Logging\Util\AssetPublisher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AssetPublisherTest extends TestCase
{
    private string $tempDir;
    private string $vendorDir;
    private string $sourceDir;
    private string $targetDir;
    private Event|MockObject $event;
    private IOInterface|MockObject $io;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/asset_publisher_test_' . uniqid();
        mkdir($this->tempDir);
        $this->vendorDir = $this->tempDir . '/vendor';
        $this->sourceDir = $this->vendorDir . '/kariricode/logging/resources';
        $this->targetDir = $this->tempDir . '/resources/logging';

        // Create mock objects
        $composer = $this->createMock(Composer::class);
        $config = $this->createMock(Config::class);
        $this->io = $this->createMock(IOInterface::class);
        $this->event = $this->createMock(Event::class);

        // Set up expectations
        $composer->method('getConfig')->willReturn($config);
        $config->method('get')->with('vendor-dir')->willReturn($this->vendorDir);
        $this->event->method('getComposer')->willReturn($composer);
        $this->event->method('getIO')->willReturn($this->io);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    private function removeDirectory(string $dir): void
    {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testPublishAssetsSuccessfully(): void
    {
        // Arrange
        mkdir($this->sourceDir . '/css', 0755, true);
        file_put_contents($this->sourceDir . '/css/style.css', 'body { color: black; }');

        // Act
        AssetPublisher::publishAssets($this->event);

        // Assert
        $this->assertDirectoryExists($this->targetDir);
        $this->assertDirectoryExists($this->targetDir . '/css');
        $this->assertFileExists($this->targetDir . '/css/style.css');
        $this->assertEquals('body { color: black; }', file_get_contents($this->targetDir . '/css/style.css'));
    }

    public function testPublishAssetsWhenSourceDirectoryDoesNotExist(): void
    {
        // Arrange
        $this->io->expects($this->once())
            ->method('write')
            ->with($this->stringContains('Source directory not found'));

        // Act
        AssetPublisher::publishAssets($this->event);

        // Assert
        $this->assertDirectoryDoesNotExist($this->targetDir);
    }

    public function testPublishAssetsWhenTargetDirectoryCannotBeCreated(): void
    {
        // Arrange
        mkdir($this->sourceDir, 0755, true);
        mkdir(dirname($this->targetDir), 0000, true); // Make parent directory inaccessible

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Directory "%s" was not created', $this->targetDir));

        // Act
        try {
            AssetPublisher::publishAssets($this->event);
        } finally {
            chmod(dirname($this->targetDir), 0755); // Restore permissions to allow cleanup
        }
    }

    public function testPublishAssetsWhenCopyFails(): void
    {
        // Arrange
        mkdir($this->sourceDir, 0755, true);
        file_put_contents($this->sourceDir . '/test.txt', 'Test content');

        // Cria o diretório de destino, mas com permissões que impedem a escrita
        mkdir(dirname($this->targetDir), 0555, true);

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Directory "%s" was not created', $this->targetDir));

        // Act
        try {
            AssetPublisher::publishAssets($this->event);
        } finally {
            // Restaura as permissões para permitir a limpeza
            chmod(dirname($this->targetDir), 0755);
        }
    }

    public function testPublishAssetsWithSubdirectories(): void
    {
        // Arrange
        mkdir($this->sourceDir . '/css/nested', 0755, true);
        file_put_contents($this->sourceDir . '/css/style.css', 'body { color: black; }');
        file_put_contents($this->sourceDir . '/css/nested/nested.css', '.nested { display: none; }');

        // Act
        AssetPublisher::publishAssets($this->event);

        // Assert
        $this->assertDirectoryExists($this->targetDir . '/css/nested');
        $this->assertFileExists($this->targetDir . '/css/style.css');
        $this->assertFileExists($this->targetDir . '/css/nested/nested.css');
        $this->assertEquals('body { color: black; }', file_get_contents($this->targetDir . '/css/style.css'));
        $this->assertEquals('.nested { display: none; }', file_get_contents($this->targetDir . '/css/nested/nested.css'));
    }
}
