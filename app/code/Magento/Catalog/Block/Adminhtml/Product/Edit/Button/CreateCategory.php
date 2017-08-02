<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Button;

/**
 * Button "Create Category" in "New Category" slide-out panel of a product page
 * @since 2.1.0
 */
class CreateCategory extends Generic
{
    /**
     * @return array
     * @since 2.1.0
     */
    public function getButtonData()
    {
        return [
            'label' => __('Create Category'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 10
        ];
    }
}
