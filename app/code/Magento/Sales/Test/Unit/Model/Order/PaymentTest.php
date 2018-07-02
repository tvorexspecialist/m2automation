<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\Model\Context;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class PaymentTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class PaymentTest extends \PHPUnit\Framework\TestCase
{
    const TRANSACTION_ID = 'ewr34fM49V0';

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var OrderStateResolverInterface|MockObject
     */
    private $orderStateResolver;

    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var Data|MockObject
     */
    private $helper;

    /**
     * @var \Magento\Framework\Event\Manager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Directory\Model\PriceCurrency | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var \Magento\Directory\Model\Currency | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyMock;

    /**
     * @var Order|MockObject
     */
    private $order;

    /**
     * @var AbstractMethod|MockObject
     */
    private $paymentMethod;

    /**
     * @var Invoice|MockObject
     */
    private $invoice;

    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\CreditmemoFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoFactoryMock;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditMemoMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Repository | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionRepositoryMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionManagerMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface | \PHPUnit_Framework_MockObject_MockObject
     */

    protected $transactionBuilderMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentProcessor;

    /**
     * @var \Magento\Sales\Model\OrderRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepository;

    /**
     * @var CreditmemoManagementInterface
     */
    private $creditmemoManagerMock;

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.TooManyFields)
     */
    protected function setUp()
    {
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->atLeastOnce())
            ->method('getEventDispatcher')
            ->will($this->returnValue($this->eventManagerMock));

        $this->helper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethodInstance'])
            ->getMock();

        $this->priceCurrencyMock = $this->getMockBuilder(\Magento\Directory\Model\PriceCurrency::class)
            ->disableOriginalConstructor()
            ->setMethods(['format'])
            ->getMock();
        $this->currencyMock = $this->getMockBuilder(\Magento\Directory\Model\Currency::class)
            ->disableOriginalConstructor()
            ->setMethods(['formatTxt'])
            ->getMock();
        $transaction = \Magento\Sales\Model\Order\Payment\Transaction\Repository::class;
        $this->transactionRepositoryMock = $this->getMockBuilder($transaction)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'getByTransactionType', 'getByTransactionId'])
            ->getMock();
        $this->paymentProcessor = $this->createMock(\Magento\Sales\Model\Order\Payment\Processor::class);
        $this->orderRepository = $this->createPartialMock(\Magento\Sales\Model\OrderRepository::class, ['get']);

        $this->priceCurrencyMock->expects($this->any())
            ->method('format')
            ->willReturnCallback(
                function ($value) {
                    return $value;
                }
            );

        $this->paymentMethod = $this->getMockBuilder(AbstractMethod::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'canVoid',
                    'authorize',
                    'getConfigData',
                    'getConfigPaymentAction',
                    'validate',
                    'setStore',
                    'acceptPayment',
                    'denyPayment',
                    'fetchTransactionInfo',
                    'canCapture',
                    'canRefund',
                    'canOrder',
                    'order',
                    'isInitializeNeeded',
                    'initialize',
                    'refund'
                ]
            )
            ->getMock();

        $this->invoice = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getTransactionId',
                    'load',
                    'getId',
                    'pay',
                    'cancel',
                    'getGrandTotal',
                    'getBaseGrandTotal',
                    'getShippingAmount',
                    'getBaseShippingAmount',
                    'getBaseTotalRefunded',
                    'getItemsCollection',
                    'getOrder',
                    'register',
                    'capture',
                ]
            )
            ->getMock();
        $this->helper->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));

        $this->order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getConfig',
                    'setState',
                    'setStatus',
                    'getStoreId',
                    'getBaseGrandTotal',
                    'getBaseCurrency',
                    'getBaseCurrencyCode',
                    'getTotalDue',
                    'getBaseTotalDue',
                    'getInvoiceCollection',
                    'addRelatedObject',
                    'getState',
                    'getStatus',
                    'addStatusHistoryComment',
                    'registerCancellation',
                    'getCustomerNote',
                    'prepareInvoice',
                    'getPaymentsCollection',
                    'setIsCustomerNotified'
                ]
            )
            ->getMock();

        $this->transactionCollectionFactory = $this->getMockBuilder(
            \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory::class
        )
            ->setMethods(['create'])
            ->getMock();
        $this->creditmemoFactoryMock = $this->createMock(\Magento\Sales\Model\Order\CreditmemoFactory::class);
        $this->transactionManagerMock = $this->createMock(
            \Magento\Sales\Model\Order\Payment\Transaction\Manager::class
        );
        $this->transactionBuilderMock = $this->createMock(
            \Magento\Sales\Model\Order\Payment\Transaction\Builder::class
        );
        $this->orderStateResolver = $this->getMockBuilder(Order\OrderStateResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->creditMemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setPaymentRefundDisallowed',
                    'getItemsCollection',
                    'getItems',
                    'setAutomaticallyCreated',
                    'register',
                    'addComment',
                    'save',
                    'getGrandTotal',
                    'getBaseGrandTotal',
                    'getDoTransaction',
                    'getInvoice',
                    'getOrder',
                    'getPaymentRefundDisallowed'
                ]
            )
            ->getMock();

        $this->creditmemoManagerMock = $this->getMockBuilder(CreditmemoManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->payment = $this->initPayment();
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $helper->setBackwardCompatibleProperty($this->payment, 'orderStateResolver', $this->orderStateResolver);
        $this->payment->setMethod('any');
        $this->payment->setOrder($this->order);
        $this->transactionId = self::TRANSACTION_ID;
    }

    protected function tearDown()
    {
        unset($this->payment);
    }

    public function testCancel()
    {
        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));
        // check fix for partial refunds in Payflow Pro
        $this->paymentMethod->expects($this->once())
            ->method('canVoid')
            ->willReturn(false);

        $this->assertEquals($this->payment, $this->payment->cancel());
    }

    public function testPlace()
    {
        $newOrderStatus = 'new_status';

        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));

        $this->paymentMethod->expects($this->any())
            ->method('getConfigData')
            ->with('order_status', null)
            ->willReturn($newOrderStatus);

        $this->mockGetDefaultStatus(Order::STATE_NEW, $newOrderStatus, ['first', 'second']);
        $this->assertOrderUpdated(Order::STATE_NEW, $newOrderStatus);

        $this->paymentMethod->expects($this->once())
            ->method('getConfigPaymentAction')
            ->willReturn(null);

        $this->mockPlaceEvents();

        $this->assertEquals($this->payment, $this->payment->place());
    }

    public function testPlaceActionOrder()
    {
        $newOrderStatus = 'new_status';
        $customerNote = 'blabla';
        $sum = 10;
        $this->payment->setTransactionId($this->transactionId);
        $this->order->expects($this->any())->method('getTotalDue')->willReturn($sum);
        $this->order->expects($this->any())->method('getBaseTotalDue')->willReturn($sum);
        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));
        $this->paymentMethod->expects($this->once())
            ->method('getConfigPaymentAction')
            ->willReturn(\Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER);
        $this->paymentMethod->expects($this->once())->method('isInitializeNeeded')->willReturn(false);
        $this->paymentMethod->expects($this->any())
            ->method('getConfigData')
            ->with('order_status', null)
            ->willReturn($newOrderStatus);
        $this->mockGetDefaultStatus(Order::STATE_PROCESSING, $newOrderStatus, ['first', 'second']);
        $this->order->expects($this->any())
            ->method('setState')
            ->with(Order::STATE_PROCESSING)
            ->willReturnSelf();
        $this->order->expects($this->any())
            ->method('setStatus')
            ->with($newOrderStatus)
            ->willReturnSelf();
        $this->paymentProcessor->expects($this->once())
            ->method('order')
            ->with($this->payment, $sum)
            ->willReturnSelf();
        $this->mockPlaceEvents();
        $statusHistory = $this->getMockForAbstractClass(
            \Magento\Sales\Api\Data\OrderStatusHistoryInterface::class
        );
        $this->order->expects($this->any())->method('getCustomerNote')->willReturn($customerNote);
        $this->order->expects($this->any())
            ->method('addStatusHistoryComment')
            ->withConsecutive(
                [$customerNote]
            )
            ->willReturn($statusHistory);
        $this->order->expects($this->any())
            ->method('setIsCustomerNotified')
            ->with(true)
            ->willReturn($statusHistory);

        $this->assertEquals($this->payment, $this->payment->place());
    }

    protected function mockPlaceEvents()
    {
        $this->eventManagerMock->expects($this->at(0))
            ->method('dispatch')
            ->with('sales_order_payment_place_start', ['payment' => $this->payment]);
        $this->eventManagerMock->expects($this->at(1))
            ->method('dispatch')
            ->with('sales_order_payment_place_end', ['payment' => $this->payment]);
    }

    public function testPlaceActionAuthorizeInitializeNeeded()
    {
        $newOrderStatus = 'new_status';
        $customerNote = 'blabla';
        $sum = 10;
        $this->order->expects($this->any())->method('getBaseGrandTotal')->willReturn($sum);
        $this->order->expects($this->any())->method('getTotalDue')->willReturn($sum);
        $this->order->expects($this->any())->method('getBaseTotalDue')->willReturn($sum);
        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));
        $this->paymentMethod->expects($this->once())
            ->method('getConfigPaymentAction')
            ->willReturn(\Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE);
        $this->paymentMethod->expects($this->any())
            ->method('getConfigData')
            ->withConsecutive(
                ['order_status'],
                ['payment_action']
            )->willReturn($newOrderStatus);
        $this->paymentMethod->expects($this->once())->method('isInitializeNeeded')->willReturn(true);
        $this->paymentMethod->expects($this->once())->method('initialize');
        $this->mockGetDefaultStatus(Order::STATE_NEW, $newOrderStatus, ['first', 'second']);
        $this->order->expects($this->any())
            ->method('setState')
            ->with(Order::STATE_NEW)
            ->willReturnSelf();
        $this->order->expects($this->any())
            ->method('setStatus')
            ->with($newOrderStatus)
            ->willReturnSelf();
        $this->mockPlaceEvents();
        $statusHistory = $this->getMockForAbstractClass(
            \Magento\Sales\Api\Data\OrderStatusHistoryInterface::class
        );
        $this->order->expects($this->any())->method('getCustomerNote')->willReturn($customerNote);
        $this->order->expects($this->any())
            ->method('addStatusHistoryComment')
            ->withConsecutive(
                [$customerNote],
                [__('Authorized amount of %1', $sum)]
            )
            ->willReturn($statusHistory);
        $this->order->expects($this->any())
            ->method('setIsCustomerNotified')
            ->with(true)
            ->willReturn($statusHistory);
        $this->assertEquals($this->payment, $this->payment->place());
    }

    public function testPlaceActionAuthorizeFraud()
    {
        $newOrderStatus = 'new_status';
        $customerNote = 'blabla';
        $sum = 10;
        $this->order->expects($this->any())->method('getTotalDue')->willReturn($sum);
        $this->order->expects($this->any())->method('getBaseTotalDue')->willReturn($sum);
        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));
        $this->payment->setTransactionId($this->transactionId);
        $this->paymentMethod->expects($this->once())
            ->method('getConfigPaymentAction')
            ->willReturn(\Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE);
        $this->paymentMethod->expects($this->any())
            ->method('getConfigData')
            ->with('order_status', null)
            ->willReturn($newOrderStatus);
        $statusHistory = $this->getMockForAbstractClass(
            \Magento\Sales\Api\Data\OrderStatusHistoryInterface::class
        );
        $this->order->expects($this->any())->method('getCustomerNote')->willReturn($customerNote);
        $this->order->expects($this->any())
            ->method('addStatusHistoryComment')
            ->with($customerNote)
            ->willReturn($statusHistory);
        $this->mockGetDefaultStatus(Order::STATE_PROCESSING, Order::STATUS_FRAUD, ['first', 'second']);
        $this->order->expects($this->any())
            ->method('setState')
            ->with(Order::STATE_PROCESSING)
            ->willReturnSelf();
        $this->order->expects($this->any())
            ->method('setStatus')
            ->withConsecutive(
                [Order::STATUS_FRAUD]
            )->willReturnSelf();
        $this->order->expects($this->atLeastOnce())
            ->method('getStatus')
            ->willReturn(Order::STATUS_FRAUD);
        $this->paymentMethod->expects($this->once())
            ->method('getConfigPaymentAction')
            ->willReturn(null);
        $this->assertEquals($this->payment, $this->payment->place());
        //maybe we don't need write authorised sum when fraud was detected
        $this->assertEquals($sum, $this->payment->getAmountAuthorized());
    }

    public function testPlaceActionAuthorizeCapture()
    {
        $newOrderStatus = 'new_status';
        $customerNote = 'blabla';
        $sum = 10;
        $this->order->expects($this->any())->method('getTotalDue')->willReturn($sum);
        $this->order->expects($this->any())->method('getBaseTotalDue')->willReturn($sum);
        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));
        $this->paymentMethod->expects($this->once())
            ->method('getConfigPaymentAction')
            ->willReturn(\Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE);
        $this->paymentMethod->expects($this->any())
            ->method('getConfigData')
            ->with('order_status', null)
            ->willReturn($newOrderStatus);
        $statusHistory = $this->getMockForAbstractClass(
            \Magento\Sales\Api\Data\OrderStatusHistoryInterface::class
        );
        $this->order->expects($this->any())->method('getCustomerNote')->willReturn($customerNote);
        $this->order->expects($this->any())
            ->method('addStatusHistoryComment')
            ->with($customerNote)
            ->willReturn($statusHistory);
        $this->mockGetDefaultStatus(Order::STATE_PROCESSING, $newOrderStatus, ['first', 'second']);
        $this->order->expects($this->any())
            ->method('setState')
            ->with(Order::STATE_PROCESSING)
            ->willReturnSelf();
        $this->order->expects($this->any())
            ->method('setStatus')
            ->with($newOrderStatus)
            ->willReturnSelf();
        $this->paymentMethod->expects($this->once())
            ->method('getConfigPaymentAction')
            ->willReturn(null);

        $this->assertEquals($this->payment, $this->payment->place());

        $this->assertEquals($sum, $this->payment->getAmountAuthorized());
        $this->assertEquals($sum, $this->payment->getBaseAmountAuthorized());
    }

    /**
     * @param bool $isOnline
     * @param float $amount
     * @dataProvider authorizeDataProvider
     */
    public function testAuthorize($isOnline, $amount)
    {
        $this->paymentProcessor->expects($this->once())
            ->method('authorize')
            ->with($this->payment, $isOnline, $amount)
            ->willReturn($this->payment);
        $this->assertEquals($this->payment, $this->payment->authorize($isOnline, $amount));
    }

    /**
     * Data rpovider for testAuthorize
     * @return array
     */
    public function authorizeDataProvider()
    {
        return [
            [false, 9.99],
            [true, 0.01]
        ];
    }

    public function testAcceptApprovePaymentTrue()
    {
        $baseGrandTotal = 300.00;
        $message = sprintf('Approved the payment online. Transaction ID: "%s"', $this->transactionId);
        $acceptPayment = true;

        $this->payment->setLastTransId($this->transactionId);

        $this->mockInvoice($this->transactionId, 2);
        $this->invoice->setBaseGrandTotal($baseGrandTotal);

        $this->mockResultTrueMethods($this->transactionId, $baseGrandTotal, $message);

        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));

        $this->paymentMethod->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

        $this->paymentMethod->expects($this->once())
            ->method('acceptPayment')
            ->with($this->payment)
            ->willReturn($acceptPayment);

        $this->payment->accept();
        self::assertEquals($baseGrandTotal, $this->payment->getBaseAmountPaidOnline());
    }

    /**
     * @return array
     */
    public function acceptPaymentFalseProvider()
    {
        return [
            'Fraud = 1' => [
                true,
                Order::STATUS_FRAUD
            ],
            'Fraud = 0' => [
                false,
                false
            ],
        ];
    }

    /**
     * @dataProvider acceptPaymentFalseProvider
     * @param bool $isFraudDetected
     * @param bool $status
     */
    public function testAcceptApprovePaymentFalse($isFraudDetected, $status)
    {
        $message = sprintf('There is no need to approve this payment. Transaction ID: "%s"', $this->transactionId);
        $acceptPayment = false;
        $orderState = 'random_state';

        $this->payment->setLastTransId($this->transactionId);
        $this->payment->setData('is_fraud_detected', $isFraudDetected);

        $this->order->expects($this->once())
            ->method('getState')
            ->willReturn($orderState);

        $this->assertOrderUpdated(Order::STATE_PAYMENT_REVIEW, $status, $message);

        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));

        $this->paymentMethod->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

        $this->paymentMethod->expects($this->once())
            ->method('acceptPayment')
            ->with($this->payment)
            ->willReturn($acceptPayment);

        $this->payment->accept();
        $this->assertEquals($this->transactionId, $this->payment->getLastTransId());
    }

    /**
     *
     * @dataProvider acceptPaymentFalseProvider
     * @param bool $isFraudDetected
     */
    public function testAcceptApprovePaymentFalseOrderState($isFraudDetected)
    {
        $message = sprintf('There is no need to approve this payment. Transaction ID: "%s"', $this->transactionId);
        $acceptPayment = false;
        $orderState = Order::STATE_PAYMENT_REVIEW;

        $this->payment->setLastTransId($this->transactionId);
        $this->payment->setData('is_fraud_detected', $isFraudDetected);

        $this->order->expects($this->once())
            ->method('getState')
            ->willReturn($orderState);

        $this->order->expects($this->never())
            ->method('setState');
        $this->order->expects($this->once())
            ->method('addStatusHistoryComment')
            ->with($message);

        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));

        $this->paymentMethod->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

        $this->paymentMethod->expects($this->once())
            ->method('acceptPayment')
            ->with($this->payment)
            ->willReturn($acceptPayment);

        $this->payment->accept();
        $this->assertEquals($this->transactionId, $this->payment->getLastTransId());
    }

    public function testDenyPaymentFalse()
    {
        $denyPayment = true;
        $message = sprintf('Denied the payment online Transaction ID: "%s"', $this->transactionId);

        $this->payment->setLastTransId($this->transactionId);

        $this->mockInvoice($this->transactionId);
        $this->mockResultFalseMethods($message);

        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));

        $this->paymentMethod->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

        $this->paymentMethod->expects($this->once())
            ->method('denyPayment')
            ->with($this->payment)
            ->willReturn($denyPayment);

        $this->payment->deny();
    }

    /**
     * Test offline IPN calls
     */
    public function testDenyPaymentIpn()
    {
        $isOnline = false;
        $message = sprintf('Denied the payment online Transaction ID: "%s"', $this->transactionId);

        $this->payment->setTransactionId($this->transactionId);
        $this->payment->setNotificationResult(true);

        $this->mockInvoice($this->transactionId);
        $this->mockResultFalseMethods($message);

        $this->helper->expects($this->never())
            ->method('getMethodInstance');

        $this->payment->deny($isOnline);
    }

    /**
     * @dataProvider acceptPaymentFalseProvider
     * @param bool $isFraudDetected
     * @param bool $status
     */
    public function testDenyPaymentNegative($isFraudDetected, $status)
    {
        $denyPayment = false;
        $message = sprintf('There is no need to deny this payment. Transaction ID: "%s"', $this->transactionId);

        $orderState = 'random_state';

        $this->payment->setLastTransId($this->transactionId);
        $this->payment->setData('is_fraud_detected', $isFraudDetected);

        $this->order->expects($this->once())
            ->method('getState')
            ->willReturn($orderState);

        $this->assertOrderUpdated(Order::STATE_PAYMENT_REVIEW, $status, $message);

        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));

        $this->paymentMethod->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

        $this->paymentMethod->expects($this->once())
            ->method('denyPayment')
            ->with($this->payment)
            ->willReturn($denyPayment);

        $this->payment->deny();
    }

    public function testDenyPaymentNegativeStateReview()
    {
        $denyPayment = false;
        $message = sprintf('There is no need to deny this payment. Transaction ID: "%s"', $this->transactionId);

        $orderState = Order::STATE_PAYMENT_REVIEW;

        $this->payment->setLastTransId($this->transactionId);

        $this->order->expects($this->once())
            ->method('getState')
            ->willReturn($orderState);

        $this->order->expects($this->never())
            ->method('setState');
        $this->order->expects($this->once())
            ->method('addStatusHistoryComment')
            ->with($message);

        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));

        $this->paymentMethod->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

        $this->paymentMethod->expects($this->once())
            ->method('denyPayment')
            ->with($this->payment)
            ->willReturn($denyPayment);

        $this->payment->deny();
    }

    /**
     * Test offline IPN call, negative
     */
    public function testDenyPaymentIpnNegativeStateReview()
    {
        $isOnline = false;
        $message = sprintf('Registered notification about denied payment. Transaction ID: "%s"', $this->transactionId);

        $orderState = Order::STATE_PAYMENT_REVIEW;

        $this->payment->setTransactionId($this->transactionId);
        $this->payment->setNotificationResult(false);

        $this->order->expects($this->once())
            ->method('getState')
            ->willReturn($orderState);

        $this->order->expects($this->never())
            ->method('setState');
        $this->order->expects($this->once())
            ->method('addStatusHistoryComment')
            ->with($message);

        $this->helper->expects($this->never())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));

        $this->paymentMethod->expects($this->never())
            ->method('setStore')
            ->will($this->returnSelf());

        $this->paymentMethod->expects($this->never())
            ->method('denyPayment')
            ->with($this->payment);

        $this->payment->deny($isOnline);
    }

    /**
     * @param int $transactionId
     * @param int $countCall
     */
    private function mockInvoice($transactionId, $countCall = 1)
    {
        $this->invoice->method('getTransactionId')
            ->willReturn($transactionId);
        $this->invoice->method('load')
            ->with($transactionId);
        $this->invoice->method('getId')
            ->willReturn($transactionId);
        $this->order->expects(self::exactly($countCall))
            ->method('getInvoiceCollection')
            ->willReturn([$this->invoice]);
    }

    public function testUpdateOnlineTransactionApproved()
    {
        $message = sprintf('Registered update about approved payment. Transaction ID: "%s"', $this->transactionId);

        $storeId = 50;
        $baseGrandTotal = 299.99;

        $this->payment->setLastTransId($this->transactionId);
        $this->payment->setData('is_transaction_approved', true);

        $this->mockInvoice($this->transactionId, 2);
        $this->invoice->setBaseGrandTotal($baseGrandTotal);
        $this->mockResultTrueMethods($this->transactionId, $baseGrandTotal, $message);

        $this->order->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));
        $this->paymentMethod->expects($this->once())
            ->method('setStore')
            ->with($storeId)
            ->willReturn($this->paymentMethod);
        $this->paymentMethod->expects($this->once())
            ->method('fetchTransactionInfo')
            ->with($this->payment, $this->transactionId);

        $this->payment->update();
        $this->assertEquals($baseGrandTotal, $this->payment->getBaseAmountPaidOnline());
    }

    /**
     * Test update calls from IPN controller
     */
    public function testUpdateOnlineTransactionApprovedIpn()
    {
        $isOnline = false;
        $message = sprintf('Registered update about approved payment. Transaction ID: "%s"', $this->transactionId);

        $baseGrandTotal = 299.99;

        $this->payment->setTransactionId($this->transactionId);
        $this->payment->setData('is_transaction_approved', true);

        $this->mockInvoice($this->transactionId, 2);
        $this->invoice->setBaseGrandTotal($baseGrandTotal);
        $this->mockResultTrueMethods($this->transactionId, $baseGrandTotal, $message);

        $this->order->expects(self::never())
            ->method('getStoreId');
        $this->helper->expects(self::never())
            ->method('getMethodInstance');
        $this->paymentMethod->expects(self::never())
            ->method('setStore');
        $this->paymentMethod->expects(self::never())
            ->method('fetchTransactionInfo');

        $this->payment->update($isOnline);
        $this->assertEquals($baseGrandTotal, $this->payment->getBaseAmountPaidOnline());
    }

    public function testUpdateOnlineTransactionDenied()
    {
        $message = sprintf('Registered update about denied payment. Transaction ID: "%s"', $this->transactionId);

        $storeId = 50;

        $this->payment->setLastTransId($this->transactionId);
        $this->payment->setData('is_transaction_denied', true);

        $this->mockInvoice($this->transactionId);
        $this->mockResultFalseMethods($message);

        $this->order->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));
        $this->paymentMethod->expects($this->once())
            ->method('setStore')
            ->with($storeId)
            ->willReturn($this->paymentMethod);
        $this->paymentMethod->expects($this->once())
            ->method('fetchTransactionInfo')
            ->with($this->payment, $this->transactionId);

        $this->payment->update();
    }

    /**
     * @dataProvider acceptPaymentFalseProvider
     * @param bool $isFraudDetected
     * @param bool $status
     */
    public function testUpdateOnlineTransactionDeniedFalse($isFraudDetected, $status)
    {
        $message = sprintf('There is no update for the payment. Transaction ID: "%s"', $this->transactionId);

        $storeId = 50;
        $orderState = 'random_state';

        $this->payment->setLastTransId($this->transactionId);
        $this->payment->setData('is_transaction_denied', false);
        $this->payment->setData('is_transaction_approved', false);
        $this->payment->setData('is_fraud_detected', $isFraudDetected);

        $this->mockInvoice($this->transactionId);

        $this->order->expects($this->once())
            ->method('getState')
            ->willReturn($orderState);

        $this->assertOrderUpdated(Order::STATE_PAYMENT_REVIEW, $status, $message);

        $this->order->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));
        $this->paymentMethod->expects($this->once())
            ->method('setStore')
            ->with($storeId)
            ->willReturn($this->paymentMethod);
        $this->paymentMethod->expects($this->once())
            ->method('fetchTransactionInfo')
            ->with($this->payment, $this->transactionId);

        $this->payment->update();
        $this->assertEquals($this->transactionId, $this->payment->getLastTransId());
    }

    public function testUpdateOnlineTransactionDeniedFalseHistoryComment()
    {
        $message = sprintf('There is no update for the payment. Transaction ID: "%s"', $this->transactionId);

        $storeId = 50;
        $orderState = Order::STATE_PAYMENT_REVIEW;

        $this->payment->setLastTransId($this->transactionId);
        $this->payment->setData('is_transaction_denied', false);
        $this->payment->setData('is_transaction_approved', false);

        $this->mockInvoice($this->transactionId);

        $this->order->expects($this->once())
            ->method('getState')
            ->willReturn($orderState);

        $this->order->expects($this->never())
            ->method('setState');

        $this->order->expects($this->once())
            ->method('addStatusHistoryComment')
            ->with($message);

        $this->order->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));
        $this->paymentMethod->expects($this->once())
            ->method('setStore')
            ->with($storeId)
            ->willReturn($this->paymentMethod);
        $this->paymentMethod->expects($this->once())
            ->method('fetchTransactionInfo')
            ->with($this->payment, $this->transactionId);

        $this->payment->update();
        $this->assertEquals($this->transactionId, $this->payment->getLastTransId());
    }

    /**
     * @param int $transactionId
     * @param float $baseGrandTotal
     * @param string $message
     */
    protected function mockResultTrueMethods($transactionId, $baseGrandTotal, $message)
    {
        $status = 'status';

        $this->invoice->expects($this->once())
            ->method('pay')
            ->willReturn($transactionId);
        $this->invoice->method('getBaseGrandTotal')
            ->willReturn($baseGrandTotal);

        //acceptPayment = true
        $this->order->expects($this->once())
            ->method('addRelatedObject')
            ->with($this->invoice);

        $this->mockGetDefaultStatus(Order::STATE_PROCESSING, $status);
        $this->assertOrderUpdated(Order::STATE_PROCESSING, $status, $message);
    }

    /**
     * @param $message
     */
    protected function mockResultFalseMethods($message)
    {
        $this->invoice->expects($this->once())
            ->method('cancel');
        $this->order->expects($this->once())
            ->method('addRelatedObject')
            ->with($this->invoice);
        $this->order->expects($this->once())
            ->method('registerCancellation')
            ->with($message, false);
    }

    public function testAcceptWithoutInvoiceResultTrue()
    {
        $baseGrandTotal = null;
        $acceptPayment = true;

        $this->payment->setData('transaction_id', $this->transactionId);

        $this->invoice->expects($this->never())
            ->method('pay');

        $this->order->expects($this->any())
            ->method('getInvoiceCollection')
            ->willReturn([]);

        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));

        $this->paymentMethod->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

        $this->paymentMethod->expects($this->once())
            ->method('acceptPayment')
            ->with($this->payment)
            ->willReturn($acceptPayment);

        $status = 'status';
        $this->mockGetDefaultStatus(Order::STATE_PROCESSING, $status);
        $this->assertOrderUpdated(Order::STATE_PROCESSING, $status, __('Approved the payment online.'));

        $this->payment->accept();
        $this->assertEquals($baseGrandTotal, $this->payment->getBaseAmountPaidOnline());
    }

    public function testDenyWithoutInvoiceResultFalse()
    {
        $baseGrandTotal = null;
        $denyPayment = true;

        $this->payment->setData('transaction_id', $this->transactionId);

        $this->invoice->expects($this->never())
            ->method('cancel');

        $this->order->expects($this->any())
            ->method('getInvoiceCollection')
            ->willReturn([]);

        $this->helper->expects($this->once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethod));

        $this->paymentMethod->expects($this->once())
            ->method('setStore')
            ->will($this->returnSelf());

        $this->paymentMethod->expects($this->once())
            ->method('denyPayment')
            ->with($this->payment)
            ->willReturn($denyPayment);

        $status = 'status';
        $this->mockGetDefaultStatus(Order::STATE_PROCESSING, $status);
        $this->assertOrderUpdated(Order::STATE_PROCESSING, $status, __('Denied the payment online'));

        $this->payment->deny();
        $this->assertEquals($baseGrandTotal, $this->payment->getBaseAmountPaidOnline());
    }

    public function testCanCaptureNoAuthorizationTransaction()
    {
        $this->paymentMethod->expects($this->once())
            ->method('canCapture')
            ->willReturn(true);
        $this->assertTrue($this->payment->canCapture());
    }

    public function testCanCaptureCreateTransaction()
    {
        $this->paymentMethod->expects($this->once())
            ->method('canCapture')
            ->willReturn(true);

        $parentTransactionId = 1;
        $paymentId = 22;
        $this->payment->setId($paymentId);
        $this->payment->setParentTransactionId($parentTransactionId);

        $transaction = $this->createMock(\Magento\Sales\Model\Order\Payment\Transaction::class);
        $transaction->expects($this->once())
            ->method('getIsClosed')
            ->willReturn(false);
        $this->transactionManagerMock->expects($this->once())
            ->method('getAuthorizationTransaction')
            ->with($parentTransactionId, $paymentId)
            ->willReturn($transaction);

        $this->assertTrue($this->payment->canCapture());
    }

    public function testCanCaptureAuthorizationTransaction()
    {
        $paymentId = 1;
        $parentTransactionId = 1;
        $this->payment->setParentTransactionId($parentTransactionId);
        $this->payment->setId($paymentId);
        $this->paymentMethod->expects($this->once())
            ->method('canCapture')
            ->willReturn(true);
        $transaction = $this->createMock(\Magento\Sales\Model\Order\Payment\Transaction::class);
        $this->transactionManagerMock->expects($this->once())
            ->method('getAuthorizationTransaction')
            ->with($parentTransactionId, $paymentId)
            ->willReturn($transaction);
        $transaction->expects($this->once())->method('getIsClosed')->willReturn(true);

        $this->transactionRepositoryMock->expects($this->once())
            ->method('getByTransactionType')
            ->with(Transaction::TYPE_ORDER, $paymentId)
            ->willReturn($transaction);

        $this->assertTrue($this->payment->canCapture());
    }

    public function testCannotCapture()
    {
        $this->paymentMethod->expects($this->once())->method('canCapture')->willReturn(false);
        $this->assertFalse($this->payment->canCapture());
    }

    /**
     * Tests pay method and perform assertions for payment amount
     */
    public function testPay()
    {
        $amountPaid = 10;
        $shippingCaptured = 5;

        self::assertNull($this->payment->getAmountPaid());

        $this->mockInvoice(null);
        $this->invoice->setGrandTotal($amountPaid);
        $this->invoice->setBaseGrandTotal($amountPaid);
        $this->invoice->setShippingAmount($shippingCaptured);
        $this->invoice->setBaseShippingAmount($shippingCaptured);

        self::assertSame($this->payment, $this->payment->pay($this->invoice));
        self::assertEquals($amountPaid, $this->payment->getAmountPaid());
        self::assertEquals($amountPaid, $this->payment->getBaseAmountPaid());
        self::assertEquals($shippingCaptured, $this->payment->getShippingCaptured());
        self::assertEquals($shippingCaptured, $this->payment->getBaseShippingCaptured());
    }

    /**
     * Checks if total paid amount correctly calculated for multiple order invoices.
     */
    public function testPayWithMultipleInvoices()
    {
        $this->invoice->setGrandTotal(5);
        $this->invoice->setShippingAmount(5);

        /** @var Invoice|MockObject $invoice */
        $invoice = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods(['getGrandTotal'])
            ->getMock();
        $invoice->setGrandTotal(5);

        $this->order->method('getInvoiceCollection')
            ->willReturn([$this->invoice, $invoice]);

        $this->payment->pay($this->invoice);

        self::assertEquals(10, $this->payment->getAmountPaid());
        self::assertEquals(5, $this->payment->getShippingCaptured());
    }

    public function testGetOrder()
    {
        $payment = $this->initPayment();
        $this->orderRepository->expects($this->once())->method('get')->willReturn($this->order);
        $payment->setParentId(1211);
        $this->assertSame($this->order, $payment->getOrder());
    }

    public function testGetOrderDefault()
    {
        $this->orderRepository->expects($this->never())->method('get');
        $this->assertSame($this->order, $this->payment->getOrder());
    }

    public function testGetOrderNull()
    {
        $payment = $this->initPayment();
        $this->orderRepository->expects($this->never())->method('get');
        $this->assertNull($payment->getOrder());
    }

    public function testCancelInvoice()
    {
        $expects = [
            'amount_paid' => 10,
            'base_amount_paid' => 10,
            'shipping_captured' => 5,
            'base_shipping_captured' => 5,
        ];
        $this->assertNull($this->payment->getData('amount_paid'));
        $this->invoice->expects($this->once())->method('getGrandTotal')->willReturn($expects['amount_paid']);
        $this->invoice->expects($this->once())->method('getBaseGrandTotal')->willReturn(
            $expects['base_amount_paid']
        );
        $this->invoice->expects($this->once())->method('getShippingAmount')->willReturn(
            $expects['shipping_captured']
        );
        $this->invoice->expects($this->once())->method('getBaseShippingAmount')->willReturn(
            $expects['base_shipping_captured']
        );
        $this->eventManagerMock->expects($this->once())->method('dispatch')->with(
            'sales_order_payment_cancel_invoice',
            ['payment' => $this->payment, 'invoice' => $this->invoice]
        );
        $this->assertSame($this->payment, $this->payment->cancelInvoice($this->invoice));
        $this->assertEquals(-1 * $expects['amount_paid'], $this->payment->getData('amount_paid'));
        $this->assertEquals(-1 * $expects['base_amount_paid'], $this->payment->getData('base_amount_paid'));
        $this->assertEquals(-1 * $expects['shipping_captured'], $this->payment->getData('shipping_captured'));
        $this->assertEquals(
            -1 * $expects['base_shipping_captured'],
            $this->payment->getData('base_shipping_captured')
        );
    }

    public function testRegisterRefundNotificationTransactionExists()
    {
        $amount = 10;
        $paymentId = 1;
        $orderId = 9;
        $this->payment->setParentTransactionId($this->transactionId);
        $this->payment->setId($paymentId);
        $this->order->setId($orderId);
        $transaction = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Payment\Transaction::class);
        $newTransactionId = $this->transactionId . '-' . Transaction::TYPE_REFUND;
        $this->transactionRepositoryMock->expects($this->once())
            ->method('getByTransactionId')
            ->with($this->transactionId)
            ->willReturn($transaction);

        $this->transactionManagerMock->expects($this->once())
            ->method('isTransactionExists')
            ->with($newTransactionId, $paymentId, $orderId)
            ->willReturn(true);

        $this->transactionManagerMock->expects($this->once())
            ->method('generateTransactionId')
            ->with($this->payment, Transaction::TYPE_REFUND, $transaction)
            ->willReturn($newTransactionId);

        $this->assertSame($this->payment, $this->payment->registerRefundNotification($amount));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testRegisterRefundNotification()
    {
        $message = 'Registered notification about refunded amount of . Transaction ID: "' .
            self::TRANSACTION_ID . '-refund"';
        $amount = 50;
        $grandTotalCreditMemo = 50;
        $invoiceBaseGrandTotal = 50;
        $invoiceBaseTotalRefunded = 0;
        $this->invoice->expects($this->any())->method('getBaseGrandTotal')->willReturn($invoiceBaseGrandTotal);
        $this->invoice->expects($this->any())->method('getBaseTotalRefunded')->willReturn(
            $invoiceBaseTotalRefunded
        );
        $this->creditMemoMock->expects($this->any())->method('getGrandTotal')->willReturn($grandTotalCreditMemo);
        $this->payment->setParentTransactionId($this->transactionId);
        $this->mockInvoice($this->transactionId, 1);
        $this->creditmemoFactoryMock->expects($this->once())
            ->method('createByInvoice')
            ->with($this->invoice, ['adjustment_negative' => $invoiceBaseGrandTotal - $amount])
            ->willReturn($this->creditMemoMock);
        $this->creditMemoMock->expects($this->once())->method('setPaymentRefundDisallowed')->willReturnSelf();
        $this->creditMemoMock->expects($this->once())->method('setAutomaticallyCreated')->willReturnSelf();
        $this->creditMemoMock->expects($this->once())->method('addComment')->willReturnSelf();

        $this->creditmemoManagerMock->expects($this->once())
            ->method('refund')
            ->with($this->creditMemoMock, false)
            ->willReturn($this->creditMemoMock);

        $this->order->expects($this->once())->method('getBaseCurrency')->willReturn($this->currencyMock);

        $parentTransaction = $this->createPartialMock(
            \Magento\Sales\Model\Order\Payment\Transaction::class,
            ['setOrderId', 'setPaymentId', 'loadByTxnId', 'getId', 'getTxnId', 'setTxnId', 'getTxnType']
        );
        $newTransactionId = $this->transactionId . '-' . Transaction::TYPE_REFUND;
        $this->transactionRepositoryMock->expects($this->once())
            ->method('getByTransactionId')
            ->with($this->transactionId)
            ->willReturn($parentTransaction);

        $this->transactionManagerMock->expects($this->once())
            ->method('isTransactionExists')
            ->with($newTransactionId)
            ->willReturn(false);
        $this->transactionManagerMock->expects($this->once())
            ->method('generateTransactionId')
            ->with($this->payment, Transaction::TYPE_REFUND, $parentTransaction)
            ->willReturn($newTransactionId);

        $status = 'status';
        $this->mockGetDefaultStatus(Order::STATE_PROCESSING, $status);
        $this->assertOrderUpdated(Order::STATE_PROCESSING, $status, $message);

        $additionalInformation = [];
        $failSafe = false;
        $transactionType = Transaction::TYPE_REFUND;
        $this->getTransactionBuilderMock(
            $additionalInformation,
            $failSafe,
            $transactionType,
            $this->transactionId . '-' . Transaction::TYPE_REFUND
        );

        $this->assertSame($this->payment, $this->payment->registerRefundNotification($amount));
        $this->assertSame($this->creditMemoMock, $this->payment->getData('created_creditmemo'));
        $this->assertEquals($grandTotalCreditMemo, $this->payment->getData('amount_refunded'));
    }

    public function testRegisterRefundNotificationWrongAmount()
    {
        $amount = 30;
        $grandTotalCreditMemo = 50;
        $invoiceBaseGrandTotal = 50;
        $invoiceBaseTotalRefunded = 0;
        $this->invoice->expects($this->any())->method('getBaseGrandTotal')->willReturn($invoiceBaseGrandTotal);
        $this->invoice->expects($this->any())->method('getBaseTotalRefunded')->willReturn(
            $invoiceBaseTotalRefunded
        );
        $this->creditMemoMock->expects($this->any())->method('getGrandTotal')->willReturn($grandTotalCreditMemo);
        $this->payment->setParentTransactionId($this->transactionId);
        $this->mockInvoice($this->transactionId, 1);
        $this->order->expects($this->once())->method('getBaseCurrency')->willReturn($this->currencyMock);
        $parentTransaction = $this->createPartialMock(
            \Magento\Sales\Model\Order\Payment\Transaction::class,
            ['setOrderId', 'setPaymentId', 'loadByTxnId', 'getId', 'getTxnId', 'getTxnType']
        );
        //generate new transaction and check if not exists
        $this->transactionRepositoryMock->expects($this->once())
            ->method('getByTransactionId')
            ->with($this->transactionId)
            ->willReturn($parentTransaction);

        $newTransactionId = $this->transactionId . '-refund';
        $this->transactionManagerMock->expects($this->once())
            ->method('isTransactionExists')
            ->with($newTransactionId)
            ->willReturn(false);

        $this->transactionManagerMock->expects($this->once())
            ->method('generateTransactionId')
            ->with($this->payment, Transaction::TYPE_REFUND, $parentTransaction)
            ->willReturn($newTransactionId);
        $this->assertSame($this->payment, $this->payment->registerRefundNotification($amount));
    }

    /**
     * @dataProvider boolProvider
     */
    public function testCanRefund($canRefund)
    {
        $this->paymentMethod->expects($this->once())
            ->method('canRefund')
            ->willReturn($canRefund);
        $this->assertEquals($canRefund, $this->payment->canRefund());
    }

    /**
     * @covers \Magento\Sales\Model\Order\Payment::refund()
     */
    public function testRefund()
    {
        $amount = 204.04;
        $this->creditMemoMock->expects(static::once())
            ->method('getBaseGrandTotal')
            ->willReturn($amount);
        $this->creditMemoMock->expects(static::once())
            ->method('getGrandTotal')
            ->willReturn($amount);
        $this->creditMemoMock->expects(static::once())
            ->method('getDoTransaction')
            ->willReturn(true);

        $this->paymentMethod->expects(static::once())
            ->method('canRefund')
            ->willReturn(true);

        $this->mockInvoice(self::TRANSACTION_ID, 0);
        $this->creditMemoMock->expects(static::once())
            ->method('getInvoice')
            ->willReturn($this->invoice);
        $this->creditMemoMock->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->order);

        $captureTranId = self::TRANSACTION_ID . '-' . Transaction::TYPE_CAPTURE;
        $captureTransaction = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTxnId'])
            ->getMock();

        $refundTranId = $captureTranId . '-' . Transaction::TYPE_REFUND;
        $this->transactionManagerMock->expects(static::once())
            ->method('generateTransactionId')
            ->willReturn($refundTranId);
        $captureTransaction->expects(static::once())
            ->method('getTxnId')
            ->willReturn($captureTranId);
        $this->transactionRepositoryMock->expects(static::once())
            ->method('getByTransactionId')
            ->willReturn($captureTransaction);

        $this->paymentMethod->expects(static::once())
            ->method('refund')
            ->with($this->payment, $amount);

        $isOnline = true;
        $this->getTransactionBuilderMock([], $isOnline, Transaction::TYPE_REFUND, $refundTranId);

        $this->currencyMock->expects(static::once())
            ->method('formatTxt')
            ->willReturn($amount);
        $this->order->expects(static::once())
            ->method('getBaseCurrency')
            ->willReturn($this->currencyMock);

        $status = 'status';
        $message = 'We refunded ' . $amount . ' online. Transaction ID: "' . $refundTranId . '"';
        $this->orderStateResolver->expects($this->once())->method('getStateForOrder')
            ->with($this->order)
            ->willReturn(Order::STATE_CLOSED);
        $this->mockGetDefaultStatus(Order::STATE_CLOSED, $status);
        $this->assertOrderUpdated(Order::STATE_PROCESSING, $status, $message);

        static::assertSame($this->payment, $this->payment->refund($this->creditMemoMock));
        static::assertEquals($amount, $this->payment->getData('amount_refunded'));
        static::assertEquals($amount, $this->payment->getData('base_amount_refunded_online'));
        static::assertEquals($amount, $this->payment->getData('base_amount_refunded'));
    }

    /**
     * @return array
     */
    public function boolProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @covers \Magento\Sales\Model\Order\Payment::isCaptureFinal()
     * @return void
     */
    public function testIsCaptureFinal()
    {
        $amount = 23.02;
        $partialAmount = 12.00;

        $this->order->expects(static::exactly(2))
            ->method('getBaseTotalDue')
            ->willReturn($amount);

        static::assertFalse($this->payment->isCaptureFinal($partialAmount));
        static::assertTrue($this->payment->isCaptureFinal($amount));
    }

    /**
     * @covers \Magento\Sales\Model\Order\Payment::getShouldCloseParentTransaction()
     * @return void
     */
    public function testGetShouldCloseParentTransaction()
    {
        $this->payment->setShouldCloseParentTransaction(1);
        static::assertTrue($this->payment->getShouldCloseParentTransaction());

        $this->payment->setShouldCloseParentTransaction(0);
        static::assertFalse($this->payment->getShouldCloseParentTransaction());
    }

    /**
     * @return object
     */
    protected function initPayment()
    {
        return (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
            \Magento\Sales\Model\Order\Payment::class,
            [
                'context' => $this->context,
                'creditmemoFactory' => $this->creditmemoFactoryMock,
                'paymentData' => $this->helper,
                'priceCurrency' => $this->priceCurrencyMock,
                'transactionRepository' => $this->transactionRepositoryMock,
                'transactionManager' => $this->transactionManagerMock,
                'transactionBuilder' => $this->transactionBuilderMock,
                'paymentProcessor' => $this->paymentProcessor,
                'orderRepository' => $this->orderRepository,
                'creditmemoManager' => $this->creditmemoManagerMock
            ]
        );
    }

    /**
     * @param $state
     * @param null $status
     * @param null $message
     * @param null $isCustomerNotified
     */
    protected function assertOrderUpdated(
        $state,
        $status = null,
        $message = null,
        $isCustomerNotified = null
    ) {
        $this->order->expects($this->any())
            ->method('setState')
            ->with($state)
            ->willReturnSelf();
        $this->order->expects($this->any())
            ->method('setStatus')
            ->with($status)
            ->willReturnSelf();

        $statusHistory = $this->getMockForAbstractClass(
            \Magento\Sales\Api\Data\OrderStatusHistoryInterface::class
        );
        $this->order->expects($this->any())
            ->method('addStatusHistoryComment')
            ->with($message)
            ->willReturn($statusHistory);
        $this->order->expects($this->any())
            ->method('setIsCustomerNotified')
            ->with($isCustomerNotified)
            ->willReturn($statusHistory);
    }

    /**
     * @param $state
     * @param $status
     * @param array $allStatuses
     */
    protected function mockGetDefaultStatus($state, $status, $allStatuses = [])
    {
        /** @var \Magento\Sales\Model\Order\Config | \PHPUnit_Framework_MockObject_MockObject $orderConfigMock */
        $orderConfigMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStateStatuses', 'getStateDefaultStatus'])
            ->getMock();

        if (!empty($allStatuses)) {
            $orderConfigMock->expects($this->any())
                ->method('getStateStatuses')
                ->with($state)
                ->will($this->returnValue($allStatuses));
        }

        $orderConfigMock->expects($this->any())
            ->method('getStateDefaultStatus')
            ->with($state)
            ->will($this->returnValue($status));

        $this->order->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($orderConfigMock));
    }

    /**
     * @param $transactionId
     * @return MockObject
     */
    protected function getTransactionMock($transactionId)
    {
        $transaction = $this->createPartialMock(\Magento\Sales\Model\Order\Payment\Transaction::class, [
                'getId',
                'setOrderId',
                'setPaymentId',
                'loadByTxnId',
                'setTxnId',
                'getTransactionId',
                'setTxnType',
                'isFailsafe',
                'getTxnId',
                'getHtmlTxnId',
                'getTxnType'
            ]);
        $transaction->expects($this->any())->method('getId')->willReturn($transactionId);
        $transaction->expects($this->any())->method('getTxnId')->willReturn($transactionId);
        $transaction->expects($this->any())->method('getHtmlTxnId')->willReturn($transactionId);
        return $transaction;
    }

    /**
     * @param $additionalInformation
     * @param $failSafe
     * @param $transactionType
     * @param bool $transactionId
     */
    protected function getTransactionBuilderMock(
        $additionalInformation,
        $failSafe,
        $transactionType,
        $transactionId = false
    ) {
        if (!$transactionId) {
            $transactionId = $this->transactionId;
        }
        $this->transactionBuilderMock->expects($this->once())
            ->method('setPayment')
            ->with($this->payment)
            ->willReturnSelf();
        $this->transactionBuilderMock->expects($this->once())
            ->method('setOrder')
            ->with($this->order)
            ->willReturnSelf();
        $this->transactionBuilderMock->expects($this->once())
            ->method('setTransactionId')
            ->with($transactionId)
            ->willReturnSelf();
        $this->transactionBuilderMock->expects($this->once())
            ->method('setAdditionalInformation')
            ->with($additionalInformation)
            ->willReturnSelf();
        $this->transactionBuilderMock->expects($this->once())
            ->method('setFailSafe')
            ->with($failSafe)
            ->willReturnSelf();
        $transaction = $this->getTransactionMock($transactionId);
        $this->transactionBuilderMock->expects($this->once())
            ->method('build')
            ->with($transactionType)
            ->willReturn($transaction);
    }

    /**
     * @return string
     */
    protected function getTransactionIdComment()
    {
        return __(' Transaction ID: "%1"', $this->transactionId);
    }
}
