<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Template;

/**
 * Class \Magento\Newsletter\Controller\Adminhtml\Template\Grid
 *
 * @since 2.0.0
 */
class Grid extends \Magento\Newsletter\Controller\Adminhtml\Template
{
    /**
     * JSON Grid Action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $grid = $this->_view->getLayout()->createBlock(
            \Magento\Newsletter\Block\Adminhtml\Template\Grid::class
        )->toHtml();
        $this->getResponse()->setBody($grid);
    }
}
