<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Store;

/**
 * Adminhtml sales order create select store block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Select extends \Magento\Backend\Block\Store\Switcher
{
    /**
     * Constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sc_store_select');
    }
}
