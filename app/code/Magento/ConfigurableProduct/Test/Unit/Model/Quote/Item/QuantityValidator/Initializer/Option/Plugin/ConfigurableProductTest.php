<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\ConfigurableProduct\Test\Unit\Model\Quote\Item\QuantityValidator\Initializer\Option\Plugin;

class ConfigurableProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $data
     * @dataProvider aroundGetStockItemDataProvider
     */
    public function testAroundGetStockItem(array $data)
    {
        $subjectMock = $this->getMock(
            \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option::class,
            [],
            [],
            '',
            false
        );

        $quoteItemMock = $this->getMock(
            \Magento\Quote\Model\Quote\Item::class, ['getProductType', '__wakeup'], [], '', false
        );
        $quoteItemMock->expects($this->once())
            ->method('getProductType')
            ->will($this->returnValue($data['product_type']));

        $stockItemMock = $this->getMock(
            \Magento\CatalogInventory\Model\Stock\Item::class, ['setProductName', '__wakeup'], [], '', false
        );
        $matcherMethod = $data['matcher_method'];
        $stockItemMock->expects($this->$matcherMethod())
            ->method('setProductName');

        $optionMock = $this->getMock(
            \Magento\Quote\Model\Quote\Item\Option::class, ['getProduct', '__wakeup'], [], '', false
        );

        $model = new \Magento\ConfigurableProduct\Model\Quote\Item\QuantityValidator\Initializer\Option\Plugin\ConfigurableProduct();
        $model->afterGetStockItem($subjectMock, $stockItemMock, $optionMock, $quoteItemMock, 0);
    }

    /**
     * @return array
     */
    public function aroundGetStockItemDataProvider()
    {
        return [
            [
                [
                    'product_type' => 'not_configurable',
                    'matcher_method' => 'never',
                ],
            ],
            [
                [
                    'product_type' => 'configurable',
                    'matcher_method' => 'once',
                ]
            ]
        ];
    }
}
