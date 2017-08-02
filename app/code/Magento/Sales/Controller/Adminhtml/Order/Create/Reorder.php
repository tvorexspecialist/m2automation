<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Create;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\Create\Reorder
 *
 * @since 2.0.0
 */
class Reorder extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_getSession()->clearStorage();
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);
        if (!$this->_objectManager->get(\Magento\Sales\Helper\Reorder::class)->canReorder($order->getEntityId())) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($order->getId()) {
            $order->setReordered(true);
            $this->_getSession()->setUseOldShippingMethod(true);
            $this->_getOrderCreateModel()->initFromOrder($order);

            $resultRedirect->setPath('sales/*');
        } else {
            $resultRedirect->setPath('sales/order/');
        }
        return $resultRedirect;
    }
}
