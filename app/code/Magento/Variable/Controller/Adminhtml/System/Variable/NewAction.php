<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Controller\Adminhtml\System\Variable;

class NewAction extends \Magento\Variable\Controller\Adminhtml\System\Variable
{
    /**
     * New Action (forward to edit action)
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function executeInternal()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
