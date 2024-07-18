<?php

declare(strict_types=1);

namespace KaririCode\Logging\Util;

use Composer\Script\Event;

class AssetPublisher
{
    public static function publishAssets(Event $event): void
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $sourceDir = $vendorDir . '/kariricode/logging/resources';
        $targetDir = dirname($vendorDir) . '/resources/logging';

        if (!is_dir($sourceDir)) {
            $event->getIO()->write('<error>Source directory not found: ' . $sourceDir . '</error>');

            return;
        }

        if (!is_dir($targetDir) && !@mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $targetDir));
        }

        /** @var RecursiveDirectoryIterator $iterator */
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $subPathName = $iterator->getSubPathName();
            if ($item->isDir()) {
                $targetPath = $targetDir . DIRECTORY_SEPARATOR . $subPathName;
                if (!is_dir($targetPath) && !@mkdir($targetPath, 0755, true) && !is_dir($targetPath)) {
                    throw new \RuntimeException(sprintf('Directory "%s" was not created', $targetPath));
                }
            } else {
                $targetPath = $targetDir . DIRECTORY_SEPARATOR . $subPathName;
                if (!copy($item->getPathname(), $targetPath)) {
                    throw new \RuntimeException(sprintf('Failed to copy "%s" to "%s"', $item->getPathname(), $targetPath));
                }
            }
        }

        $event->getIO()->write('<info>Published assets to: ' . $targetDir . '</info>');
    }
}
