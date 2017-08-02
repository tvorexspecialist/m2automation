<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\FullFactory;
use Magento\CatalogSearch\Model\Indexer\Scope\State;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext as FulltextResource;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\Request\Config as SearchRequestConfig;
use Magento\Framework\Search\Request\DimensionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provide functionality for Fulltext Search indexing.
 *
 * @api
 * @since 2.0.0
 */
class Fulltext implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'catalogsearch_fulltext';

    /**
     * @var array index structure
     * @since 2.0.0
     */
    protected $data;

    /**
     * @var IndexerHandlerFactory
     * @since 2.0.0
     */
    private $indexerHandlerFactory;

    /**
     * @var StoreManagerInterface
     * @since 2.0.0
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Search\Request\DimensionFactory
     * @since 2.0.0
     */
    private $dimensionFactory;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full
     * @since 2.0.0
     */
    private $fullAction;

    /**
     * @var FulltextResource
     * @since 2.0.0
     */
    private $fulltextResource;

    /**
     * @var \Magento\Framework\Search\Request\Config
     * @since 2.0.0
     */
    private $searchRequestConfig;

    /**
     * @var IndexSwitcherInterface
     * @since 2.2.0
     */
    private $indexSwitcher;

    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Scope\State
     * @since 2.2.0
     */
    private $indexScopeState;

    /**
     * @param FullFactory $fullActionFactory
     * @param IndexerHandlerFactory $indexerHandlerFactory
     * @param StoreManagerInterface $storeManager
     * @param DimensionFactory $dimensionFactory
     * @param FulltextResource $fulltextResource
     * @param SearchRequestConfig $searchRequestConfig
     * @param array $data
     * @param IndexSwitcherInterface $indexSwitcher
     * @param Scope\State $indexScopeState
     * @since 2.0.0
     */
    public function __construct(
        FullFactory $fullActionFactory,
        IndexerHandlerFactory $indexerHandlerFactory,
        StoreManagerInterface $storeManager,
        DimensionFactory $dimensionFactory,
        FulltextResource $fulltextResource,
        SearchRequestConfig $searchRequestConfig,
        array $data,
        IndexSwitcherInterface $indexSwitcher = null,
        State $indexScopeState = null
    ) {
        $this->fullAction = $fullActionFactory->create(['data' => $data]);
        $this->indexerHandlerFactory = $indexerHandlerFactory;
        $this->storeManager = $storeManager;
        $this->dimensionFactory = $dimensionFactory;
        $this->fulltextResource = $fulltextResource;
        $this->searchRequestConfig = $searchRequestConfig;
        $this->data = $data;
        if (null === $indexSwitcher) {
            $indexSwitcher = ObjectManager::getInstance()->get(IndexSwitcherInterface::class);
        }
        if (null === $indexScopeState) {
            $indexScopeState = ObjectManager::getInstance()->get(State::class);
        }
        $this->indexSwitcher = $indexSwitcher;
        $this->indexScopeState = $indexScopeState;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     * @since 2.0.0
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
            $productIds = array_unique(array_merge($ids, $this->fulltextResource->getRelationsByChild($ids)));
            $saveHandler->deleteIndex([$dimension], new \ArrayObject($productIds));
            $saveHandler->saveIndex([$dimension], $this->fullAction->rebuildStoreIndex($storeId, $ids));
        }
    }

    /**
     * Execute full indexation
     *
     * @return void
     * @since 2.0.0
     */
    public function executeFull()
    {
        $storeIds = array_keys($this->storeManager->getStores());
        /** @var IndexerHandler $saveHandler */
        $saveHandler = $this->indexerHandlerFactory->create([
            'data' => $this->data
        ]);
        foreach ($storeIds as $storeId) {
            $dimensions = [$this->dimensionFactory->create(['name' => 'scope', 'value' => $storeId])];
            $this->indexScopeState->useTemporaryIndex();

            $saveHandler->cleanIndex($dimensions);
            $saveHandler->saveIndex($dimensions, $this->fullAction->rebuildStoreIndex($storeId));

            $this->indexSwitcher->switchIndex($dimensions);
            $this->indexScopeState->useRegularIndex();
        }
        $this->fulltextResource->resetSearchResults();
        $this->searchRequestConfig->reset();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function executeRow($id)
    {
        $this->execute([$id]);
    }
}
