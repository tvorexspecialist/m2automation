<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart;

/**
 * "My Cart" link
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 2.0.0
 */
class Link extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Magento\Framework\Module\Manager
     * @since 2.0.0
     */
    protected $_moduleManager;

    /**
     * @var \Magento\Checkout\Helper\Cart
     * @since 2.0.0
     */
    protected $_cartHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param array $data
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Checkout\Helper\Cart $cartHelper,
        array $data = []
    ) {
        $this->_cartHelper = $cartHelper;
        parent::__construct($context, $data);
        $this->_moduleManager = $moduleManager;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getLabel()
    {
        return $this->_createLabel($this->_getItemCount());
    }

    /**
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getHref()
    {
        return $this->getUrl('checkout/cart');
    }

    /**
     * Render block HTML
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        if (!$this->_moduleManager->isOutputEnabled('Magento_Checkout')) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Count items in cart
     *
     * @return int
     * @since 2.0.0
     */
    protected function _getItemCount()
    {
        $count = $this->getSummaryQty();
        return $count ? $count : $this->_cartHelper->getSummaryCount();
    }

    /**
     * Create link label based on cart item quantity
     *
     * @param int $count
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    protected function _createLabel($count)
    {
        if ($count == 1) {
            return __('My Cart (1 item)');
        } elseif ($count > 0) {
            return __('My Cart (%1 items)', $count);
        } else {
            return __('My Cart');
        }
    }
}
