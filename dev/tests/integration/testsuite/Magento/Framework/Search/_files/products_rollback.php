<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/** @var \Magento\Framework\Registry $registry */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$registry = $objectManager->get('Magento\Framework\Registry');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Model\Resource\Product\Collection $collection */
$collection = $objectManager->create('Magento\Catalog\Model\Resource\Product\Collection');
$collection->addAttributeToSelect('id')->load();
echo "++++++++++++++++Collection count: ";
var_dump($collection->count());
echo "++++++++++++++++";
if ($collection->count() > 0) {
    $collection->delete();
}


$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
