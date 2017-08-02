<?php
/**
 * Product controller.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Marketplace\Controller\Adminhtml;

/**
 * Class \Magento\Marketplace\Controller\Adminhtml\Partners
 *
 * @since 2.0.0
 */
abstract class Partners extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Marketplace::partners';
}
