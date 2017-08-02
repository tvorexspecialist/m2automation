<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Edit;

/**
 * Class \Magento\Sales\Controller\Adminhtml\Order\Edit\ProcessData
 *
 * @since 2.0.0
 */
class ProcessData extends \Magento\Sales\Controller\Adminhtml\Order\Create\ProcessData
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::actions_edit';
}
