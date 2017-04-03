<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Stock;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Indexer\Model\ResourceModel\FrontendResource;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var FrontendResource
     */
    private $indexerFrontendResource;

    /**
     * @var \Magento\Indexer\Model\ResourceModel\FrontendResource
     */
    private $indexerStockFrontendResource;

    /**
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     * @param Session $customerSession
     * @param FrontendResource $indexerFrontendResource
     * @param FrontendResource $indexerStockFrontendResource
     *
     * @SuppressWarnings(Magento.TypeDuplication)
     */
    public function __construct(
        Config $eavConfig,
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
        Session $customerSession,
        FrontendResource $indexerFrontendResource = null,
        FrontendResource $indexerStockFrontendResource = null
    ) {
        $this->eavConfig = $eavConfig;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->scopeResolver = $scopeResolver;
        $this->customerSession = $customerSession;
        $this->indexerFrontendResource = $indexerFrontendResource ?: ObjectManager::getInstance()->get(
            Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\FrontendResource::class
        );
        $this->indexerStockFrontendResource = $indexerStockFrontendResource ?: ObjectManager::getInstance()
            ->get(\Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\FrontendResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSet(
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    ) {
        $currentScope = $this->scopeResolver->getScope($dimensions['scope']->getValue())->getId();

        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $bucket->getField());

        $select = $this->getSelect();

        $select->joinInner(
            ['entities' => $entityIdsTable->getName()],
            'main_table.entity_id  = entities.entity_id',
            []
        );

        if ($attribute->getAttributeCode() === 'price') {
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->scopeResolver->getScope($currentScope);
            if (!$store instanceof \Magento\Store\Model\Store) {
                throw new \RuntimeException('Illegal scope resolved');
            }
            $table = $this->indexerFrontendResource->getMainTable();
            $select->from(['main_table' => $table], null)
                ->columns([BucketInterface::FIELD_VALUE => 'main_table.min_price'])
                ->where('main_table.customer_group_id = ?', $this->customerSession->getCustomerGroupId())
                ->where('main_table.website_id = ?', $store->getWebsiteId());
        } else {
            $currentScopeId = $this->scopeResolver->getScope($currentScope)
                ->getId();
            $table = $this->resource->getTableName(
                'catalog_product_index_eav' . ($attribute->getBackendType() === 'decimal' ? '_decimal' : '')
            );
            $subSelect = $select;
            $subSelect->from(['main_table' => $table], ['main_table.value'])
                ->joinLeft(
                    ['stock_index' => $this->indexerStockFrontendResource->getMainTable()],
                    'main_table.source_id = stock_index.product_id',
                    []
                )
                ->where('main_table.attribute_id = ?', $attribute->getAttributeId())
                ->where('main_table.store_id = ? ', $currentScopeId)
                ->where('stock_index.stock_status = ?', Stock::STOCK_IN_STOCK)
                ->group(['main_table.entity_id', 'main_table.value']);
            $parentSelect = $this->getSelect();
            $parentSelect->from(['main_table' => $subSelect], ['main_table.value']);
            $select = $parentSelect;
        }

        return $select;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Select $select)
    {
        return $this->connection->fetchAssoc($select);
    }

    /**
     * @return Select
     */
    private function getSelect()
    {
        return $this->connection->select();
    }
}
