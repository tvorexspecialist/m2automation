<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Eav;

/**
 * Abstract action reindex class
 * @since 2.0.0
 */
abstract class AbstractAction
{
    /**
     * EAV Indexers by type
     *
     * @var array
     * @since 2.0.0
     */
    protected $_types;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory
     * @since 2.0.0
     */
    protected $_eavSourceFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory
     * @since 2.0.0
     */
    protected $_eavDecimalFactory;

    /**
     * AbstractAction constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory $eavDecimalFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory $eavSourceFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\DecimalFactory $eavDecimalFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\SourceFactory $eavSourceFactory
    ) {
        $this->_eavDecimalFactory = $eavDecimalFactory;
        $this->_eavSourceFactory = $eavSourceFactory;
    }

    /**
     * Execute action for given ids
     *
     * @param array|int $ids
     * @return void
     * @since 2.0.0
     */
    abstract public function execute($ids);

    /**
     * Retrieve array of EAV type indexers
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\AbstractEav[]
     * @since 2.0.0
     */
    public function getIndexers()
    {
        if ($this->_types === null) {
            $this->_types = [
                'source' => $this->_eavSourceFactory->create(),
                'decimal' => $this->_eavDecimalFactory->create(),
            ];
        }

        return $this->_types;
    }

    /**
     * Retrieve indexer instance by type
     *
     * @param string $type
     * @return \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\AbstractEav
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getIndexer($type)
    {
        $indexers = $this->getIndexers();
        if (!isset($indexers[$type])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unknown EAV indexer type "%1".', $type));
        }
        return $indexers[$type];
    }

    /**
     * Reindex entities
     *
     * @param null|array|int $ids
     * @throws \Exception
     * @return void
     * @since 2.0.0
     */
    public function reindex($ids = null)
    {
        foreach ($this->getIndexers() as $indexer) {
            if ($ids === null) {
                $indexer->reindexAll();
            } else {
                if (!is_array($ids)) {
                    $ids = [$ids];
                }
                $ids = $this->processRelations($indexer, $ids);
                $indexer->reindexEntities($ids);
                $destinationTable = $indexer->getMainTable();
                $this->syncData($indexer, $destinationTable, $ids);
            }
        }
    }

    /**
     * Synchronize data between index storage and original storage
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\AbstractEav $indexer
     * @param string $destinationTable
     * @param array $ids
     * @throws \Exception
     * @return void
     * @since 2.2.0
     */
    protected function syncData($indexer, $destinationTable, $ids)
    {
        $connection = $indexer->getConnection();
        $connection->beginTransaction();
        try {
            // remove old index
            $where = $connection->quoteInto('entity_id IN(?)', $ids);
            $connection->delete($destinationTable, $where);
            // insert new index
            $indexer->insertFromTable($indexer->getIdxTable(), $destinationTable);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * Retrieve product relations by children and parent
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\AbstractEav $indexer
     * @param array $ids
     *
     * @param bool $onlyParents
     * @return array $ids
     * @since 2.2.0
     */
    protected function processRelations($indexer, $ids, $onlyParents = false)
    {
        $parentIds = $indexer->getRelationsByChild($ids);
        $childIds = $onlyParents ? [] : $indexer->getRelationsByParent($ids);
        return array_unique(array_merge($ids, $childIds, $parentIds));
    }
}
