<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\Settlement;

/**
 * Settlement reports transaction details
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Details extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Block construction
     * Initialize titles, buttons
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_controller = '';
        $this->_headerText = __('View Transaction Details');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('save');
    }

    /**
     * Initialize form
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->addChild('form', \Magento\Paypal\Block\Adminhtml\Settlement\Details\Form::class);
        return $this;
    }
}
