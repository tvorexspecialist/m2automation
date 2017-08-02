<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NewRelicReporting\Model\ResourceModel\Orders;

/**
 * Class \Magento\NewRelicReporting\Model\ResourceModel\Orders\Collection
 *
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize orders resource collection
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\NewRelicReporting\Model\Orders::class,
            \Magento\NewRelicReporting\Model\ResourceModel\Orders::class
        );
    }
}
