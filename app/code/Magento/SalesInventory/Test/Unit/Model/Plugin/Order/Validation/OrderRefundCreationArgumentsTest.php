<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesInventory\Test\Unit\Model\Plugin\Order\Validation;

use Magento\SalesInventory\Model\Order\ReturnValidator;
use Magento\SalesInventory\Model\Plugin\Order\Validation\OrderRefundCreationArguments;
use Magento\Sales\Model\Order\Validation\RefundOrderInterface;
use Magento\Sales\Model\ValidatorResultInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class OrderRefundCreationArgumentsTest
 */
class OrderRefundCreationArgumentsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OrderRefundCreationArguments
     */
    private $plugin;

    /**
     * @var ReturnValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $returnValidatorMock;

    /**
     * @var CreditmemoCreationArgumentsExtensionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extencionAttributesMock;

    /**
     * @var CreditmemoCreationArgumentsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoCreationArgumentsMock;

    /**
     * @var RefundOrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $refundOrderValidatorMock;

    /**
     * @var ValidatorResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validateResultMock;

    /**
     * @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var CreditmemoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creditmemoMock;

    protected function setUp()
    {
        $this->returnValidatorMock = $this->getMockBuilder(ReturnValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->creditmemoCreationArgumentsMock = $this->getMockBuilder(CreditmemoCreationArgumentsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->extencionAttributesMock = $this->getMockBuilder(CreditmemoCreationArgumentsExtensionInterface::class)
            ->setMethods(['getReturnToStockItems'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->validateResultMock = $this->getMockBuilder(ValidatorResultInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->refundOrderValidatorMock = $this->getMockBuilder(RefundOrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->creditmemoMock = $this->getMockBuilder(CreditmemoInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new OrderRefundCreationArguments($this->returnValidatorMock);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAfterValidation($erroMessage)
    {
        $returnToStockItems = [1];
        $this->creditmemoCreationArgumentsMock->expects($this->exactly(3))
            ->method('getExtensionAttributes')
            ->willReturn($this->extencionAttributesMock);

        $this->extencionAttributesMock->expects($this->exactly(2))
            ->method('getReturnToStockItems')
            ->willReturn($returnToStockItems);

        $this->returnValidatorMock->expects($this->once())
            ->method('validate')
            ->willReturn($erroMessage);

        $this->validateResultMock->expects($erroMessage ? $this->once() : $this->never())
            ->method('addMessage')
            ->with($erroMessage);

        $this->plugin->afterValidate(
            $this->refundOrderValidatorMock,
            $this->validateResultMock,
            $this->orderMock,
            $this->creditmemoMock,
            [],
            false,
            false,
            null,
            $this->creditmemoCreationArgumentsMock
        );
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'withErrors' => ['Error!'],
            'withoutErrors' => ['null'],
        ];
    }
}
