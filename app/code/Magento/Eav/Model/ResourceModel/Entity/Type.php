<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel\Entity;

/**
 * EAV entity type resource model
 *
 * @api
 * @since 2.0.0
 */
class Type extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Additional attribute tables data
     *
     * @var array
     * @since 2.2.0
     */
    private $additionalAttributeTables = [];

    /**
     * Resource initialization
     *
     * @return void
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('eav_entity_type', 'entity_type_id');
    }

    /**
     * Load Entity Type by Code
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $code
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function loadByCode($object, $code)
    {
        return $this->load($object, $code, 'entity_type_code');
    }

    /**
     * Retrieve additional attribute table name for specified entity type
     *
     * @param integer $entityTypeId
     * @return string
     * @since 2.0.0
     */
    public function getAdditionalAttributeTable($entityTypeId)
    {
        if (isset($this->additionalAttributeTables[$entityTypeId])) {
            return $this->additionalAttributeTables[$entityTypeId];
        }
        $connection = $this->getConnection();
        $bind = ['entity_type_id' => $entityTypeId];
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['additional_attribute_table']
        )->where(
            'entity_type_id = :entity_type_id'
        );

        return $this->additionalAttributeTables[$entityTypeId] = $connection->fetchOne($select, $bind);
    }
}
