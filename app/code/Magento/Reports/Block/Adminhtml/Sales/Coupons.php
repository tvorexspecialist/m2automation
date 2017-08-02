<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Sales;

/**
 * Adminhtml coupons report page content block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Coupons extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Template file
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'report/grid/container.phtml';

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magento_Reports';
        $this->_controller = 'adminhtml_sales_coupons';
        $this->_headerText = __('Coupons Usage Report');
        parent::_construct();

        $this->buttonList->remove('add');
        $this->addButton(
            'filter_form_submit',
            ['label' => __('Show Report'), 'onclick' => 'filterFormSubmit()', 'class' => 'primary']
        );
    }

    /**
     * Get filter URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/coupons', ['_current' => true]);
    }
}
