<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Block\Tracking;

/**
 * Tracking info link
 *
 * @api
 * @since 2.0.0
 */
class Link extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry;

    /**
     * Shipping data
     *
     * @var \Magento\Shipping\Helper\Data
     * @since 2.0.0
     */
    protected $_shippingData;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Shipping\Helper\Data $shippingData
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Shipping\Helper\Data $shippingData,
        array $data = []
    ) {
        $this->_shippingData = $shippingData;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Sales\Model\AbstractModel $model
     * @return string
     * @since 2.0.0
     */
    public function getWindowUrl($model)
    {
        return $this->_shippingData->getTrackingPopupUrlBySalesModel($model);
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }
}
