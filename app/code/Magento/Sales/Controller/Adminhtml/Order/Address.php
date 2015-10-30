<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;

class Address extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Edit order address form
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function executeInternal()
    {
        $addressId = $this->getRequest()->getParam('address_id');
        $address = $this->_objectManager->create('Magento\Sales\Model\Order\Address')->load($addressId);
        if ($address->getId()) {
            $this->_coreRegistry->register('order_address', $address);
            $resultPage = $this->resultPageFactory->create();
            // Do not display VAT validation button on edit order address form
            $addressFormContainer = $resultPage->getLayout()->getBlock('sales_order_address.form.container');
            if ($addressFormContainer) {
                $addressFormContainer->getChildBlock('form')->setDisplayVatValidationButton(false);
            }

            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath('sales/*/');
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::actions_edit');
    }
}
