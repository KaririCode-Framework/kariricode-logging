<?php

declare(strict_types=1);

namespace KaririCode\Logging\Util;

use Composer\Script\Event;

class AssetPublisher
{
    private const DIRECTORY_PERMISSIONS = 0755;

    public static function publishAssets(Event $event): void
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $sourceDir = $vendorDir . '/kariricode/logging/resources';
        $targetDir = dirname($vendorDir) . '/resources/logging';

        if (!is_dir($sourceDir)) {
            $event->getIO()->writeError(sprintf('Source directory not found: %s', $sourceDir));

            return;
        }

        self::createDirectory($targetDir);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        /** @var \RecursiveDirectoryIterator $iterator */
        foreach ($iterator as $item) {
            $subPathName = $iterator->getSubPathName();
            $targetPath = $targetDir . DIRECTORY_SEPARATOR . $subPathName;

            if ($item->isDir()) {
                self::createDirectory($targetPath);
            } else {
                self::copyFile($item->getPathname(), $targetPath);
            }
        }

        $event->getIO()->write(sprintf('<info>Published assets to: %s</info>', $targetDir));
    }

    private static function createDirectory(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, self::DIRECTORY_PERMISSIONS, true)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }
    }

    private static function copyFile(string $source, string $destination): void
    {
        if (!copy($source, $destination)) {
            throw new \RuntimeException(sprintf('Failed to copy "%s" to "%s"', $source, $destination));
        }
    }
}
