<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Fixture\Cart;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Data for verify cart item block on checkout page.
 *
 * Data keys:
 *  - product (fixture data for verify)
 */
class Item extends \Magento\Catalog\Test\Fixture\Cart\Item
{
    /**
     * Return prepared dataset.
     *
     * @param null $key
     * @return mixed
     */
    public function getData($key = null)
    {
        $this->data = parent::getData($key);
        /** @var BundleProduct $product */
        $bundleSelection = $this->product->getBundleSelections();
        $checkoutData = $this->product->getCheckoutData();
        $checkoutBundleOptions = isset($checkoutData['options']['bundle_options'])
            ? $checkoutData['options']['bundle_options']
            : [];

        $productSku = [$this->product->getSku()];
        foreach ($checkoutBundleOptions as $checkoutOptionKey => $checkoutOption) {
            $keys = $this->getKeys($bundleSelection['bundle_options'], $checkoutOption);
            $attributeKey = $keys['attribute'];
            $optionKey = $keys['attribute'];
            // Prepare option data
            $bundleSelectionAttribute = $bundleSelection['products'][$attributeKey];
            $bundleOptions = $bundleSelection['bundle_options'][$attributeKey];
            $value = $bundleSelectionAttribute[$optionKey]->getName();
            $this->product->getSkuType() == 'No' ?: $productSku[] = $bundleSelectionAttribute[$optionKey]->getSku();
            $qty = $bundleOptions['assigned_products'][$optionKey]['data']['selection_qty'];
            $price = $this->product->getPriceType() == 'Yes'
                ? number_format($bundleSelectionAttribute[$optionKey]->getPrice(), 2)
                : number_format($bundleOptions['assigned_products'][$optionKey]['data']['selection_price_value'], 2);
            $optionData = [
                'title' => $checkoutOption['title'],
                'value' => "{$qty} x {$value} {$price}",
            ];

            $checkoutBundleOptions[$checkoutOptionKey] = $optionData;
        }

        $this->data['sku'] = implode('-', $productSku);
        $this->data['options'] += $checkoutBundleOptions;

        return $this->data;
    }

    /**
     * Get option key.
     *
     * @param array $assignedProducts
     * @param $checkoutOption
     * @return null|string
     */
    private function getOptionKey(array $assignedProducts, $checkoutOption)
    {
        foreach ($assignedProducts as $key => $value) {
            if (false !== strpos($value['search_data']['name'], $checkoutOption)) {
                return $key;
            }
        }
    }

    /**
     * Find option and attribute keys.
     *
     * @param array $bundleOptions
     * @param $checkoutOption
     * @return array
     */
    private function getKeys(array $bundleOptions, $checkoutOption)
    {
        $keys = [];
        foreach ($bundleOptions as $key => $option) {
            if ($option['title'] == $checkoutOption['title']) {
                $keys['attribute'] = $key;
                $keys['option'] = $this->getOptionKey($option['assigned_products'], $checkoutOption['value']['name']);
            }
        }
        return $keys;
    }
}
