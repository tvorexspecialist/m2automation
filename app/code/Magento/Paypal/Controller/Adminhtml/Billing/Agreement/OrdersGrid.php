<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Adminhtml\Billing\Agreement;

class OrdersGrid extends \Magento\Paypal\Controller\Adminhtml\Billing\Agreement
{
    /**
     * Related orders ajax action
     *
     * @return void
     */
    public function executeInternal()
    {
        $this->_initBillingAgreement();
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
