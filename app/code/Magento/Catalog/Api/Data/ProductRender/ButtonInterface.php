<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data\ProductRender;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Button interface.
 *
 * This interface represents all manner of product buttons: add to cart, add to compare, etc...
 * The buttons describes by this interface should have interaction with backend
 * @api
 */
interface ButtonInterface extends ExtensibleDataInterface
{
    /**
     * @param string $postData Post data should be serialized (JSON/serialized) string
     * Post data can be empty
     * @return void
     */
    public function setPostData($postData);

    /**
     * Retrieve post data
     *
     * Post data is serialized data, which represents post params, that should goes on backend, in order
     * to handle product action
     *
     * @return string
     */
    public function getPostData();

    /**
     * Set button end point
     *
     * End point can be represented by any backend url, where button request can be handled
     *
     * @param string $url
     * @return void
     */
    public function setUrl($url);

    /**
     * Retrieve url, needed to add product to cart
     *
     * @return string
     */
    public function getUrl();

    /**
     * Required options is flag for options (attributes), without which we cant do actions with a product
     * E.g.: without product size we cant add this product to cart
     *
     * @param bool $requiredOptions
     * @return void
     */
    public function setRequiredOptions($requiredOptions);

    /**
     * Retrieve flag whether a product has options or not
     *
     * @return bool
     */
    public function hasRequiredOptions();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\ButtonExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductRender\ButtonExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductRender\ButtonExtensionInterface $extensionAttributes
    );
}
