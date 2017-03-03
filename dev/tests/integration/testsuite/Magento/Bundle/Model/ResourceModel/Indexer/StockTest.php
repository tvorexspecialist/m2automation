<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\ResourceModel\Indexer;

class StockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $processor;

    protected function setUp()
    {
        $this->processor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\CatalogInventory\Model\Indexer\Stock\Processor::class
        );
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Bundle/_files/product_in_category.php
     */
    public function testReindexAll()
    {
        $this->processor->reindexAll();

        $categoryFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\CategoryFactory::class
        );
        /** @var \Magento\Catalog\Block\Product\ListProduct $listProduct */
        $listProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Block\Product\ListProduct::class
        );

        /** @var \Magento\Indexer\Model\ResourceModel\FrontendResource $indexerStockFrontendResource */
        $indexerStockFrontendResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\FrontendResource::class
        );

        $category = $categoryFactory->create()->load(2);
        $layer = $listProduct->getLayer();
        $layer->setCurrentCategory($category);
        $productCollection = $layer->getProductCollection();
        $productCollection->joinField(
            'qty',
            $indexerStockFrontendResource->getMainTable(),
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );

        $this->assertCount(3, $productCollection);

        $expectedResult = [
            'Simple Product' => 22,
            'Custom Design Simple Product' => 24,
            'Bundle Product' => 0
        ];

        /** @var $product \Magento\Catalog\Model\Product */
        foreach ($productCollection as $product) {
            $this->assertEquals($expectedResult[$product->getName()], $product->getQty());
        }
    }
}
