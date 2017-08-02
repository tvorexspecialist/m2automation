<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

/**
 * Class \Magento\Customer\Controller\Adminhtml\Index\ViewCart
 *
 * @since 2.0.0
 */
class ViewCart extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Get shopping cart to view only
     *
     * @return \Magento\Framework\View\Result\Layout
     * @since 2.0.0
     */
    public function execute()
    {
        $this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->getLayout()->getBlock('admin.customer.view.cart')->setWebsiteId(
            (int)$this->getRequest()->getParam('website_id')
        );
        return $resultLayout;
    }
}
