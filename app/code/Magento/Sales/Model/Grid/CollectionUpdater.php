<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Grid;

/**
 * Class \Magento\Sales\Model\Grid\CollectionUpdater
 *
 * @since 2.0.0
 */
class CollectionUpdater implements \Magento\Framework\View\Layout\Argument\UpdaterInterface
{
    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $registryManager;

    /**
     * @param \Magento\Framework\Registry $registryManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Registry $registryManager)
    {
        $this->registryManager = $registryManager;
    }

    /**
     * Update grid collection according to chosen order
     *
     * @param \Magento\Sales\Model\ResourceModel\Transaction\Grid\Collection $argument
     * @return \Magento\Sales\Model\ResourceModel\Transaction\Grid\Collection
     * @since 2.0.0
     */
    public function update($argument)
    {
        $order = $this->registryManager->registry('current_order');
        if ($order) {
            $argument->setOrderFilter($order->getId());
        }
        $argument->addOrderInformation(['increment_id']);

        return $argument;
    }
}
