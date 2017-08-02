<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Model\ResourceModel\Inbox;

/**
 * AdminNotification Inbox model
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource collection initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\AdminNotification\Model\Inbox::class,
            \Magento\AdminNotification\Model\ResourceModel\Inbox::class
        );
    }

    /**
     * Add remove filter
     *
     * @return $this
     * @since 2.0.0
     */
    public function addRemoveFilter()
    {
        $this->getSelect()->where('is_remove=?', 0);
        return $this;
    }
}
