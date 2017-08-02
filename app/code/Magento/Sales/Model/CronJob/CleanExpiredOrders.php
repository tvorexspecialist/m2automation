<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\CronJob;

use Magento\Store\Model\StoresConfig;
use Magento\Sales\Model\Order;

/**
 * Class \Magento\Sales\Model\CronJob\CleanExpiredOrders
 *
 * @since 2.0.0
 */
class CleanExpiredOrders
{
    /**
     * @var StoresConfig
     * @since 2.0.0
     */
    protected $storesConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     * @since 2.0.0
     */
    protected $orderCollectionFactory;

    /**
     * @param StoresConfig $storesConfig
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @since 2.0.0
     */
    public function __construct(
        StoresConfig $storesConfig,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
    ) {
        $this->storesConfig = $storesConfig;
        $this->orderCollectionFactory = $collectionFactory;
    }

    /**
     * Clean expired quotes (cron process)
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $lifetimes = $this->storesConfig->getStoresConfigByPath('sales/orders/delete_pending_after');
        foreach ($lifetimes as $storeId => $lifetime) {
            /** @var $orders \Magento\Sales\Model\ResourceModel\Order\Collection */
            $orders = $this->orderCollectionFactory->create();
            $orders->addFieldToFilter('store_id', $storeId);
            $orders->addFieldToFilter('status', Order::STATE_PENDING_PAYMENT);
            $orders->getSelect()->where(
                new \Zend_Db_Expr('TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP, `updated_at`)) >= ' . $lifetime * 60)
            );
            $orders->walk('cancel');
            $orders->walk('save');
        }
    }
}
