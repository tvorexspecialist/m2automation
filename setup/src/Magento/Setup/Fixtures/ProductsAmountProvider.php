<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Provide amount of products which need to be generated by fixture
 */
class ProductsAmountProvider
{
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->productCollectionFactory = $collectionFactory;
    }

    /**
     * Get amount of products filtered by specified pattern
     *
     * @param int $requestedProducts
     * @param string $productSkuPattern
     * @return int
     */
    public function getAmount($requestedProducts, $productSkuPattern)
    {
        $productSkuPattern = str_replace('%s', '[0-9]+', $productSkuPattern);
        $productCollection = $this->productCollectionFactory->create();
        $productCollection
            ->getSelect()
            ->where('sku ?', new \Zend_Db_Expr('REGEXP \'^' . $productSkuPattern . '$\''));

        return max(0, $requestedProducts - $productCollection->getSize());
    }
}
