<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Initialization\Helper;

/**
 * Class \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks
 *
 * @since 2.0.0
 */
class ProductLinks
{
    /**
     * Init product links data (related, upsell, cross sell)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $links link data
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function initializeLinks(\Magento\Catalog\Model\Product $product, array $links)
    {
        return $product;
    }
}
