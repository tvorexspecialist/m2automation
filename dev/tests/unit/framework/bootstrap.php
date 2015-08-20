<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/../../../../app/autoload.php';

$updateAppBootstrap = __DIR__ . '/../../../../update/app/bootstrap.php';
if (file_exists($updateAppBootstrap)) {
    require_once $updateAppBootstrap;
}

if (!defined('TESTS_TEMP_DIR')) {
    define('TESTS_TEMP_DIR', dirname(__DIR__) . '/tmp');
}

require_once __DIR__ . '/autoload.php';
require BP . '/app/functions.php';


\Magento\Framework\Phrase::setRenderer(new \Magento\Framework\Phrase\Renderer\Placeholder());

error_reporting(E_ALL);
ini_set('display_errors', 1);
