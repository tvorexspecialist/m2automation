<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $cache \Magento\Framework\App\Cache */
$cache = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Framework\App\Cache::class);
$cache->save('application data', 'APPLICATION_FIXTURE');
