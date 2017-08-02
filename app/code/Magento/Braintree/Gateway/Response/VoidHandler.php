<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Response;

use Magento\Sales\Model\Order\Payment;

/**
 * Class \Magento\Braintree\Gateway\Response\VoidHandler
 *
 * @since 2.1.0
 */
class VoidHandler extends TransactionIdHandler
{
    /**
     * @param Payment $orderPayment
     * @param \Braintree\Transaction $transaction
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    protected function setTransactionId(Payment $orderPayment, \Braintree\Transaction $transaction)
    {
        return;
    }

    /**
     * Whether transaction should be closed
     *
     * @return bool
     * @since 2.1.0
     */
    protected function shouldCloseTransaction()
    {
        return true;
    }

    /**
     * Whether parent transaction should be closed
     *
     * @param Payment $orderPayment
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    protected function shouldCloseParentTransaction(Payment $orderPayment)
    {
        return true;
    }
}
