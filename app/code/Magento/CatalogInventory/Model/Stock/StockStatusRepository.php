<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\Stock;

use Magento\CatalogInventory\Api\Data\StockStatusCollectionInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatusResource;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\Framework\DB\MapperFactory;
use Magento\Framework\DB\QueryBuilderFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class StockStatusRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class StockStatusRepository implements StockStatusRepositoryInterface
{
    /**
     * @var StockStatusResource
     * @since 2.0.0
     */
    protected $resource;

    /**
     * @var StatusFactory
     * @since 2.0.0
     */
    protected $stockStatusFactory;

    /**
     * @var StockStatusCollectionInterfaceFactory
     * @since 2.0.0
     */
    protected $stockStatusCollectionFactory;

    /**
     * @var QueryBuilderFactory
     * @since 2.0.0
     */
    protected $queryBuilderFactory;

    /**
     * @var MapperFactory
     * @since 2.0.0
     */
    protected $mapperFactory;

    /**
     * @var StockRegistryStorage
     * @since 2.1.0
     */
    protected $stockRegistryStorage;

    /**
     * @param StockStatusResource $resource
     * @param StatusFactory $stockStatusFactory
     * @param StockStatusCollectionInterfaceFactory $collectionFactory
     * @param QueryBuilderFactory $queryBuilderFactory
     * @param MapperFactory $mapperFactory
     * @since 2.0.0
     */
    public function __construct(
        StockStatusResource $resource,
        StatusFactory $stockStatusFactory,
        StockStatusCollectionInterfaceFactory $collectionFactory,
        QueryBuilderFactory $queryBuilderFactory,
        MapperFactory $mapperFactory
    ) {
        $this->resource = $resource;
        $this->stockStatusFactory = $stockStatusFactory;
        $this->stockStatusCollectionFactory = $collectionFactory;
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->mapperFactory = $mapperFactory;
    }

    /**
     * @param StockStatusInterface $stockStatus
     * @return StockStatusInterface
     * @throws CouldNotSaveException
     * @since 2.0.0
     */
    public function save(StockStatusInterface $stockStatus)
    {
        try {
            $this->resource->save($stockStatus);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Unable to save Stock Status'), $exception);
        }
        return $stockStatus;
    }

    /**
     * @param string $stockStatusId
     * @return StockStatusInterface|Status
     * @since 2.0.0
     */
    public function get($stockStatusId)
    {
        $stockStatus = $this->stockStatusFactory->create();
        $this->resource->load($stockStatus, $stockStatusId);
        return $stockStatus;
    }

    /**
     * @param \Magento\CatalogInventory\Api\StockStatusCriteriaInterface $criteria
     * @return \Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface
     * @since 2.0.0
     */
    public function getList(\Magento\CatalogInventory\Api\StockStatusCriteriaInterface $criteria)
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder->setCriteria($criteria);
        $queryBuilder->setResource($this->resource);
        $query = $queryBuilder->create();
        $collection = $this->stockStatusCollectionFactory->create(['query' => $query]);
        return $collection;
    }

    /**
     * @param StockStatusInterface $stockStatus
     * @return bool|true
     * @throws CouldNotDeleteException
     * @since 2.0.0
     */
    public function delete(StockStatusInterface $stockStatus)
    {
        try {
            $this->resource->delete($stockStatus);
            $this->getStockRegistryStorage()->removeStockStatus($stockStatus->getProductId());
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Unable to remove Stock Status for product %1', $stockStatus->getProductId()),
                $exception
            );
        }
        return true;
    }

    /**
     * @param int $id
     * @return bool
     * @throws CouldNotDeleteException
     * @since 2.0.0
     */
    public function deleteById($id)
    {
        try {
            $stockStatus = $this->get($id);
            $this->delete($stockStatus);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Unable to remove Stock Status for product %1', $id),
                $exception
            );
        }
        return true;
    }

    /**
     * @return StockRegistryStorage
     * @since 2.1.0
     */
    private function getStockRegistryStorage()
    {
        if (null === $this->stockRegistryStorage) {
            $this->stockRegistryStorage = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\CatalogInventory\Model\StockRegistryStorage::class);
        }
        return $this->stockRegistryStorage;
    }
}
