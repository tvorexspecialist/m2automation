<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\ResourceModel;

/**
 * Correct particular stock products qty
 * @since 2.0.0
 */
interface QtyCounterInterface
{
    /**
     * Correct particular stock products qty based on operator
     *
     * @param int[] $items
     * @param int $websiteId
     * @param string $operator +/-
     * @return void
     * @since 2.0.0
     */
    public function correctItemsQty(array $items, $websiteId, $operator);
}
