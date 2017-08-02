<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Block\Adminhtml\Rating\Edit;

/**
 * Admin rating left menu
 *
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
        $this->setId('rating_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Rating Information'));
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_section',
            [
                'label' => __('Rating Information'),
                'title' => __('Rating Information'),
                'content' => $this->getLayout()
                        ->createBlock(\Magento\Review\Block\Adminhtml\Rating\Edit\Tab\Form::class)
                        ->toHtml()
            ]
        );
        return parent::_beforeToHtml();
    }
}
