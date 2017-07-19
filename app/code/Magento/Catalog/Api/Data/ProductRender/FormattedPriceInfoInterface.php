<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data\ProductRender;

/**
 * Formatted Price interface.
 *
 * Aggregate formatted html with price representations.
 * E.g.:
 * <span class="price">$9.00</span>
 * Consider currency, rounding and html
 *
 * @api
 */
interface FormattedPriceInfoInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Retrieve html with final price
     *
     * @return string
     */
    public function getFinalPrice();

    /**
     * Set the final price: usually it calculated as minimal price of the product
     * Can be different depends on type of product
     *
     * @param string $finalPrice
     * @return void
     */
    public function setFinalPrice($finalPrice);

    /**
     * Retrieve max price of a product
     * E.g. for product with custom options is price with the most expensive custom option
     *
     * @return string
     */
    public function getMaxPrice();

    /**
     * Set the max price of the product
     *
     * @param string $maxPrice
     * @return void
     */
    public function setMaxPrice($maxPrice);

    /**
     * Retrieve the minimal price of the product or variation
     * The minimal price is for example, the lowest price of all variations for complex product
     *
     * @return string
     */
    public function getMinimalPrice();

    /**
     * Set max regular price
     * Max regular price is the same, as maximum price, except of excluding calculating special price and catalogules
     * in it
     *
     * @param string $maxRegularPrice
     * @return void
     */
    public function setMaxRegularPrice($maxRegularPrice);

    /**
     * Retrieve max regular price
     *
     * @return string
     */
    public function getMaxRegularPrice();

    /**
     * The minimal regular price has the same behavior of calculation as max regular price, but is opposite price
     *
     * @param string $minRegularPrice
     * @return void
     */
    public function setMinimalRegularPrice($minRegularPrice);

    /**
     * Retrieve minimal regular price
     *
     * @return string
     */
    public function getMinimalRegularPrice();

    /**
     * Set special price
     *
     * Special price - is temporary price, that can be set to specific product
     *
     * @param string $specialPrice
     * @return void
     */
    public function setSpecialPrice($specialPrice);

    /**
     * Retrieve special price
     *
     * @return string
     */
    public function getSpecialPrice();

    /**
     * Set minimal price
     *
     * @param string $minimalPrice
     * @return void
     */
    public function setMinimalPrice($minimalPrice);

    /**
     * Regular price - is price of product without discounts and special price with taxes and fixed product tax
     * Usually this price is corresponding to price in admin panel of product
     *
     * @return string
     */
    public function getRegularPrice();

    /**
     * Set regular price
     *
     * @param string $regularPrice
     * @return void
     */
    public function setRegularPrice($regularPrice);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductRender\FormattedPriceInfoExtensionInterface $extensionAttributes
    );
}
