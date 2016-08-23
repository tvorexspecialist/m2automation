<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface ProductTierPriceInterface extends ExtensibleDataInterface
{
    const QTY = 'qty';

    const VALUE = 'value';

    const PERCENTAGE_VALUE = 'percentage_value';

    const CUSTOMER_GROUP_ID = 'customer_group_id';

    const WEBSITE_ID = 'website_id';

    /**
     * Retrieve customer group id
     *
     * @return int
     */
    public function getCustomerGroupId();

    /**
     * Set customer group id
     *
     * @param int $customerGroupId
     * @return $this
     */
    public function setCustomerGroupId($customerGroupId);

    /**
     * Retrieve tier qty
     *
     * @return float
     */
    public function getQty();

    /**
     * Set tier qty
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Retrieve price value
     *
     * @return float
     */
    public function getValue();

    /**
     * Set price value
     *
     * @param float $value
     * @return $this
     */
    public function setValue($value);

    /**
     * Retrieve percentage value
     * @return float
     */
    public function getPercentageValue();

    /**
     * Set percentage value
     *
     * @param $value
     * @return $this
     */
    public function setPercentageValue($value);

    /**
     * Retrieve website id
     * @return int
     */
    public function getWebsiteId();

    /**
     * Set website id
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Magento\Catalog\Api\Data\ProductTierPriceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\ProductTierPriceExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductTierPriceExtensionInterface $extensionAttributes
    );
}
