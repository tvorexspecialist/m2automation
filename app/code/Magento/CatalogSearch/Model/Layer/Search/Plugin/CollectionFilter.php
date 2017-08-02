<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Layer\Search\Plugin;

use Magento\Catalog\Model\Category;
use Magento\Search\Model\QueryFactory;

/**
 * Class \Magento\CatalogSearch\Model\Layer\Search\Plugin\CollectionFilter
 *
 * @since 2.0.0
 */
class CollectionFilter
{
    /**
     * @var \Magento\Search\Model\QueryFactory
     * @since 2.0.0
     */
    protected $queryFactory;

    /**
     * @param QueryFactory $queryFactory
     * @since 2.0.0
     */
    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    /**
     * Add search filter criteria to search collection
     *
     * @param \Magento\Catalog\Model\Layer\Search\CollectionFilter $subject
     * @param null $result
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     * @param Category $category
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterFilter(
        \Magento\Catalog\Model\Layer\Search\CollectionFilter $subject,
        $result,
        $collection,
        Category $category
    ) {
        /** @var \Magento\Search\Model\Query $query */
        $query = $this->queryFactory->get();
        if (!$query->isQueryTextShort()) {
            $collection->addSearchFilter($query->getQueryText());
        }
    }
}
