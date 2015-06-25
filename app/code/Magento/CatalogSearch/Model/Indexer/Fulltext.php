<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full;
use Magento\CatalogSearch\Model\Resource\Fulltext as FulltextResource;
use \Magento\Framework\Search\Request\Config as SearchRequestConfig;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Fulltext implements \Magento\Indexer\Model\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'catalogsearch_fulltext';

    /** @var array index structure */
    protected $data;

    /**
     * @var IndexerHandlerFactory
     */
    private $indexerHandlerFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;
    /**
     * @var Full
     */
    private $fullAction;
    /**
     * @var FulltextResource
     */
    private $fulltextResource;
    /**
     * @var SearchRequestConfig
     */
    private $searchRequestConfig;

    /**
     * @param Full $fullAction
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param StoreManagerInterface $storeManager
     * @param DimensionFactory $dimensionFactory
     * @param FulltextResource $fulltextResource
     * @param SearchRequestConfig $searchRequestConfig
     * @param array $data
     */
    public function __construct(
        Full $fullAction,
        IndexerHandlerFactory $indexerHandlerFactory,
        StoreManagerInterface $storeManager,
        DimensionFactory $dimensionFactory,
        FulltextResource $fulltextResource,
        SearchRequestConfig $searchRequestConfig,
        array $data
    ) {
        $this->fullAction = $fullAction;
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->storeManager = $storeManager;
        $this->dimensionFactory = $dimensionFactory;
        $this->fulltextResource = $fulltextResource;
        $this->searchRequestConfig = $searchRequestConfig;
        $this->data = $data;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $storeIds = array_keys($this->storeManager->getStores());
        /** @var IndexerHandler $saveHandler */
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);
        foreach ($storeIds as $storeId) {
            $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
            $saveHandler->deleteIndex([$dimension], new \ArrayObject($ids));
            $saveHandler->saveIndex(
                [$dimension],
                $this->fullAction->rebuildStoreIndex($storeId, $ids)
            );
        }
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $storeIds = array_keys($this->storeManager->getStores());
        /** @var IndexerHandler $saveHandler */
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);
        foreach ($storeIds as $storeId) {
            $dimension = $this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId]);
            $saveHandler->cleanIndex([$dimension]);
            $saveHandler->saveIndex(
                [$dimension],
                $this->fullAction->rebuildStoreIndex($storeId)
            );
        }
        $this->fulltextResource->resetSearchResults();
        $this->searchRequestConfig->reset();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->execute([$id]);
    }
}
