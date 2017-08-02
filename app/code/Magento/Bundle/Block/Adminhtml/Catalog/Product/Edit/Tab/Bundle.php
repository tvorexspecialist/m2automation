<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab;

/**
 * Adminhtml catalog product bundle items tab block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Bundle extends \Magento\Backend\Block\Widget implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var mixed
     * @since 2.0.0
     */
    protected $_product = null;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'product/edit/bundle.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getTabUrl()
    {
        return $this->getUrl('adminhtml/bundle_product_edit/form', ['_current' => true]);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getTabClass()
    {
        return 'ajax';
    }

    /**
     * Prepare layout
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->setData('opened', true);
        $this->addChild(
            'add_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Create New Option'),
                'class' => 'add',
                'id' => 'add_new_option',
                'on_click' => 'bOption.add()'
            ]
        );

        $this->setChild(
            'options_box',
            $this->getLayout()->createBlock(
                \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option::class,
                'adminhtml.catalog.product.edit.tab.bundle.option'
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * Check block readonly
     *
     * @return boolean
     * @since 2.0.0
     */
    public function isReadonly()
    {
        return $this->getProduct()->getCompositeReadonly();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getOptionsBoxHtml()
    {
        return $this->getChildHtml('options_box');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getFieldSuffix()
    {
        return 'product';
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getTabLabel()
    {
        return __('Bundle Items');
    }

    /**
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getTabTitle()
    {
        return __('Bundle Items');
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get parent tab code
     *
     * @return string
     * @since 2.0.0
     */
    public function getParentTab()
    {
        return 'product-details';
    }
}
