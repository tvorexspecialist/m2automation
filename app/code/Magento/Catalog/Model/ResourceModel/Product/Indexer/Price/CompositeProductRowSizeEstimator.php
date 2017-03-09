<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

class CompositeProductRowSizeEstimator implements \Magento\Framework\Indexer\IndexTableRowSizeEstimatorInterface
{
    /**
     * @var \Magento\Indexer\Model\IndexTableRowSizeEstimator
     */
    private $indexTableRowSizeEstimator;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice
     */
    private $indexerResource;

    /**
     * @param DefaultPrice $indexerResource
     * @param \Magento\Indexer\Model\IndexTableRowSizeEstimator $indexTableRowSizeEstimator
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice $indexerResource,
        \Magento\Indexer\Model\IndexTableRowSizeEstimator $indexTableRowSizeEstimator
    ) {
        $this->indexerResource = $indexerResource;
        $this->indexTableRowSizeEstimator = $indexTableRowSizeEstimator;
    }

    /**
     * Calculate memory size for largest composite product in database.
     *
     * @inheritdoc
     */
    public function estimateRowSize()
    {
        $connection = $this->indexerResource->getConnection();
        $relationSelect = $connection->select();
        $relationSelect->from(
            ['relation' => $this->indexerResource->getTable('catalog_product_relation')],
            ['count' => new \Zend_Db_Expr('count(relation.child_id)')]
        );
        $relationSelect->group('parent_id');

        $maxSelect = $connection->select();
        $maxSelect->from(
            ['max_value' => $relationSelect],
            ['count' => new \Zend_Db_Expr('MAX(count)')]
        );
        $maxRelatedProductCount = $connection->fetchOne($maxSelect);

        /**
         * Calculate memory size for largest composite product in database.
         *
         * $maxRelatedProductCount - maximum number of related products
         */
        return ceil($maxRelatedProductCount * $this->indexTableRowSizeEstimator->estimateRowSize());
    }
}
