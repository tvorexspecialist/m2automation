<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Price;

/**
 * TierPriceStorage test.
 */
class TierPriceStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Price\TierPricePersistence|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tierPricePersistence;

    /**
     * @var \Magento\Catalog\Model\Product\Price\TierPriceValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tierPriceValidator;

    /**
     * @var \Magento\Catalog\Model\Product\Price\TierPriceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tierPriceFactory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceIndexer;

    /**
     * @var \Magento\Catalog\Model\ProductIdLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productIdLocator;

    /**
     * @var \Magento\PageCache\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typeList;

    /**
     * @var \Magento\Catalog\Model\Product\Price\TierPriceStorage
     */
    private $tierPriceStorage;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->tierPricePersistence = $this->getMock(
            \Magento\Catalog\Model\Product\Price\TierPricePersistence::class,
            [],
            [],
            '',
            false
        );
        $this->tierPricePersistence->expects($this->any())
            ->method('getEntityLinkField')
            ->willReturn('row_id');
        $this->tierPriceValidator = $this->getMock(
            \Magento\Catalog\Model\Product\Price\TierPriceValidator::class,
            [],
            [],
            '',
            false
        );
        $this->tierPriceFactory = $this->getMock(
            \Magento\Catalog\Model\Product\Price\TierPriceFactory::class,
            [],
            [],
            '',
            false
        );
        $this->priceIndexer = $this->getMock(
            \Magento\Catalog\Model\Indexer\Product\Price::class,
            [],
            [],
            '',
            false
        );
        $this->productIdLocator = $this->getMock(
            \Magento\Catalog\Model\ProductIdLocatorInterface::class,
            [],
            [],
            '',
            false
        );
        $this->config = $this->getMock(
            \Magento\PageCache\Model\Config::class,
            [],
            [],
            '',
            false
        );
        $this->typeList = $this->getMock(
            \Magento\Framework\App\Cache\TypeListInterface::class,
            [],
            [],
            '',
            false
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->tierPriceStorage = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Price\TierPriceStorage::class,
            [
                'tierPricePersistence' => $this->tierPricePersistence,
                'tierPriceValidator' => $this->tierPriceValidator,
                'tierPriceFactory' => $this->tierPriceFactory,
                'priceIndexer' => $this->priceIndexer,
                'productIdLocator' => $this->productIdLocator,
                'config' => $this->config,
                'typeList' => $this->typeList,
            ]
        );
    }

    /**
     * Test get method.
     * @return void
     */
    public function testGet()
    {
        $skus = ['simple', 'virtual'];
        $this->productIdLocator->expects($this->atLeastOnce())
            ->method('retrieveProductIdsBySkus')
            ->with(['simple', 'virtual'])
            ->willReturn(['simple' => ['2' => 'simple'], 'virtual' => ['3' => 'virtual']]);
        $this->tierPricePersistence->expects($this->once())
            ->method('get')
            ->willReturn(
                [
                    [
                        'value_id' => 1,
                        'row_id' => 2,
                        'all_groups' => 1,
                        'customer_group_id' => 0,
                        'qty' => 2.0000,
                        'value' => 2.0000,
                        'percentage_value' => null,
                        'website_id' => 0
                    ],
                    [
                        'value_id' => 2,
                        'row_id' => 3,
                        'all_groups' => 1,
                        'customer_group_id' => 0,
                        'qty' => 3.0000,
                        'value' => 3.0000,
                        'percentage_value' => null,
                        'website_id' => 0
                    ]
                ]
            );
        $price = $this->getMockBuilder(\Magento\Catalog\Api\Data\TierPriceInterface::class)->getMockForAbstractClass();
        $this->tierPriceFactory->expects($this->at(0))->method('create')->willReturn($price);
        $this->tierPriceFactory->expects($this->at(1))->method('create')->willReturn($price);
        $prices = $this->tierPriceStorage->get($skus);
        $this->assertNotEmpty($prices);
        $this->assertEquals(2, count($prices));
    }

    /**
     * Test update method.
     * @return void
     */
    public function testUpdate()
    {
        $this->productIdLocator->expects($this->atLeastOnce())
            ->method('retrieveProductIdsBySkus')
            ->willReturn(['bundle' => ['2' => 'bundle']]);
        $this->tierPriceValidator->expects($this->atLeastOnce())->method('validatePrices')->willReturn(true);
        $this->tierPriceFactory->expects($this->atLeastOnce())->method('createSkeleton')->willReturn(
            [
                'row_id' => 2,
                'all_groups' => 1,
                'customer_group_id' => 0,
                'qty' => 2,
                'value' => 3,
                'percentage_value' => null,
                'website_id' => 0
            ]
        );
        $this->tierPricePersistence->expects($this->once())
            ->method('get')
            ->willReturn(
                [
                    [
                        'value_id' => 1,
                        'row_id' => 2,
                        'all_groups' => 1,
                        'customer_group_id' => 0,
                        'qty' => 2.0000,
                        'value' => 2.0000,
                        'percentage_value' => null,
                        'website_id' => 0
                    ]
                ]
            );
        $this->tierPricePersistence->expects($this->atLeastOnce())->method('update');
        $this->priceIndexer->expects($this->atLeastOnce())->method('execute');
        $this->config->expects($this->atLeastOnce())->method('isEnabled')->willReturn(true);
        $this->typeList->expects($this->atLeastOnce())->method('invalidate');
        $price = $this->getMockBuilder(\Magento\Catalog\Api\Data\TierPriceInterface::class)->getMockForAbstractClass();
        $price->method('getSku')->willReturn('bundle');
        $this->assertTrue($this->tierPriceStorage->update([$price]));
    }

    /**
     * Test replace method.
     * @return void
     */
    public function testReplace()
    {
        $this->tierPriceValidator->expects($this->atLeastOnce())->method('validatePrices');
        $this->productIdLocator->expects($this->atLeastOnce())
            ->method('retrieveProductIdsBySkus')
            ->willReturn(['virtual' => ['2' => 'virtual']]);
        $this->tierPriceFactory->expects($this->atLeastOnce())->method('createSkeleton')->willReturn(
            [
                'row_id' => 3,
                'all_groups' => 1,
                'customer_group_id' => 0,
                'qty' => 3,
                'value' => 7,
                'percentage_value' => null,
                'website_id' => 0
            ]
        );
        $this->tierPricePersistence->expects($this->atLeastOnce())->method('replace');
        $this->priceIndexer->expects($this->atLeastOnce())->method('execute');
        $price = $this->getMockBuilder(\Magento\Catalog\Api\Data\TierPriceInterface::class)->getMockForAbstractClass();
        $price->method('getSku')->willReturn('virtual');
        $this->config->expects($this->atLeastOnce())->method('isEnabled')->willReturn(true);
        $this->typeList->expects($this->atLeastOnce())->method('invalidate');
        $this->assertTrue($this->tierPriceStorage->replace([$price]));
    }

    /**
     * Test delete method.
     * @return void
     */
    public function testDelete()
    {
        $this->tierPriceValidator->expects($this->atLeastOnce())->method('validatePrices');
        $this->productIdLocator->expects($this->atLeastOnce())
            ->method('retrieveProductIdsBySkus')
            ->willReturn(['simple' => ['2' => 'simple']]);
        $this->tierPricePersistence->expects($this->once())
            ->method('get')
            ->willReturn(
                [
                    [
                        'value_id' => 7,
                        'row_id' => 7,
                        'all_groups' => 1,
                        'customer_group_id' => 0,
                        'qty' => 5.0000,
                        'value' => 6.0000,
                        'percentage_value' => null,
                        'website_id' => 0
                    ]
                ]
            );
        $this->tierPriceFactory->expects($this->atLeastOnce())->method('createSkeleton')->willReturn(
            [
                'row_id' => 3,
                'all_groups' => 1,
                'customer_group_id' => 0,
                'qty' => 3,
                'value' => 7,
                'percentage_value' => null,
                'website_id' => 0
            ]
        );
        $this->tierPricePersistence->expects($this->atLeastOnce())->method('delete');
        $this->priceIndexer->expects($this->atLeastOnce())->method('execute');
        $this->config->expects($this->atLeastOnce())->method('isEnabled')->willReturn(true);
        $this->typeList->expects($this->atLeastOnce())->method('invalidate');
        $price = $this->getMockBuilder(\Magento\Catalog\Api\Data\TierPriceInterface::class)->getMockForAbstractClass();
        $price->method('getSku')->willReturn('simple');
        $this->assertTrue($this->tierPriceStorage->delete([$price]));
    }
}
