<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin;

use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Framework\Model\AbstractModel;

/**
 * Class \Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin\Product
 *
 * @since 2.0.0
 */
class Product extends AbstractPlugin
{
    /**
     * Reindex on product save
     *
     * @param ResourceProduct $productResource
     * @param \Closure $proceed
     * @param AbstractModel $product
     * @return ResourceProduct
     * @since 2.0.0
     */
    public function aroundSave(ResourceProduct $productResource, \Closure $proceed, AbstractModel $product)
    {
        return $this->addCommitCallback($productResource, $proceed, $product);
    }

    /**
     * Reindex on product delete
     *
     * @param ResourceProduct $productResource
     * @param \Closure $proceed
     * @param AbstractModel $product
     * @return ResourceProduct
     * @since 2.0.0
     */
    public function aroundDelete(ResourceProduct $productResource, \Closure $proceed, AbstractModel $product)
    {
        return $this->addCommitCallback($productResource, $proceed, $product);
    }

    /**
     * @param ResourceProduct $productResource
     * @param \Closure $proceed
     * @param AbstractModel $product
     * @return ResourceProduct
     * @throws \Exception
     * @since 2.1.0
     */
    private function addCommitCallback(ResourceProduct $productResource, \Closure $proceed, AbstractModel $product)
    {
        try {
            $productResource->beginTransaction();
            $result = $proceed($product);
            $productResource->addCommitCallback(function () use ($product) {
                $this->reindexRow($product->getEntityId());
            });
            $productResource->commit();
        } catch (\Exception $e) {
            $productResource->rollBack();
            throw $e;
        }

        return $result;
    }
}
