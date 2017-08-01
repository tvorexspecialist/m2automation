<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model;

use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;

/**
 * Class StockRegistry
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class StockRegistry implements StockRegistryInterface
{
    /**
     * @var StockConfigurationInterface
     * @since 2.0.0
     */
    protected $stockConfiguration;

    /**
     * @var StockRegistryProviderInterface
     * @since 2.0.0
     */
    protected $stockRegistryProvider;

    /**
     * @var ProductFactory
     * @since 2.0.0
     */
    protected $productFactory;

    /**
     * @var StockItemRepositoryInterface
     * @since 2.0.0
     */
    protected $stockItemRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory
     * @since 2.0.0
     */
    protected $criteriaFactory;

    /**
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryProviderInterface $stockRegistryProvider
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockItemCriteriaInterfaceFactory $criteriaFactory
     * @param ProductFactory $productFactory
     * @since 2.0.0
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        StockRegistryProviderInterface $stockRegistryProvider,
        StockItemRepositoryInterface $stockItemRepository,
        StockItemCriteriaInterfaceFactory $criteriaFactory,
        ProductFactory $productFactory
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->stockItemRepository = $stockItemRepository;
        $this->criteriaFactory = $criteriaFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockInterface
     * @since 2.0.0
     */
    public function getStock($scopeId = null)
    {
        $scopeId = $this->stockConfiguration->getDefaultScopeId();
        return $this->stockRegistryProvider->getStock($scopeId);
    }

    /**
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @since 2.0.0
     */
    public function getStockItem($productId, $scopeId = null)
    {
        $scopeId = $this->stockConfiguration->getDefaultScopeId();
        return $this->stockRegistryProvider->getStockItem($productId, $scopeId);
    }

    /**
     * @param string $productSku
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 2.0.0
     */
    public function getStockItemBySku($productSku, $scopeId = null)
    {
        $scopeId = $this->stockConfiguration->getDefaultScopeId();
        $productId = $this->resolveProductId($productSku);
        return $this->stockRegistryProvider->getStockItem($productId, $scopeId);
    }

    /**
     * @param int $productId
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     * @since 2.0.0
     */
    public function getStockStatus($productId, $scopeId = null)
    {
        $scopeId = $this->stockConfiguration->getDefaultScopeId();
        return $this->stockRegistryProvider->getStockStatus($productId, $scopeId);
    }

    /**
     * @param string $productSku
     * @param int $scopeId
     * @return \Magento\CatalogInventory\Api\Data\StockStatusInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 2.0.0
     */
    public function getStockStatusBySku($productSku, $scopeId = null)
    {
        $scopeId = $this->stockConfiguration->getDefaultScopeId();
        $productId = $this->resolveProductId($productSku);
        return $this->getStockStatus($productId, $scopeId);
    }

    /**
     * Retrieve Product stock status
     * @param int $productId
     * @param int $scopeId
     * @return int
     * @since 2.0.0
     */
    public function getProductStockStatus($productId, $scopeId = null)
    {
        $scopeId = $this->stockConfiguration->getDefaultScopeId();
        $stockStatus = $this->getStockStatus($productId, $scopeId);
        return $stockStatus->getStockStatus();
    }

    /**
     * @param string $productSku
     * @param null $scopeId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 2.0.0
     */
    public function getProductStockStatusBySku($productSku, $scopeId = null)
    {
        $scopeId = $this->stockConfiguration->getDefaultScopeId();
        $productId = $this->resolveProductId($productSku);
        return $this->getProductStockStatus($productId, $scopeId);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function getLowStockItems($scopeId, $qty, $currentPage = 1, $pageSize = 0)
    {
        $criteria = $this->criteriaFactory->create();
        $criteria->setLimit($currentPage, $pageSize);
        $criteria->setScopeFilter($scopeId);
        $criteria->setQtyFilter('<=', $qty);
        $criteria->addField('qty');
        return $this->stockItemRepository->getList($criteria);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function updateStockItemBySku($productSku, \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem)
    {
        $productId = $this->resolveProductId($productSku);
        $websiteId = $stockItem->getWebsiteId() ?: null;
        $origStockItem = $this->getStockItem($productId, $websiteId);
        $data = $stockItem->getData();
        if ($origStockItem->getItemId()) {
            unset($data['item_id']);
        }
        $origStockItem->addData($data);
        $origStockItem->setProductId($productId);
        return $this->stockItemRepository->save($origStockItem)->getItemId();
    }

    /**
     * @param string $productSku
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 2.0.0
     */
    protected function resolveProductId($productSku)
    {
        $product = $this->productFactory->create();
        $productId = $product->getIdBySku($productSku);
        if (!$productId) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __(
                    'Product with SKU "%1" does not exist',
                    $productSku
                )
            );
        }
        return $productId;
    }
}
