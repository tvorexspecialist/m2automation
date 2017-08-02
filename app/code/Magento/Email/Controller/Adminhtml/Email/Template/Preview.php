<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Controller\Adminhtml\Email\Template;

/**
 * Class \Magento\Email\Controller\Adminhtml\Email\Template\Preview
 *
 * @since 2.0.0
 */
class Preview extends \Magento\Email\Controller\Adminhtml\Email\Template
{
    /**
     * Preview transactional email action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        try {
            $this->_view->loadLayout();
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Email Preview'));
            $this->_view->renderLayout();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred. The email template can not be opened for preview.'));
            $this->_redirect('adminhtml/*/');
        }
    }
}
