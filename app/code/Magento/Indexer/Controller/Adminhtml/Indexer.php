<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Controller\Adminhtml;

/**
 * Class \Magento\Indexer\Controller\Adminhtml\Indexer
 *
 * @since 2.0.0
 */
abstract class Indexer extends \Magento\Backend\App\Action
{
    /**
     * Check ACL permissions
     *
     * @return bool
     * @since 2.0.0
     */
    protected function _isAllowed()
    {
        switch ($this->_request->getActionName()) {
            case 'list':
                return $this->_authorization->isAllowed('Magento_Indexer::index');
            case 'massOnTheFly':
            case 'massChangelog':
                return $this->_authorization->isAllowed('Magento_Indexer::changeMode');
        }
        return false;
    }
}
