<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Resource\Db\VersionControl;

/**
 * Class Snapshot register snapshot of entity data, for tracking changes
 */
class Snapshot
{
    /**
     * Array of snapshots of entities data
     *
     * @var array
     */
    protected $snapshotData = [];

    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * Initialization
     *
     * @param Metadata $metadata
     */
    public function __construct(
        Metadata $metadata
    ) {
        $this->metadata = $metadata;
    }

    /**
     * Register snapshot of entity data, for tracking changes
     *
     * @param \Magento\Framework\Object $entity
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function registerSnapshot(\Magento\Framework\Object $entity)
    {
        $metaData = $this->metadata->getFields($entity);
        $filteredData = array_intersect_key($entity->getData(), $metaData);
        $data = array_merge($metaData, $filteredData);
        $this->snapshotData[get_class($entity)][$entity->getId()] = $data;
    }

    /**
     * Check is current entity has changes, by comparing current object state with stored snapshot
     *
     * @param \Magento\Framework\Object $entity
     * @return bool
     */
    public function isModified(\Magento\Framework\Object $entity)
    {
        if (!$entity->getId()) {
            return true;
        }

        $entityClass = get_class($entity);
        if (!isset($this->snapshotData[$entityClass][$entity->getId()])) {
            return true;
        }
        foreach ($this->snapshotData[$entityClass][$entity->getId()] as $field => $value) {
            if ($entity->getDataByKey($field) != $value) {
                return true;
            }
        }

        return false;
    }
}
