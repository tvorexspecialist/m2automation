<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

/**
 * Class \Magento\Customer\Controller\Adminhtml\Index\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customers list action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Forward
     * @since 2.0.0
     */
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }
        $resultPage = $this->resultPageFactory->create();
        /**
         * Set active menu item
         */
        $resultPage->setActiveMenu('Magento_Customer::customer_manage');
        $resultPage->getConfig()->getTitle()->prepend(__('Customers'));

        /**
         * Add breadcrumb item
         */
        $resultPage->addBreadcrumb(__('Customers'), __('Customers'));
        $resultPage->addBreadcrumb(__('Manage Customers'), __('Manage Customers'));

        $this->_getSession()->unsCustomerData();
        $this->_getSession()->unsCustomerFormData();

        return $resultPage;
    }
}
