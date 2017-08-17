<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Block\System;

/**
 * Custom Variable Block
 *
 * @api
 */
class Variable extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Variable';
        $this->_controller = 'system_variable';
        $this->_headerText = __('Custom Variables');
        parent::_construct();
        $this->buttonList->update('add', 'label', __('Add New Variable'));
    }
}
