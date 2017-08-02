<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product;

/**
 * Class Product Aware interface
 * @since 2.2.0
 */
interface AwareInterface
{
    /**
     * Set product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return $this
     * @since 2.2.0
     */
    public function setProduct(\Magento\Catalog\Api\Data\ProductInterface $product);
}
