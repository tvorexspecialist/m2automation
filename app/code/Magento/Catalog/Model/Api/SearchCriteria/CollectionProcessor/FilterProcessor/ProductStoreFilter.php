<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class \Magento\Catalog\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor\ProductStoreFilter
 *
 * @since 2.2.0
 */
class ProductStoreFilter implements CustomFilterInterface
{
    /**
     * Apply store Filter to Product Collection
     *
     * @param Filter $filter
     * @param AbstractDb $collection
     * @return bool Whether the filter is applied
     * @since 2.2.0
     */
    public function apply(Filter $filter, AbstractDb $collection)
    {
        /** @var Collection $collection */
        $collection->addStoreFilter($filter->getValue());
        return true;
    }
}
