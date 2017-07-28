<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\ProductList\Item\AddTo;

/**
 * Add product to compare
 *
 * @api
 * @since 2.2.0
 */
class Compare extends \Magento\Catalog\Block\Product\ProductList\Item\Block
{
    /**
     * @return \Magento\Catalog\Helper\Product\Compare
     * @since 2.2.0
     */
    public function getCompareHelper()
    {
        return $this->_compareProduct;
    }
}
