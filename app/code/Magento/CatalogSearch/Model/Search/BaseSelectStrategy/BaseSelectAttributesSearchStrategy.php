<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\BaseSelectStrategy;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Indexer\Model\ResourceModel\FrontendResource;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer;

/**
 * Class BaseSelectAttributesSearchStrategy
 * This class represents strategy for building base select query for search request
 *
 * The main idea of this strategy is using eav index table as main table for query
 * in case when search request requires search by attributes
 */
class BaseSelectAttributesSearchStrategy implements BaseSelectStrategyInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FrontendResource
     */
    private $indexerEavFrontendResource;

    /**
     * @var IndexScopeResolver
     */
    private $scopeResolver;

    /**
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param FrontendResource $indexerEavFrontendResource
     * @param IndexScopeResolver $scopeResolver
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        FrontendResource $indexerEavFrontendResource,
        IndexScopeResolver $scopeResolver
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->indexerEavFrontendResource = $indexerEavFrontendResource;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * Creates base select query that can be populated with additional filters
     *
     * @param SelectContainer $selectContainer
     * @return SelectContainer
     * @throws \DomainException
     */
    public function createBaseSelect(SelectContainer $selectContainer)
    {
        $select = $this->resource->getConnection()->select();
        $mainTableAlias = $selectContainer->isFullTextSearchRequired() ? 'eav_index' : 'search_index';

        $select->distinct()
            ->from(
                [$mainTableAlias => $this->indexerEavFrontendResource->getMainTable()],
                ['entity_id' => 'entity_id']
            )->where(
                $this->resource->getConnection()->quoteInto(
                    sprintf('%s.store_id = ?', $mainTableAlias),
                    $this->storeManager->getStore()->getId()
                )
            );

        if ($selectContainer->isFullTextSearchRequired()) {
            $tableName = $this->scopeResolver->resolve(
                $selectContainer->getUsedIndex(),
                $selectContainer->getDimensions()
            );

            $select->joinInner(
                ['search_index' => $tableName],
                'eav_index.entity_id = search_index.entity_id',
                []
            )->joinInner(
                ['cea' => $this->resource->getTableName('catalog_eav_attribute')],
                'search_index.attribute_id = cea.attribute_id',
                []
            );
        }

        $selectContainer = $selectContainer->updateSelect($select);
        return $selectContainer;
    }
}
