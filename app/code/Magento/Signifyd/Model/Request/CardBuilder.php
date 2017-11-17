<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\Request;

use Magento\Sales\Model\Order;

/**
 * Prepare data related to the card that was used for the purchase and its cardholder.
 */
class CardBuilder
{
    /**
     * @var AddressBuilder
     */
    private $addressBuilder;

    /**
     * @param AddressBuilder $addressBuilder
     */
    public function __construct(
        AddressBuilder $addressBuilder
    ) {
        $this->addressBuilder = $addressBuilder;
    }

    /**
     * Returns card data params
     *
     * @param Order $order
     * @return array
     */
    public function build(Order $order)
    {
        $result = [];
        $address = $order->getBillingAddress();
        if ($address === null) {
            return $result;
        }

        $payment = $order->getPayment();
        $result = [
            'cardHolderName' => $address->getFirstname() . ' ' . $address->getLastname(),
            'last4' => $payment->getCcLast4(),
            'expiryMonth' => $payment->getCcExpMonth(),
            'expiryYear' =>  $payment->getCcExpYear(),
            'billingAddress' => $this->addressBuilder->build($address),
        ];

        return $result;
    }
}
