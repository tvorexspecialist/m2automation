<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order;

use \Magento\Sales\Model\Order;

/**
 * Test for \Magento\Sales\Model\Order\InvoiceValidator class
 */
class InvoiceValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\InvoiceValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Sales\Model\Order\OrderValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderValidatorMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Sales\Api\Data\InvoiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->orderValidatorMock = $this->getMockBuilder('Magento\Sales\Model\Order\OrderValidatorInterface')
            ->disableOriginalConstructor()
            ->setMethods(['canInvoice'])
            ->getMockForAbstractClass();

        $this->orderMock = $this->getMockBuilder('Magento\Sales\Api\Data\OrderInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getStatus'])
            ->getMockForAbstractClass();

        $this->invoiceMock = $this->getMockBuilder('Magento\Sales\Api\Data\InvoiceInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getTotalQty', 'getItems'])
            ->getMockForAbstractClass();

        $this->model = $this->objectManager->getObject(
            'Magento\Sales\Model\Order\InvoiceValidator',
            ['orderValidator' => $this->orderValidatorMock]
        );
    }

    public function testValidate()
    {
        $expectedResult = [];
        $invoiceItemMock = $this->getInvoiceItemMock(1, 1);
        $this->invoiceMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$invoiceItemMock]);

        $orderItemMock = $this->getOrderItemMock(1, 1, true);
        $this->orderMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$orderItemMock]);
        $this->orderValidatorMock->expects($this->once())
            ->method('canInvoice')
            ->with($this->orderMock)
            ->willReturn(true);
        $this->assertEquals(
            $expectedResult,
            $this->model->validate($this->invoiceMock, $this->orderMock)
        );
    }

    public function testValidateCanNotInvoiceOrder()
    {
        $orderStatus = 'Test Status';
        $expectedResult = [sprintf('The order in status %s does not allow an invoice to be created.', $orderStatus)];
        $invoiceItemMock = $this->getInvoiceItemMock(1, 1);
        $this->invoiceMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$invoiceItemMock]);

        $orderItemMock = $this->getOrderItemMock(1, 1, true);
        $this->orderMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$orderItemMock]);
        $this->orderMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($orderStatus);
        $this->orderValidatorMock->expects($this->once())
            ->method('canInvoice')
            ->with($this->orderMock)
            ->willReturn(false);
        $this->assertEquals(
            $expectedResult,
            $this->model->validate($this->invoiceMock, $this->orderMock)
        );
    }

    public function testValidateInvoiceQtyBiggerThanOrder()
    {
        $orderItemId = 1;
        $message = 'Quantity to invoice must not be greater than uninvoiced quantity for product SKU: %s.';
        $expectedResult = [sprintf($message, $orderItemId)];
        $invoiceItemMock = $this->getInvoiceItemMock($orderItemId, 2);
        $this->invoiceMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$invoiceItemMock]);

        $orderItemMock = $this->getOrderItemMock($orderItemId, 1, false);
        $this->orderMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$orderItemMock]);
        $this->orderValidatorMock->expects($this->once())
            ->method('canInvoice')
            ->with($this->orderMock)
            ->willReturn(true);
        $this->assertEquals(
            $expectedResult,
            $this->model->validate($this->invoiceMock, $this->orderMock)
        );
    }

    public function testValidateNoOrderItems()
    {
        $expectedResult = ['Order does not contain item(s) existed in invoice.'];
        $invoiceItemMock = $this->getInvoiceItemMock(1, 1);
        $this->invoiceMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$invoiceItemMock]);

        $this->orderMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);
        $this->orderValidatorMock->expects($this->once())
            ->method('canInvoice')
            ->with($this->orderMock)
            ->willReturn(true);
        $this->assertEquals(
            $expectedResult,
            $this->model->validate($this->invoiceMock, $this->orderMock)
        );
    }

    public function testValidateNoInvoiceItems()
    {
        $expectedResult = ['You can\'t create an invoice without products.'];
        $orderItemId = 1;
        $invoiceItemMock = $this->getInvoiceItemMock($orderItemId, 0);
        $this->invoiceMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$invoiceItemMock]);

        $orderItemMock = $this->getOrderItemMock($orderItemId, 1, false);
        $this->orderMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$orderItemMock]);
        $this->orderValidatorMock->expects($this->once())
            ->method('canInvoice')
            ->with($this->orderMock)
            ->willReturn(true);
        $this->assertEquals(
            $expectedResult,
            $this->model->validate($this->invoiceMock, $this->orderMock)
        );
    }

    private function getInvoiceItemMock($orderItemId, $qty)
    {
        $invoiceItemMock = $this->getMockBuilder('Magento\Sales\Api\Data\InvoiceItemInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getOrderItemId', 'getQty'])
            ->getMockForAbstractClass();
        $invoiceItemMock->expects($this->once())->method('getOrderItemId')->willReturn($orderItemId);
        $invoiceItemMock->expects($this->once())->method('getQty')->willReturn($qty);
        return $invoiceItemMock;
    }

    private function getOrderItemMock($id, $qtyToInvoice, $isDummy)
    {
        $orderItemMock = $this->getMockBuilder('Magento\Sales\Api\Data\OrderItemInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getQtyToInvoice', 'isDummy', 'getSku'])
            ->getMockForAbstractClass();
        $orderItemMock->expects($this->any())->method('getId')->willReturn($id);
        $orderItemMock->expects($this->any())->method('getQtyToInvoice')->willReturn($qtyToInvoice);
        $orderItemMock->expects($this->any())->method('isDummy')->willReturn($isDummy);
        $orderItemMock->expects($this->any())->method('getSku')->willReturn($id);
        return $orderItemMock;
    }
}
