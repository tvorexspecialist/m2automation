<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Block\Adminhtml\Form\Field;

/**
 * Adminhtml catalog inventory "Minimum Qty Allowed in Shopping Cart" field
 *
 * @api
 * @since 2.0.0
 */
class Minsaleqty extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var Customergroup
     * @since 2.0.0
     */
    protected $_groupRenderer;

    /**
     * Retrieve group column renderer
     *
     * @return Customergroup
     * @since 2.0.0
     */
    protected function _getGroupRenderer()
    {
        if (!$this->_groupRenderer) {
            $this->_groupRenderer = $this->getLayout()->createBlock(
                \Magento\CatalogInventory\Block\Adminhtml\Form\Field\Customergroup::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_groupRenderer->setClass('customer_group_select');
        }
        return $this->_groupRenderer;
    }

    /**
     * Prepare to render
     *
     * @return void
     * @since 2.0.0
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'customer_group_id',
            ['label' => __('Customer Group'), 'renderer' => $this->_getGroupRenderer()]
        );
        $this->addColumn('min_sale_qty', ['label' => __('Minimum Qty')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Minimum Qty');
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     * @since 2.0.0
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getGroupRenderer()->calcOptionHash($row->getData('customer_group_id'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}
