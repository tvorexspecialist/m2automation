<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Model\Plugin\Order;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\SalesInventory\Model\Order\ReturnProcessor;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\RefundInvoiceInterface;

/**
 * Class ReturnToStockInvoice
 */
class ReturnToStockInvoice
{
    /**
     * @var ReturnProcessor
     */
    private $returnProcessor;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * ReturnToStockInvoice constructor.
     * @param ReturnProcessor $returnProcessor
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        ReturnProcessor $returnProcessor,
        CreditmemoRepositoryInterface $creditmemoRepository,
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->returnProcessor = $returnProcessor;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * @param RefundInvoiceInterface $refundService
     * @param int $resultEntityId
     * @param int $invoiceId
     * @param \Magento\Sales\Api\Data\CreditmemoItemCreationInterface[] $items
     * @param bool|null $isOnline
     * @param bool|null $notify
     * @param bool|null $appendComment
     * @param \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface|null $comment
     * @param \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface|null $arguments
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        RefundInvoiceInterface $refundService,
        $resultEntityId,
        $invoiceId,
        array $items = [],
        $isOnline = false,
        $notify = false,
        $appendComment = false,
        \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment = null,
        \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface $arguments = null
    ) {
        if ($this->stockConfiguration->isAutoReturnEnabled()) {
            return $resultEntityId;
        }

        $invoice = $this->invoiceRepository->get($invoiceId);
        $order = $this->orderRepository->get($invoice->getOrderId());

        $returnToStockItems = [];
        if ($arguments !== null
            && $arguments->getExtensionAttributes() !== null
            && $arguments->getExtensionAttributes()->getReturnToStockItems() !== null
        ) {
            $returnToStockItems = $arguments->getExtensionAttributes()->getReturnToStockItems();
        }

        $creditmemo = $this->creditmemoRepository->get($resultEntityId);
        $this->returnProcessor->execute($creditmemo, $order, $returnToStockItems);

        return $resultEntityId;
    }
}
