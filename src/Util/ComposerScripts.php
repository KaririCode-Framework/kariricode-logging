<?php

namespace KaririCode\Logging\Util;

use Composer\Installer\PackageEvent;
use Composer\Script\Event;

class ComposerScripts
{
    public static function postPackageInstall(PackageEvent $event)
    {
        /** @var InstallOperation|UpdateOperation $operation */
        $operation = $event->getOperation();
        $package = method_exists($operation, 'getPackage')
        ? $operation->getPackage()
        : $operation->getInitialPackage();

        if ('kariricode/logging' === $package->getName()) {
            /** @var Event $event */
            ConfigGenerator::generateConfig($event);
            AssetPublisher::publishAssets($event);
        }
    }
}
