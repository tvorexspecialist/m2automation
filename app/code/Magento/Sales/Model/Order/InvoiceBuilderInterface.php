<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\InvoiceCommentCreationInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemCreationInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface;

/**
 * Interface InvoiceBuilder
 *
 * @api
 */
interface InvoiceBuilderInterface
{
    /**
     * @param OrderInterface $order
     * @return void
     */
    public function setOrder(OrderInterface $order);

    /**
     * @return OrderInterface
     */
    public function getOrder();

    /**
     * @param InvoiceItemCreationInterface[] $items
     * @return void
     */
    public function setItems(array $items);

    /**
     * @return InvoiceItemCreationInterface[]
     */
    public function getItems();

    /**
     * @param InvoiceCommentCreationInterface $comment
     * @return void
     */
    public function setComment(InvoiceCommentCreationInterface $comment);

    /**
     * @return InvoiceCommentCreationInterface
     */
    public function getComment();

    /**
     * @param InvoiceCreationArgumentsInterface $arguments
     * @return void
     */
    public function setCreationArguments(InvoiceCreationArgumentsInterface $arguments);

    /**
     * @return InvoiceCreationArgumentsInterface
     */
    public function getCreationArguments();

    /**
     * @return InvoiceInterface
     */
    public function create();
}
