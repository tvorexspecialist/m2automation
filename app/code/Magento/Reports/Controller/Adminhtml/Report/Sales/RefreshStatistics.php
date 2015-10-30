<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

class RefreshStatistics extends \Magento\Reports\Controller\Adminhtml\Report\Sales
{
    /**
     * Refresh report statistics action
     *
     * @return void
     */
    public function executeInternal()
    {
        $this->_forward('index', 'report_statistics');
    }
}
