<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Controller\Adminhtml\Index;

class Grid extends \Magento\Backup\Controller\Adminhtml\Index
{
    /**
     * Backup list action
     *
     * @return void
     */
    public function executeInternal()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
