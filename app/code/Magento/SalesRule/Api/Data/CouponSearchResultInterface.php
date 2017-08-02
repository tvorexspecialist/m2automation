<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Api\Data;

/**
 * @api
 * @since 2.0.0
 */
interface CouponSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get rules.
     *
     * @return \Magento\SalesRule\Api\Data\CouponInterface[]
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Set rules .
     *
     * @param \Magento\SalesRule\Api\Data\CouponInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items = null);
}
