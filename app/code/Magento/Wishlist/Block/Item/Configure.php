<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist Item Configure block
 * Serves for configuring item on product view page
 *
 * @module     Wishlist
 */
namespace Magento\Wishlist\Block\Item;

/**
 * @api
 * @since 2.0.0
 */
class Configure extends \Magento\Framework\View\Element\Template
{
    /**
     * Wishlist data
     *
     * @var \Magento\Wishlist\Helper\Data
     * @since 2.0.0
     */
    protected $_wishlistData = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Wishlist\Helper\Data $wishlistData
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Wishlist\Helper\Data $wishlistData,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_wishlistData = $wishlistData;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Return wishlist widget options
     *
     * @return array
     * @since 2.0.0
     */
    public function getWishlistOptions()
    {
        return ['productType' => $this->escapeHtml($this->getProduct()->getTypeId())];
    }

    /**
     * Returns product being edited
     *
     * @return \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Get update params for http post
     *
     * @return bool|string
     * @since 2.0.0
     */
    public function getUpdateParams()
    {
        return $this->_wishlistData->getUpdateParams($this->getWishlistItem());
    }

    /**
     * Returns wishlist item being configured
     *
     * @return \Magento\Catalog\Model\Product|\Magento\Wishlist\Model\Item
     * @since 2.0.0
     */
    protected function getWishlistItem()
    {
        return $this->_coreRegistry->registry('wishlist_item');
    }

    /**
     * Configure product view blocks
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        // Set custom add to cart url
        $block = $this->getLayout()->getBlock('product.info');
        if ($block && $this->getWishlistItem()) {
            $url = $this->_wishlistData->getAddToCartUrl($this->getWishlistItem());
            $block->setCustomAddToCartUrl($url);
        }

        return parent::_prepareLayout();
    }
}
