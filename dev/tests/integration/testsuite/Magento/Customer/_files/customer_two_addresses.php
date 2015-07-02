<?php
/**
 * Customer address fixture with entity_id = 2, this fixture also creates address with entity_id = 1
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require 'customer_address.php';

/** @var \Magento\Customer\Model\Address $customerAddress */
$customerAddress = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Customer\Model\Address');

$customerAddress->isObjectNew(true);
$customerAddress->setData(
    [
        'entity_id' => 2,
        'attribute_set_id' => 2,
        'telephone' => 3234676,
        'postcode' => 47676,
        'country_id' => 'US',
        'city' => 'CityX',
        'street' => ['Black str, 48'],
        'lastname' => 'Smith',
        'firstname' => 'John',
        'parent_id' => 1,
        'region_id' => 1,
    ]
)->setCustomerId(
    1
);
$customerAddress->save();
