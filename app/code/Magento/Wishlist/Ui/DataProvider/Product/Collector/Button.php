<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Ui\DataProvider\Product\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRender\ButtonInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderInfoDtoInterface;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\ProductRenderInfoDto;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderInfoProviderInterface;
use Magento\Wishlist\Helper\Data;

/**
 * Collect information needed to render wishlist button on front
 * @since 2.2.0
 */
class Button implements ProductRenderCollectorInterface
{
    /** Url Key */
    const KEY_WISHLIST_URL_PARAMS = "wishlist_url_params";

    /**
     * @var Data
     * @since 2.2.0
     */
    private $wishlistHelper;

    /**
     * @var \Magento\Catalog\Api\Data\ProductRender\ProductRenderExtensionInterfaceFactory
     * @since 2.2.0
     */
    private $productRenderExtensionFactory;

    /**
     * @var ButtonInterfaceFactory
     * @since 2.2.0
     */
    private $buttonInterfaceFactory;

    /**
     * @param Data $wishlistHelper
     * @param \Magento\Catalog\Api\Data\ProductRenderExtensionFactory $productRenderExtensionFactory
     * @param ButtonInterfaceFactory $buttonInterfaceFactory
     * @since 2.2.0
     */
    public function __construct(
        Data $wishlistHelper,
        \Magento\Catalog\Api\Data\ProductRenderExtensionFactory $productRenderExtensionFactory,
        ButtonInterfaceFactory $buttonInterfaceFactory
    ) {
        $this->wishlistHelper = $wishlistHelper;
        $this->productRenderExtensionFactory = $productRenderExtensionFactory;
        $this->buttonInterfaceFactory = $buttonInterfaceFactory;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        /** @var \Magento\Catalog\Api\Data\ProductRenderExtensionInterface $extensionAttributes */
        $extensionAttributes = $productRender->getExtensionAttributes();

        if (!$extensionAttributes) {
            $extensionAttributes = $this->productRenderExtensionFactory->create();
        }

        $button = $this->buttonInterfaceFactory->create();
        $button->setUrl($this->wishlistHelper->getAddParams($product));
        $extensionAttributes->setWishlistButton($button);
        $productRender->setExtensionAttributes($extensionAttributes);
    }
}
