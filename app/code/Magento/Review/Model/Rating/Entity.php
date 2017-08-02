<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\Rating;

/**
 * Ratings entity model
 *
 * @method \Magento\Review\Model\ResourceModel\Rating\Entity _getResource()
 * @method \Magento\Review\Model\ResourceModel\Rating\Entity getResource()
 * @method string getEntityCode()
 * @method \Magento\Review\Model\Rating\Entity setEntityCode(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Entity extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Review\Model\ResourceModel\Rating\Entity::class);
    }

    /**
     * @param string $entityCode
     * @return int
     * @since 2.0.0
     */
    public function getIdByCode($entityCode)
    {
        return $this->_getResource()->getIdByCode($entityCode);
    }
}
