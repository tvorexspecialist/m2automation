<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ProductLink\CollectionProvider;

/**
 * Class \Magento\Catalog\Model\ProductLink\CollectionProvider\Crosssell
 *
 * @since 2.0.0
 */
class Crosssell implements \Magento\Catalog\Model\ProductLink\CollectionProviderInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getLinkedProducts(\Magento\Catalog\Model\Product $product)
    {
        return $product->getCrossSellProducts();
    }
}
