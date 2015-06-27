<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$base = basename($_SERVER['SCRIPT_FILENAME']);

return [
    'navUpdater' => [
        [
            'id'          => 'root',
            'step'        => 0,
            'views'       => ['root' => []],
        ],
        [
            'id'          => 'root.license',
            'url'         => 'license',
            'templateUrl' => "$base/license",
            'title'       => 'License',
            'main'        => true,
            'nav-bar'     => false,
            'order'       => -1,
        ],
        [
            'id'          => 'root.landing-updater',
            'url'         => 'landing-updater',
            'templateUrl' => "$base/landing-updater",
            'title'       => 'Landing',
            'controller'  => 'landingUpdaterController',
            'main'        => true,
            'default'     => true,
            'order'       => 0,
        ],
        [
            'id'          => 'root.readiness-check-updater',
            'url'         => 'readiness-check-updater',
            'templateUrl' => "{$base}/readiness-check-updater",
            'title'       => "Readiness \n Check",
            'header'      => 'Step 1: Readiness Check',
            'nav-bar'     => true,
            'order'       => 1,
        ],
        [
            'id'          => 'root.readiness-check-updater.progress',
            'url'         => 'readiness-check-updater/progress',
            'templateUrl' => "{$base}/readiness-check-updater/progress",
            'title'       => 'Readiness Check',
            'header'      => 'Step 1: Readiness Check',
            'controller'  => 'readinessCheckUpdaterController',
            'nav-bar'     => false,
            'order'       => 2,
        ],
        [
            'id'          => 'root.create-backup',
            'url'         => 'create-backup',
            'templateUrl' => "{$base}/create-backup",
            'title'       => "Create \n Backup",
            'header'      => 'Step 2: Create Backup',
            'controller'  => 'createBackupController',
            'nav-bar'     => true,
            'validate'    => true,
            'order'       => 3,
        ],
        [
            'id'          => 'root.complete-backup',
            'url'         => 'complete-backup',
            'templateUrl' => "{$base}/complete-backup",
            'title'       => "Backup \n Status",
            'header'      => 'Step 2: Create Backup',
            'controller'  => 'completeBackupController',
            'nav-bar'     => false,
            'order'       => 4,
        ],
        [
            'id'          => 'root.component-upgrade',
            'url'         => 'component-upgrade',
            'templateUrl' => "{$base}/component-upgrade",
            'controller'  => 'componentUpgradeController',
            'title'       => "Component \n Upgrade",
            'header'      => 'Step 3: Component Upgrade',
            'nav-bar'     => true,
            'order'       => 5,
        ],
        [
            'id'          => 'root.component-upgrade-success',
            'url'         => 'component-upgrade-success',
            'templateUrl' => "{$base}/component-upgrade-success",
            'controller'  => 'componentUpgradeSuccessController',
            'order'       => 6,
            'main'        => true
        ],
    ]
];
