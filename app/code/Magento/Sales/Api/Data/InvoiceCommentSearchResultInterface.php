<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Invoice comment search result interface.
 *
 * An invoice is a record of the receipt of payment for an order. An invoice can include comments that detail the
 * invoice history.
 * @api
 * @since 2.0.0
 */
interface InvoiceCommentSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Magento\Sales\Api\Data\InvoiceCommentInterface[] Array of collection items.
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Sets collection items.
     *
     * @param \Magento\Sales\Api\Data\InvoiceCommentInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items);
}
