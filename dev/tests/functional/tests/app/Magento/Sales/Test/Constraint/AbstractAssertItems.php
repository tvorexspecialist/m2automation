<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Mtf\Constraint\AbstractAssertForm;

/**
 * Assert items represented in order's entity view page.
 */
abstract class AbstractAssertItems extends AbstractAssertForm
{
    /**
     * Key for sort data.
     *
     * @var string
     */
    protected $sortKey = "::sku";

    /**
     * List compare fields.
     *
     * @var array
     */
    protected $compareFields = [
        'product',
        'sku',
        'qty',
    ];

    /**
     * Prepare order products.
     *
     * @param Cart $cart
     * @param array|null $data [optional]
     * @return array
     */
    protected function prepareOrderProducts(Cart $cart, array $data = null)
    {
        $productsData = [];
        /** @var \Magento\Catalog\Test\Fixture\Cart\Item $item */
        foreach ($cart->getItems() as $key => $item) {
            $productsData[] = [
                'product' => $item->getData()['name'],
                'sku' => $item->getData()['sku'],
                'qty' => (isset($data[$key]['qty']) && $data[$key]['qty'] != '-')
                    ? $data[$key]['qty']
                    : $item->getData()['qty'],
            ];
        }

        return $this->sortDataByPath($productsData, $this->sortKey);
    }

    /**
     * Prepare invoice data.
     *
     * @param array $itemsData
     * @return array
     */
    protected function preparePageItems(array $itemsData)
    {
        foreach ($itemsData as $key => $itemData) {
            $itemsData[$key] = array_intersect_key($itemData, array_flip($this->compareFields));
        }
        return $this->sortDataByPath($itemsData, $this->sortKey);
    }
}
