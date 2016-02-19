<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\StateInterface;

class Processor
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var IndexerFactory
     */
    protected $indexerFactory;

    /**
     * @var Indexer\CollectionFactory
     */
    protected $indexersFactory;

    /**
     * @var \Magento\Framework\Mview\ProcessorInterface
     */
    protected $mviewProcessor;

    /**
     * @var array
     */
    protected $threadSharedIndexes;

    /**
     * @param ConfigInterface $config
     * @param IndexerFactory $indexerFactory
     * @param Indexer\CollectionFactory $indexersFactory
     * @param \Magento\Framework\Mview\ProcessorInterface $mviewProcessor
     */
    public function __construct(
        ConfigInterface $config,
        IndexerFactory $indexerFactory,
        Indexer\CollectionFactory $indexersFactory,
        \Magento\Framework\Mview\ProcessorInterface $mviewProcessor
    ) {
        $this->config = $config;
        $this->indexerFactory = $indexerFactory;
        $this->indexersFactory = $indexersFactory;
        $this->mviewProcessor = $mviewProcessor;
        $config = $this->config->getIndexers();
    }

    /**
     * Regenerate indexes for all invalid indexers
     *
     * @return void
     */
    public function reindexAllInvalid()
    {
        $sharedIndexesComplete = [];
        foreach (array_keys($this->config->getIndexers()) as $indexerId) {
            /** @var Indexer $indexer */
            $indexer = $this->indexerFactory->create();
            $indexer->load($indexerId);
            if ($indexer->isInvalid()) {
                // Skip indexers that have shared index that was already
                if (!in_array($indexer->getSharedIndex(), $sharedIndexesComplete)) {
                    $indexer->reindexAll();
                } else {
                    /** @var \Magento\Indexer\Model\Indexer\State $state */
                    $state = $indexer->getState();
                    $state->setStatus(StateInterface::STATUS_VALID);
                    $state->save();
                }
                if ($indexer->getSharedIndex()) {
                    $sharedIndexesComplete[] = $indexer->getSharedIndex();
                }
            }
        }
    }

    /**
     * Regenerate indexes for all indexers
     *
     * @return void
     */
    public function reindexAll()
    {
        /** @var IndexerInterface[] $indexers */
        $indexers = $this->indexersFactory->create()->getItems();
        foreach ($indexers as $indexer) {
            $indexer->reindexAll();
        }
    }

    /**
     * Update indexer views
     *
     * @return void
     */
    public function updateMview()
    {
        $this->mviewProcessor->update('indexer');
    }

    /**
     * Clean indexer view changelogs
     *
     * @return void
     */
    public function clearChangelog()
    {
        $this->mviewProcessor->clearChangelog('indexer');
    }
}
