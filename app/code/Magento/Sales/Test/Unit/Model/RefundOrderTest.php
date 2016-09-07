<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoCommentCreationInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\Order\Creditmemo\CreditmemoValidatorInterface;
use Magento\Sales\Model\Order\CreditmemoDocumentFactory;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\OrderValidatorInterface;
use Magento\Sales\Model\Order\PaymentAdapterInterface;
use Magento\Sales\Model\Order\Creditmemo\NotifierInterface;
use Magento\Sales\Model\RefundOrder;
use Psr\Log\LoggerInterface;

/**
 * Class RefundOrderTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class RefundOrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var CreditmemoDocumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoDocumentFactoryMock;

    /**
     * @var CreditmemoValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoValidatorMock;

    /**
     * @var OrderValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderValidatorMock;

    /**
     * @var PaymentAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentAdapterMock;

    /**
     * @var OrderStateResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderStateResolverMock;

    /**
     * @var OrderConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var Order\CreditmemoRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoRepositoryMock;

    /**
     * @var NotifierInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notifierMock;

    /**
     * @var RefundOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $refundOrder;

    /**
     * @var CreditmemoCreationArgumentsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoCommentCreationMock;

    /**
     * @var CreditmemoCommentCreationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoCreationArgumentsMock;

    /**
     * @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var CreditmemoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoMock;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapterInterface;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    protected function setUp()
    {
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->creditmemoDocumentFactoryMock = $this->getMockBuilder(CreditmemoDocumentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->creditmemoValidatorMock = $this->getMockBuilder(CreditmemoValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderValidatorMock = $this->getMockBuilder(OrderValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentAdapterMock = $this->getMockBuilder(PaymentAdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderStateResolverMock = $this->getMockBuilder(OrderStateResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->configMock = $this->getMockBuilder(OrderConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->creditmemoRepositoryMock = $this->getMockBuilder(CreditmemoRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->notifierMock = $this->getMockBuilder(NotifierInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->creditmemoCommentCreationMock = $this->getMockBuilder(CreditmemoCommentCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->creditmemoCreationArgumentsMock = $this->getMockBuilder(CreditmemoCreationArgumentsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->creditmemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->adapterInterface = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->refundOrder = new RefundOrder(
            $this->resourceConnectionMock,
            $this->orderStateResolverMock,
            $this->orderRepositoryMock,
            $this->orderValidatorMock,
            $this->creditmemoValidatorMock,
            $this->creditmemoRepositoryMock,
            $this->paymentAdapterMock,
            $this->creditmemoDocumentFactoryMock,
            $this->notifierMock,
            $this->configMock,
            $this->loggerMock
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOrderCreditmemo($orderId, $items, $notify, $appendComment)
    {
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with('sales')
            ->willReturn($this->adapterInterface);

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);

        $this->creditmemoDocumentFactoryMock->expects($this->once())
            ->method('createFromOrder')
            ->with(
                $this->orderMock,
                $items,
                $this->creditmemoCommentCreationMock,
                ($appendComment && $notify),
                $this->creditmemoCreationArgumentsMock
            )->willReturn($this->creditmemoMock);

        $this->creditmemoValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->creditmemoMock)
            ->willReturn([]);
        $this->orderValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->orderMock)
            ->willReturn([]);

        $this->paymentAdapterMock->expects($this->once())
            ->method('refund')
            ->with($this->creditmemoMock, $this->orderMock)
            ->willReturn($this->orderMock);

        $this->orderStateResolverMock->expects($this->once())
            ->method('getStateForOrder')
            ->with($this->orderMock, [])
            ->willReturn(Order::STATE_CLOSED);

        $this->orderMock->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_CLOSED)
            ->willReturnSelf();

        $this->orderMock->expects($this->once())
            ->method('getState')
            ->willReturn(Order::STATE_CLOSED);

        $this->configMock->expects($this->once())
            ->method('getStateDefaultStatus')
            ->with(Order::STATE_CLOSED)
            ->willReturn('Closed');

        $this->orderMock->expects($this->once())
            ->method('setStatus')
            ->with('Closed')
            ->willReturnSelf();

        $this->creditmemoMock->expects($this->once())
            ->method('setState')
            ->with(\Magento\Sales\Model\Order\Creditmemo::STATE_REFUNDED)
            ->willReturnSelf();

        $this->creditmemoRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->creditmemoMock)
            ->willReturn($this->creditmemoMock);

        $this->orderRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->orderMock)
            ->willReturn($this->orderMock);

        if ($notify) {
            $this->notifierMock->expects($this->once())
                ->method('notify')
                ->with($this->orderMock, $this->creditmemoMock, $this->creditmemoCommentCreationMock);
        }

        $this->creditmemoMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn(2);

        $this->assertEquals(
            2,
            $this->refundOrder->execute(
                $orderId,
                $items,
                $notify,
                $appendComment,
                $this->creditmemoCommentCreationMock,
                $this->creditmemoCreationArgumentsMock
            )
        );
    }

    /**
     * @expectedException \Magento\Sales\Api\Exception\DocumentValidationExceptionInterface
     */
    public function testDocumentValidationException()
    {
        $orderId = 1;
        $items = [1 => 2];
        $notify = true;
        $appendComment = true;
        $errorMessages = ['error1', 'error2'];

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);

        $this->creditmemoDocumentFactoryMock->expects($this->once())
            ->method('createFromOrder')
            ->with(
                $this->orderMock,
                $items,
                $this->creditmemoCommentCreationMock,
                ($appendComment && $notify),
                $this->creditmemoCreationArgumentsMock
            )->willReturn($this->creditmemoMock);

        $this->creditmemoValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->creditmemoMock)
            ->willReturn($errorMessages);
        $this->orderValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->orderMock)
            ->willReturn([]);

        $this->assertEquals(
            $errorMessages,
            $this->refundOrder->execute(
                $orderId,
                $items,
                $notify,
                $appendComment,
                $this->creditmemoCommentCreationMock,
                $this->creditmemoCreationArgumentsMock
            )
        );
    }

    /**
     * @expectedException \Magento\Sales\Api\Exception\CouldNotRefundExceptionInterface
     */
    public function testCouldNotCreditmemoException()
    {
        $orderId = 1;
        $items = [1 => 2];
        $notify = true;
        $appendComment = true;
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with('sales')
            ->willReturn($this->adapterInterface);

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);

        $this->creditmemoDocumentFactoryMock->expects($this->once())
            ->method('createFromOrder')
            ->with(
                $this->orderMock,
                $items,
                $this->creditmemoCommentCreationMock,
                ($appendComment && $notify),
                $this->creditmemoCreationArgumentsMock
            )->willReturn($this->creditmemoMock);

        $this->creditmemoValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->creditmemoMock)
            ->willReturn([]);
        $this->orderValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->orderMock)
            ->willReturn([]);
        $e = new \Exception();

        $this->paymentAdapterMock->expects($this->once())
            ->method('refund')
            ->with($this->creditmemoMock, $this->orderMock)
            ->willThrowException($e);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($e);

        $this->adapterInterface->expects($this->once())
            ->method('rollBack');

        $this->refundOrder->execute(
            $orderId,
            $items,
            $notify,
            $appendComment,
            $this->creditmemoCommentCreationMock,
            $this->creditmemoCreationArgumentsMock
        );
    }

    public function dataProvider()
    {
        return [
            'TestWithNotifyTrue' => [1, [1 => 2], true, true],
            'TestWithNotifyFalse' => [1, [1 => 2], false, true],
        ];
    }
}
