<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\AdminOrder;

use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Class EmailSender
 * @since 2.0.0
 */
class EmailSender
{
    /**
     * @var ManagerInterface
     * @since 2.0.0
     */
    protected $messageManager;

    /**
     * @var Logger
     * @since 2.0.0
     */
    protected $logger;

    /**
     * @var OrderSender
     * @since 2.0.0
     */
    protected $orderSender;

    /**
     * @param ManagerInterface $messageManager
     * @param Logger $logger
     * @param OrderSender $orderSender
     * @since 2.0.0
     */
    public function __construct(ManagerInterface $messageManager, Logger $logger, OrderSender $orderSender)
    {
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->orderSender = $orderSender;
    }

    /**
     * Send email about new order.
     * Process mail exception
     *
     * @param Order $order
     * @return bool
     * @since 2.0.0
     */
    public function send(Order $order)
    {
        try {
            $this->orderSender->send($order);
        } catch (\Magento\Framework\Exception\MailException $exception) {
            $this->logger->critical($exception);
            $this->messageManager->addWarning(
                __('You did not email your customer. Please check your email settings.')
            );
            return false;
        }

        return true;
    }
}
