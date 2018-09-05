<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Plugin;

use Magento\Catalog\Model\ResourceModel\Category as ResourceCategory;
use Magento\Framework\Model\AbstractModel;

/**
 * @deprecated
 * @see ElasticSearch module is default search engine starting from 2.3. CatalogSearch would be removed in 2.4
 */
class Category extends AbstractPlugin
{
    /**
     * Reindex on product save
     *
     * @param ResourceCategory $resourceCategory
     * @param \Closure $proceed
     * @param AbstractModel $category
     * @return ResourceCategory
     * @throws \Exception
     */
    public function aroundSave(ResourceCategory $resourceCategory, \Closure $proceed, AbstractModel $category)
    {
        return $this->addCommitCallback($resourceCategory, $proceed, $category);
    }

    /**
     * @param ResourceCategory $resourceCategory
     * @param \Closure $proceed
     * @param AbstractModel $category
     * @return ResourceCategory
     * @throws \Exception
     */
    private function addCommitCallback(ResourceCategory $resourceCategory, \Closure $proceed, AbstractModel $category)
    {
        try {
            $resourceCategory->beginTransaction();
            $result = $proceed($category);
            $resourceCategory->addCommitCallback(function () use ($category) {
                $affectedProducts = $category->getAffectedProductIds();
                if (is_array($affectedProducts)) {
                    $this->reindexList($affectedProducts);
                }
            });
            $resourceCategory->commit();
        } catch (\Exception $e) {
            $resourceCategory->rollBack();
            throw $e;
        }

        return $result;
    }
}
