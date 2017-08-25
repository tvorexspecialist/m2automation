<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Queue;

/**
 * Class \Magento\Newsletter\Controller\Adminhtml\Queue\Sending
 *
 */
class Sending extends \Magento\Newsletter\Controller\Adminhtml\Queue
{
    /**
     * Send Newsletter queue
     *
     * @return void
     */
    public function execute()
    {
        // Todo: put it somewhere in config!
        $countOfQueue = 3;
        $countOfSubscritions = 20;

        $collection = $this->_objectManager->create(
            \Magento\Newsletter\Model\ResourceModel\Queue\Collection::class
        )->setPageSize(
            $countOfQueue
        )->setCurPage(
            1
        )->addOnlyForSendingFilter()->load();

        $collection->walk('sendPerSubscriber', [$countOfSubscritions]);
    }
}
