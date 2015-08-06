<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;

class MassUnhold extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var string
     */
    protected $collection = 'Magento\Sales\Model\Resource\Order\Collection';

    /**
     * Unhold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $countUnHoldOrder = 0;

        $orderManagement = $this->_objectManager->get('Magento\Sales\Api\OrderManagementInterface');
        foreach ($collection->getItems() as $order) {
            if (!$order->canUnhold()) {
                continue;
            }
            $orderManagement->unHold($order->getEntityId());
            $countUnHoldOrder++;
        }

        $countNonUnHoldOrder = $collection->count() - $countUnHoldOrder;

        if ($countNonUnHoldOrder && $countUnHoldOrder) {
            $this->messageManager->addError(
                __('%1 order(s) were not released from on hold status.', $countNonUnHoldOrder)
            );
        } elseif ($countNonUnHoldOrder) {
            $this->messageManager->addError(__('No order(s) were released from on hold status.'));
        }

        if ($countUnHoldOrder) {
            $this->messageManager->addSuccess(
                __('%1 order(s) have been released from on hold status.', $countUnHoldOrder)
            );
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/*/');
        return $resultRedirect;
    }
}
