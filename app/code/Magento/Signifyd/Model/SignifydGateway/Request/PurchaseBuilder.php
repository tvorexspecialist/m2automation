<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Request;

use Magento\Framework\App\Area;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Config\ScopeInterface;
use Magento\Sales\Model\Order;

/**
 * Prepare data related to purchase event represented in Case Creation request.
 */
class PurchaseBuilder
{
    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var ScopeInterface
     */
    private $scope;

    /**
     * @var IdentityGeneratorInterface
     */
    private $identityGenerator;

    /**
     * @param DateTimeFactory $dateTimeFactory
     * @param ScopeInterface $scope
     * @param IdentityGeneratorInterface $identityGenerator
     */
    public function __construct(
        DateTimeFactory $dateTimeFactory,
        ScopeInterface $scope,
        IdentityGeneratorInterface $identityGenerator
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->scope = $scope;
        $this->identityGenerator = $identityGenerator;
    }

    /**
     * Returns purchase data params
     *
     * @param Order $order
     * @return array
     */
    public function build(Order $order)
    {
        $orderPayment = $order->getPayment();
        $createdAt = $this->dateTimeFactory->create(
            $order->getCreatedAt(),
            new \DateTimeZone('UTC')
        );

        $result = [
            'purchase' => [
                'orderSessionId' => $this->identityGenerator->generateIdForData($order->getQuoteId()),
                'browserIpAddress' => $order->getRemoteIp(),
                'orderId' => $order->getEntityId(),
                'createdAt' => $createdAt->format(\DateTime::ATOM),
                'paymentGateway' => $this->getPaymentGateway($orderPayment->getMethod()),
                'transactionId' => $orderPayment->getLastTransId(),
                'currency' => $order->getOrderCurrencyCode(),
                'orderChannel' => $this->getOrderChannel(),
                'totalPrice' => $order->getGrandTotal(),
            ],
        ];

        $shippingDescription = $order->getShippingDescription();
        if ($shippingDescription !== null) {
            $result['purchase']['shipments'] = [
                [
                    'shipper' => $this->getShipper($order->getShippingDescription()),
                    'shippingMethod' => $this->getShippingMethod($order->getShippingDescription()),
                    'shippingPrice' => $order->getShippingAmount()
                ]
            ];
        }

        $products = $this->getProducts($order);
        if (!empty($products)) {
            $result['purchase']['products'] = $products;
        }

        return $result;
    }

    /**
     * Gets the products purchased in the transaction.
     *
     * @param Order $order
     * @return array
     */
    private function getProducts(Order $order)
    {
        $result = [];
        foreach ($order->getAllItems() as $orderItem) {
            $result[] = [
                'itemId' => $orderItem->getSku(),
                'itemName' => $orderItem->getName(),
                'itemPrice' => $orderItem->getPrice(),
                'itemQuantity' => (int)$orderItem->getQtyOrdered(),
                'itemUrl' => $orderItem->getProduct()->getProductUrl(),
                'itemWeight' => $orderItem->getProduct()->getWeight()
            ];
        }

        return $result;
    }

    /**
     * Gets the name of the shipper
     *
     * @param string $shippingDescription
     * @return string
     */
    private function getShipper($shippingDescription)
    {
        $result = explode(' - ', $shippingDescription, 2);

        return count($result) == 2 ? $result[0] : '';
    }

    /**
     * Gets the type of the shipment method used
     *
     * @param string $shippingDescription
     * @return string
     */
    private function getShippingMethod($shippingDescription)
    {
        $result = explode(' - ', $shippingDescription, 2);

        return count($result) == 2 ? $result[1] : '';
    }

    /**
     * Gets the gateway that processed the transaction. For PayPal orders use paypal_account.
     *
     * @param string $gatewayCode
     * @return string
     */
    private function getPaymentGateway($gatewayCode)
    {
        return strstr($gatewayCode, 'paypal') === false ? $gatewayCode : 'paypal_account';
    }

    /**
     * Gets WEB for web-orders, PHONE for orders created by Admin
     *
     * @return string
     */
    private function getOrderChannel()
    {
        return $this->scope->getCurrentScope() === Area::AREA_ADMINHTML ? 'PHONE' : 'WEB';
    }
}
