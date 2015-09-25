<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\View\Design\Theme\ThemePackageList;
use Magento\Framework\View\Design\Theme\ThemePackageFactory;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/autoload.php';

$componentRegistrar = new ComponentRegistrar();
$themePackageList = new ThemePackageList($componentRegistrar, new ThemePackageFactory());
\Magento\Framework\App\Utility\Files::setInstance(
    new Files($componentRegistrar, $dirSearch, $themePackageList)
);
