<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultInterface;

/**
 * Class Reporting
 */
class Reporting implements ReportingInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var array
     */
    protected $filterPool;

    /**
     * @param array $appliers
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        FilterPool $filterPool
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->filterPool = $filterPool;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultInterface
     */
    public function search(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->getReport($searchCriteria->getRequestName());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $this->filterPool->applyFilters($collection, $searchCriteria);
        return $collection;
    }
}
