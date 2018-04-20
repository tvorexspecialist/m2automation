<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Category\Action;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\CacheContext;

/**
 * Reindex products categories.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Rows extends \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction
{
    /**
     * Limitation by products
     *
     * @var int[]
     */
    protected $limitationByProducts;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|null
     */
    private $eventManager;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Config $config
     * @param \Magento\Framework\DB\Query\Generator|null $queryGenerator
     * @param \Magento\Framework\EntityManager\MetadataPool|null $metadataPool
     * @param CacheContext|null $cacheContext
     * @param \Magento\Framework\Event\ManagerInterface|null $eventManager
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Config $config,
        \Magento\Framework\DB\Query\Generator $queryGenerator = null,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool = null,
        CacheContext $cacheContext = null,
        \Magento\Framework\Event\ManagerInterface $eventManager = null
    ) {
        parent::__construct($resource, $storeManager, $config, $queryGenerator, $metadataPool);
        $this->cacheContext = $cacheContext ?: ObjectManager::getInstance()->get(CacheContext::class);
        $this->eventManager = $eventManager ?:
            ObjectManager::getInstance()->get(\Magento\Framework\Event\ManagerInterface::class);
    }

    /**
     * Refresh entities index
     *
     * @param int[] $entityIds
     * @param bool $useTempTable
     * @return $this
     * @throws \Exception if metadataPool doesn't contain metadata for ProductInterface
     * @throws \DomainException
     */
    public function execute(array $entityIds = [], $useTempTable = false)
    {
        $idsToBeReIndexed = $this->getProductIdsWithParents($entityIds);

        $this->limitationByProducts = $idsToBeReIndexed;
        $this->useTempTable = $useTempTable;

        $affectedCategories = $this->getCategoryIdsFromIndex($idsToBeReIndexed);

        $this->removeEntries();

        $this->reindex();

        $affectedCategories = array_merge($affectedCategories, $this->getCategoryIdsFromIndex($idsToBeReIndexed));

        $this->registerProducts($idsToBeReIndexed);
        $this->registerCategories($affectedCategories);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);

        return $this;
    }

    /**
     * Get IDs of parent products by their child IDs.
     *
     * Returns identifiers of parent product from the catalog_product_relation.
     * Please note that returned ids don't contain ids of passed child products.
     *
     * @param int[] $childProductIds
     * @return int[]
     * @throws \Exception if metadataPool doesn't contain metadata for ProductInterface
     * @throws \DomainException
     */
    private function getProductIdsWithParents(array $childProductIds)
    {
        /** @var \Magento\Framework\EntityManager\EntityMetadataInterface $metadata */
        $metadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $fieldForParent = $metadata->getLinkField();

        $select = $this->connection
            ->select()
            ->from(['relation' => $this->getTable('catalog_product_relation')], [])
            ->distinct(true)
            ->where('child_id IN (?)', $childProductIds)
            ->join(
                ['cpe' => $this->getTable('catalog_product_entity')],
                'relation.parent_id = cpe.' . $fieldForParent,
                ['cpe.entity_id']
            );

        $parentProductIds = $this->connection->fetchCol($select);

        return array_unique(array_merge($childProductIds, $parentProductIds));
    }

    /**
     * Register affected products
     *
     * @param array $entityIds
     * @return void
     */
    private function registerProducts($entityIds)
    {
        $this->cacheContext->registerEntities(Product::CACHE_TAG, $entityIds);
    }

    /**
     * Register categories assigned to products
     *
     * @param array $categoryIds
     * @return void
     */
    private function registerCategories(array $categoryIds)
    {
        if ($categoryIds) {
            $this->cacheContext->registerEntities(Category::CACHE_TAG, $categoryIds);
        }
    }

    /**
     * Remove index entries before reindexation
     *
     * @return void
     */
    protected function removeEntries()
    {
        $this->connection->delete(
            $this->getMainTable(),
            ['product_id IN (?)' => $this->limitationByProducts]
        );
    }

    /**
     * Retrieve select for reindex products of non anchor categories
     *
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\DB\Select
     */
    protected function getNonAnchorCategoriesSelect(\Magento\Store\Model\Store $store)
    {
        $select = parent::getNonAnchorCategoriesSelect($store);

        return $select->where('ccp.product_id IN (?) OR relation.child_id IN (?)', $this->limitationByProducts);
    }

    /**
     * Retrieve select for reindex products of non anchor categories
     *
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\DB\Select
     */
    protected function getAnchorCategoriesSelect(\Magento\Store\Model\Store $store)
    {
        $select = parent::getAnchorCategoriesSelect($store);
        return $select->where('ccp.product_id IN (?)', $this->limitationByProducts);
    }

    /**
     * Get select for all products
     *
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\DB\Select
     */
    protected function getAllProducts(\Magento\Store\Model\Store $store)
    {
        $select = parent::getAllProducts($store);
        return $select->where('cp.entity_id IN (?)', $this->limitationByProducts);
    }

    /**
     * Check whether select ranging is needed
     *
     * @return bool
     */
    protected function isRangingNeeded()
    {
        return false;
    }

    /**
     * Returns a list of category ids which are assigned to product ids in the index.
     *
     * @param array $productIds
     * @return \Magento\Framework\Indexer\CacheContext
     */
    private function getCategoryIdsFromIndex(array $productIds)
    {
        $categoryIds = $this->connection->fetchCol(
            $this->connection->select()
                ->from($this->getMainTable(), ['category_id'])
                ->where('product_id IN (?)', $productIds)
                ->distinct()
        );
        $parentCategories = $categoryIds;
        foreach ($categoryIds as $categoryId) {
            $parentIds = explode('/', $this->getPathFromCategoryId($categoryId));
            $parentCategories = array_merge($parentCategories, $parentIds);
        }
        $categoryIds = array_unique($parentCategories);

        return $categoryIds;
    }
}
