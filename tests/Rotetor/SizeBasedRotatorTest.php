<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Logging\Rotator;

use KaririCode\Logging\Rotator\SizeBasedRotator;
use PHPUnit\Framework\TestCase;

final class SizeBasedRotatorTest extends TestCase
{
    private string $tempDir;
    private string $logFile;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/rotator_test_' . uniqid();
        mkdir($this->tempDir);
        $this->logFile = $this->tempDir . '/test.log';
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->removeDirectory("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }

    public function testShouldRotateReturnsFalseForNonExistentFile(): void
    {
        $rotator = new SizeBasedRotator();
        $this->assertFalse($rotator->shouldRotate($this->logFile));
    }

    public function testShouldRotateReturnsFalseForSmallFile(): void
    {
        file_put_contents($this->logFile, 'Small content');
        $rotator = new SizeBasedRotator(5, 100);
        $this->assertFalse($rotator->shouldRotate($this->logFile));
    }

    public function testShouldRotateReturnsTrueForLargeFile(): void
    {
        file_put_contents($this->logFile, str_repeat('a', 6 * 1024 * 1024));
        $rotator = new SizeBasedRotator();
        $this->assertTrue($rotator->shouldRotate($this->logFile));
    }

    public function testRotateCreatesCorrectNumberOfBackups(): void
    {
        file_put_contents($this->logFile, 'Original content');
        $rotator = new SizeBasedRotator(3);
        $rotator->rotate($this->logFile);

        $this->assertFileExists("{$this->logFile}.1");
        $this->assertFileDoesNotExist("{$this->logFile}.2");
        $this->assertFileDoesNotExist("{$this->logFile}.3");
    }

    public function testRotateShiftsExistingBackups(): void
    {
        file_put_contents($this->logFile, 'Original content');
        file_put_contents("{$this->logFile}.1", 'Backup 1');
        file_put_contents("{$this->logFile}.2", 'Backup 2');

        $rotator = new SizeBasedRotator(4);
        $rotator->rotate($this->logFile);

        $this->assertFileExists("{$this->logFile}.1");
        $this->assertFileExists("{$this->logFile}.2");
        $this->assertFileExists("{$this->logFile}.3");
        $this->assertStringEqualsFile("{$this->logFile}.1", 'Original content');
        $this->assertStringEqualsFile("{$this->logFile}.2", 'Backup 1');
        $this->assertStringEqualsFile("{$this->logFile}.3", 'Backup 2');
    }

    public function testRotateRemovesOldestBackupWhenMaxFilesReached(): void
    {
        file_put_contents($this->logFile, 'Original content');
        file_put_contents("{$this->logFile}.1", 'Backup 1');
        file_put_contents("{$this->logFile}.2", 'Backup 2');

        $rotator = new SizeBasedRotator(3);
        $rotator->rotate($this->logFile);

        $this->assertFileExists("{$this->logFile}.1");
        $this->assertFileExists("{$this->logFile}.2");
        $this->assertFileDoesNotExist("{$this->logFile}.3");
        $this->assertStringEqualsFile("{$this->logFile}.1", 'Original content');
        $this->assertStringEqualsFile("{$this->logFile}.2", 'Backup 1');
    }

    public function testRotateThrowsExceptionOnFailure(): void
    {
        $this->expectException(\RuntimeException::class);

        $rotatorMock = $this->createMock(SizeBasedRotator::class);
        $rotatorMock->method('rotate')
            ->willThrowException(new \RuntimeException('Mock exception'));

        file_put_contents($this->logFile, 'Original content');
        $rotatorMock->rotate($this->logFile);
    }
}
