<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Index;

class ChangeLocale extends \Magento\Backend\Controller\Adminhtml\Index
{
    /**
     * Change locale action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function executeInternal()
    {
        $redirectResult = $this->resultRedirectFactory->create();
        $redirectResult->setRefererUrl();
        return $redirectResult;
    }
}
