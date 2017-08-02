<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Customer;

/**
 * Class \Magento\Reports\Controller\Adminhtml\Report\Customer\Accounts
 *
 * @since 2.0.0
 */
class Accounts extends \Magento\Reports\Controller\Adminhtml\Report\Customer
{
    /**
     * New accounts action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Magento_Reports::report_customers_accounts'
        )->_addBreadcrumb(
            __('New Accounts'),
            __('New Accounts')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('New Accounts Report'));
        $this->_view->renderLayout();
    }
}
