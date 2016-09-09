<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\Filter\BoolExpression;
use Magento\Framework\Search\Request\Query\Filter;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TableMapper
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param AppResource $resource
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $attributeCollectionFactory
     * @param EavConfig $eavConfig
     */
    public function __construct(
        AppResource $resource,
        StoreManagerInterface $storeManager,
        CollectionFactory $attributeCollectionFactory,
        EavConfig $eavConfig = null
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->eavConfig = $eavConfig !== null ? $eavConfig : ObjectManager::getInstance()->get(EavConfig::class);
    }

    /**
     * @param Select $select
     * @param RequestInterface $request
     * @return Select
     * @throws \LogicException
     */
    public function addTables(Select $select, RequestInterface $request)
    {
        $mappedTables = [];
        $filters = $this->getFilters($request->getQuery());
        foreach ($filters as $filter) {
            list($alias, $table, $mapOn, $mappedFields, $joinType) = $this->getMappingData($filter);
            if (!array_key_exists($alias, $mappedTables)) {
                switch ($joinType) {
                    case \Magento\Framework\DB\Select::INNER_JOIN:
                        $select->joinInner(
                            [$alias => $table],
                            $mapOn,
                            $mappedFields
                        );
                        break;
                    case \Magento\Framework\DB\Select::LEFT_JOIN:
                        $select->joinLeft(
                            [$alias => $table],
                            $mapOn,
                            $mappedFields
                        );
                        break;
                    default:
                        throw new \LogicException(__('Unsupported join type: %1', $joinType));
                }
                $mappedTables[$alias] = $table;
            }
        }
        return $select;
    }

    /**
     * @param FilterInterface $filter
     * @return string
     */
    public function getMappingAlias(FilterInterface $filter)
    {
        list($alias) = $this->getMappingData($filter);
        return $alias;
    }

    /**
     * Returns mapping data for field in format: [
     *  'table_alias',
     *  'table',
     *  'join_condition',
     *  ['fields'],
     *  'joinType'
     * ]
     * @param FilterInterface $filter
     * @return array
     */
    private function getMappingData(FilterInterface $filter)
    {
        $alias = null;
        $table = null;
        $mapOn = null;
        $mappedFields = null;
        $field = $filter->getField();
        $joinType = \Magento\Framework\DB\Select::INNER_JOIN;
        $fieldToTableMap = $this->getFieldToTableMap($field);
        if ($fieldToTableMap) {
            list($alias, $table, $mapOn, $mappedFields) = $fieldToTableMap;
            $table = $this->resource->getTableName($table);
        } elseif ($attribute = $this->getAttributeByCode($field)) {
            if ($filter->getType() === FilterInterface::TYPE_TERM
                && in_array($attribute->getFrontendInput(), ['select', 'multiselect'], true)
            ) {
                $joinType = \Magento\Framework\DB\Select::LEFT_JOIN;
                $table = $this->resource->getTableName('catalog_product_index_eav');
                $alias = $field . RequestGenerator::FILTER_SUFFIX;
                $mapOn = sprintf(
                    'search_index.entity_id = %1$s.entity_id AND %1$s.attribute_id = %2$d AND %1$s.store_id = %3$d',
                    $alias,
                    $attribute->getId(),
                    $this->getStoreId()
                );
                $mappedFields = [];
            } elseif ($attribute->getBackendType() === AbstractAttribute::TYPE_STATIC) {
                $table = $attribute->getBackendTable();
                $alias = $field . RequestGenerator::FILTER_SUFFIX;
                $mapOn = 'search_index.entity_id = ' . $alias . '.entity_id';
                $mappedFields = null;
            }
        }

        return [$alias, $table, $mapOn, $mappedFields, $joinType];
    }

    /**
     * @param RequestQueryInterface $query
     * @return FilterInterface[]
     */
    private function getFilters($query)
    {
        $filters = [];
        switch ($query->getType()) {
            case RequestQueryInterface::TYPE_BOOL:
                /** @var \Magento\Framework\Search\Request\Query\BoolExpression $query */
                foreach ($query->getMust() as $subQuery) {
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                foreach ($query->getShould() as $subQuery) {
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                foreach ($query->getMustNot() as $subQuery) {
                    $filters = array_merge($filters, $this->getFilters($subQuery));
                }
                break;
            case RequestQueryInterface::TYPE_FILTER:
                /** @var Filter $query */
                $filter = $query->getReference();
                if (FilterInterface::TYPE_BOOL === $filter->getType()) {
                    $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
                } else {
                    $filters[] = $filter;
                }
                break;
            default:
                break;
        }
        return $filters;
    }

    /**
     * @param BoolExpression $boolExpression
     * @return FilterInterface[]
     */
    private function getFiltersFromBoolFilter(BoolExpression $boolExpression)
    {
        $filters = [];
        /** @var BoolExpression $filter */
        foreach ($boolExpression->getMust() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getShould() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        foreach ($boolExpression->getMustNot() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                $filters = array_merge($filters, $this->getFiltersFromBoolFilter($filter));
            } else {
                $filters[] = $filter;
            }
        }
        return $filters;
    }

    /**
     * @return int
     */
    private function getWebsiteId()
    {
        return $this->storeManager->getWebsite()->getId();
    }

    /**
     * @return int
     */
    private function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @param string $field
     * @return array|null
     */
    private function getFieldToTableMap($field)
    {
        $fieldToTableMap = [
            'price' => [
                'price_index',
                'catalog_product_index_price',
                $this->resource->getConnection()->quoteInto(
                    'search_index.entity_id = price_index.entity_id AND price_index.website_id = ?',
                    $this->getWebsiteId()
                ),
                []
            ],
            'category_ids' => [
                'category_ids_index',
                'catalog_category_product_index',
                'search_index.entity_id = category_ids_index.product_id',
                []
            ]
        ];
        return array_key_exists($field, $fieldToTableMap) ? $fieldToTableMap[$field] : null;
    }

    /**
     * @param string $field
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeByCode($field)
    {
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $field);
        return $attribute;
    }
}
