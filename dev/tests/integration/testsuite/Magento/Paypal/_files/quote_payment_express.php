<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea('adminhtml');
\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    'Magento\Framework\App\Config\MutableScopeConfigInterface'
)->setValue(
    'carriers/flatrate/active',
    1,
    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
);
\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    'Magento\Framework\App\Config\MutableScopeConfigInterface'
)->setValue(
    'payment/paypal_express/active',
    1,
    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
);
/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId('simple')
    ->setId(1)
    ->setAttributeSetId(4)
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setStockData([
    'use_config_manage_stock' => 1,
    'qty' => 100,
    'is_qty_decimal' => 0,
    'is_in_stock' => 100,
])
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->save();
$product->load(1);

$billingData = [
    'firstname' => 'testname',
    'lastname' => 'lastname',
    'company' => '',
    'email' => 'test@com.com',
    'street' => [
        0 => 'test1',
        1 => '',
    ],
    'city' => 'Test',
    'region_id' => '1',
    'region' => '',
    'postcode' => '9001',
    'country_id' => 'US',
    'telephone' => '11111111',
    'fax' => '',
    'confirm_password' => '',
    'save_in_address_book' => '1',
    'use_for_shipping' => '1',
];

$billingAddress = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Quote\Model\Quote\Address', ['data' => $billingData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');
$shippingAddress->setShippingMethod('flatrate_flatrate');
$shippingAddress->setCollectShippingRates(true);

/** @var $quote \Magento\Quote\Model\Quote */
$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Quote\Model\Quote');
$quote->setCustomerIsGuest(
    true
)->setStoreId(
    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        'Magento\Store\Model\StoreManagerInterface'
    )->getStore()->getId()
)->setReservedOrderId(
    '100000002'
)->setBillingAddress(
    $billingAddress
)->setShippingAddress(
    $shippingAddress
)->addProduct(
    $product,
    10
);
$quote->getShippingAddress()->setShippingMethod('flatrate_flatrate');
$quote->getShippingAddress()->setCollectShippingRates(true);
$quote->getPayment()->setMethod(\Magento\Paypal\Model\Config::METHOD_WPS_EXPRESS);
$quote->collectTotals()->save();

$quote->setCustomerEmail('admin@example.com');

/** @var $service \Magento\Quote\Api\CartManagementInterface */
$service = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('\Magento\Quote\Api\CartManagementInterface');
$order = $service->submit($quote, ['increment_id' => '100000002']);
