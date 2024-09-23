<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Util;

use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Script\Event;
use KaririCode\Logging\Util\AssetPublisher;
use PHPUnit\Framework\TestCase;

final class AssetPublisherTest extends TestCase
{
    private string $tempDir;
    private Event|\PHPUnit\Framework\MockObject\MockObject $eventMock;
    private Composer|\PHPUnit\Framework\MockObject\MockObject $composerMock;
    private Config|\PHPUnit\Framework\MockObject\MockObject $configMock;
    private IOInterface|\PHPUnit\Framework\MockObject\MockObject $ioMock;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/asset_publisher_test_' . uniqid();
        mkdir($this->tempDir);

        $this->eventMock = $this->createMock(Event::class);
        $this->composerMock = $this->createMock(Composer::class);
        $this->configMock = $this->createMock(Config::class);
        $this->ioMock = $this->createMock(IOInterface::class);

        $this->eventMock->method('getComposer')->willReturn($this->composerMock);
        $this->eventMock->method('getIO')->willReturn($this->ioMock);
        $this->composerMock->method('getConfig')->willReturn($this->configMock);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    public function testPublishAssetsCreatesTargetDirectoryAndCopiesFiles(): void
    {
        $vendorDir = $this->tempDir . '/vendor';
        $sourceDir = $vendorDir . '/kariricode/logging/resources';
        $targetDir = $this->tempDir . '/resources/logging';

        mkdir($sourceDir, 0777, true);
        file_put_contents($sourceDir . '/test.php', '<?php echo "test";');

        $this->configMock->method('get')->with('vendor-dir')->willReturn($vendorDir);

        $this->ioMock->expects($this->once())
            ->method('write')
            ->with($this->stringContains('Published assets to:'));

        AssetPublisher::publishAssets($this->eventMock);

        $this->assertDirectoryExists($targetDir);
        $this->assertFileExists($targetDir . '/test.php');
        $this->assertEquals('<?php echo "test";', file_get_contents($targetDir . '/test.php'));
    }

    public function c(): void
    {
        $vendorDir = $this->tempDir . '/non_existent_vendor';
        $this->configMock->method('get')->with('vendor-dir')->willReturn($vendorDir);

        $this->ioMock->expects($this->once())
            ->method('writeError')
            ->with($this->stringContains('Source directory not found:'));

        AssetPublisher::publishAssets($this->eventMock);

        $this->assertDirectoryDoesNotExist($this->tempDir . '/resources/logging');
    }

    public function testPublishAssetsHandlesExistingTargetDirectory(): void
    {
        $vendorDir = $this->tempDir . '/vendor';
        $sourceDir = $vendorDir . '/kariricode/logging/resources';
        $targetDir = $this->tempDir . '/resources/logging';

        mkdir($sourceDir, 0777, true);
        mkdir($targetDir, 0777, true);
        file_put_contents($sourceDir . '/test.php', '<?php echo "test";');

        $this->configMock->method('get')->with('vendor-dir')->willReturn($vendorDir);

        $this->ioMock->expects($this->once())
            ->method('write')
            ->with($this->stringContains('Published assets to:'));

        AssetPublisher::publishAssets($this->eventMock);

        $this->assertDirectoryExists($targetDir);
        $this->assertFileExists($targetDir . '/test.php');
        $this->assertEquals('<?php echo "test";', file_get_contents($targetDir . '/test.php'));
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ('.' === $object || '..' === $object) {
                continue;
            }

            $path = $dir . '/' . $object;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
