<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Model\ResourceModel\Stock\Customer;

/**
 * ProductAlert Stock Customer collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Collection extends \Magento\Customer\Model\ResourceModel\Customer\Collection
{
    /**
     * join productalert stock data to customer collection
     *
     * @param int $productId
     * @param int $websiteId
     * @return $this
     * @since 2.0.0
     */
    public function join($productId, $websiteId)
    {
        $this->getSelect()->join(
            ['alert' => $this->getTable('product_alert_stock')],
            'alert.customer_id=e.entity_id',
            ['alert_stock_id', 'add_date', 'send_date', 'send_count', 'status']
        );

        $this->getSelect()->where('alert.product_id=?', $productId);
        if ($websiteId) {
            $this->getSelect()->where('alert.website_id=?', $websiteId);
        }
        $this->_setIdFieldName('alert_stock_id');
        $this->addAttributeToSelect('*');

        return $this;
    }
}
