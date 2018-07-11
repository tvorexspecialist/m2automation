<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\Product\Configuration\Item;

use Magento\Catalog\Model\Config\Source\Product\Thumbnail;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;

/**
 * {@inheritdoc}
 */
class ItemProductResolver implements ItemResolverInterface
{
    /**
     * Path in config to the setting which defines if parent or child product should be used to generate a thumbnail.
     */
    const CONFIG_THUMBNAIL_SOURCE = 'checkout/cart/grouped_product_image';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getFinalProduct(ItemInterface $item) : ProductInterface
    {
        /**
         * Show grouped product thumbnail if it must be always shown according to the related setting in system config
         * or if child product thumbnail is not available
         */
        $config = $this->scopeConfig->getValue(
            self::CONFIG_THUMBNAIL_SOURCE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $childProduct = $item->getProduct();
        $childThumbnail = $childProduct->getData('thumbnail');

        $finalProduct =
            ($config == Thumbnail::OPTION_USE_PARENT_IMAGE) || (!$childThumbnail || $childThumbnail == 'no_selection')
            ?  $this->getParentProduct($item)
            : $childProduct;
        return $finalProduct;
    }

    /**
     * Get grouped product
     *
     * @param ItemInterface $item
     * @return Product
     */
    private function getParentProduct(ItemInterface $item) : Product
    {
        $option = $item->getOptionByCode('product_type');
        $product = $item->getProduct();
        if ($option) {
            $product = $option->getProduct();
        }
        return $product;
    }
}
