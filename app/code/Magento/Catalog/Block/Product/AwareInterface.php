<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product;

/**
 * Class Product Aware interface
 */
interface AwareInterface
{
    /**
     * Set product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return $this
     */
    public function setProduct(\Magento\Catalog\Api\Data\ProductInterface $product);
}
