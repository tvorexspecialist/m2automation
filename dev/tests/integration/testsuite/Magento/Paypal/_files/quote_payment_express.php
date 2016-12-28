<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/quote_express.php';

/** @var $service \Magento\Quote\Api\CartManagementInterface */
$service = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Quote\Api\CartManagementInterface::class);
$order = $service->submit($quote, ['increment_id' => '100000002']);
