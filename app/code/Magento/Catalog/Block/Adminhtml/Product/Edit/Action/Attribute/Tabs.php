<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute;

/**
 * Adminhtml catalog product edit action attributes update tabs block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('attributes_update_tabs');
        $this->setDestElementId('attributes-edit-form');
        $this->setTitle(__('Products Information'));
    }
}
