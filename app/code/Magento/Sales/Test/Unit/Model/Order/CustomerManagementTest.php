<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Api\Data\OrderAddressInterface;

/**
 * Class BuilderTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectCopyService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $accountManagement;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $regionFactory;

    /**
     * @var \Magento\Sales\Model\Order\CustomerManagement
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteAddressFactory;

    protected function setUp()
    {
        $this->objectCopyService = $this->createMock(\Magento\Framework\DataObject\Copy::class);
        $this->accountManagement = $this->createMock(\Magento\Customer\Api\AccountManagementInterface::class);
        $this->customerFactory = $this->createPartialMock(
            \Magento\Customer\Api\Data\CustomerInterfaceFactory::class,
            ['create']
        );
        $this->addressFactory = $this->createPartialMock(
            \Magento\Customer\Api\Data\AddressInterfaceFactory::class,
            ['create']
        );
        $this->regionFactory = $this->createPartialMock(
            \Magento\Customer\Api\Data\RegionInterfaceFactory::class,
            ['create']
        );
        $this->orderRepository = $this->createMock(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $this->quoteAddressFactory = $this->createMock(\Magento\Quote\Model\Quote\AddressFactory::class);

        $this->service = new \Magento\Sales\Model\Order\CustomerManagement(
            $this->objectCopyService,
            $this->accountManagement,
            $this->customerFactory,
            $this->addressFactory,
            $this->regionFactory,
            $this->orderRepository,
            $this->quoteAddressFactory
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\AlreadyExistsException
     */
    public function testCreateThrowsExceptionIfCustomerAlreadyExists()
    {
        $orderMock = $this->createMock(\Magento\Sales\Api\Data\OrderInterface::class);
        $orderMock->expects($this->once())->method('getCustomerId')->will($this->returnValue('customer_id'));
        $this->orderRepository->expects($this->once())->method('get')->with(1)->will($this->returnValue($orderMock));
        $this->service->create(1);
    }

    public function testCreateCreatesCustomerBasedonGuestOrder()
    {
        $orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $orderMock->expects($this->once())->method('getCustomerId')->will($this->returnValue(null));
        $orderMock->expects($this->any())->method('getBillingAddress')->will($this->returnValue('billing_address'));

        $orderBillingAddress = $this->createPartialMockForAbstractClass(OrderAddressInterface::class, ['getData']);
        $orderBillingAddress->expects($this->once())
            ->method('getAddressType')
            ->willReturn(Address::ADDRESS_TYPE_BILLING);

        $orderShippingAddress = $this->createPartialMockForAbstractClass(OrderAddressInterface::class, ['getData']);
        $orderShippingAddress->expects($this->once())
            ->method('getAddressType')
            ->willReturn(Address::ADDRESS_TYPE_SHIPPING);

        $orderMock->expects($this->any())
            ->method('getAddresses')
            ->will($this->returnValue([$orderBillingAddress, $orderShippingAddress]));

        $billingQuoteAddress = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $billingQuoteAddress->expects($this->once())->method('load')->willReturn($billingQuoteAddress);
        $billingQuoteAddress->expects($this->once())->method('getId')->willReturn(4);
        $billingQuoteAddress->expects($this->once())->method('getData')->with('save_in_address_book')->willReturn(1);

        $shippingQuoteAddress = $this->createMock(\Magento\Quote\Model\Quote\Address::class);
        $shippingQuoteAddress->expects($this->once())->method('load')->willReturn($shippingQuoteAddress);
        $shippingQuoteAddress->expects($this->once())->method('getId')->willReturn(5);
        $shippingQuoteAddress->expects($this->once())->method('getData')->with('save_in_address_book')->willReturn(1);
        $this->quoteAddressFactory->expects($this->exactly(2))->method('create')
            ->willReturnOnConsecutiveCalls($billingQuoteAddress, $shippingQuoteAddress);
        $this->orderRepository->expects($this->once())->method('get')->with(1)->will($this->returnValue($orderMock));
        $this->objectCopyService->expects($this->any())->method('copyFieldsetToTarget')->will($this->returnValueMap(
            [
                ['order_address', 'to_customer', 'billing_address', [], 'global', ['customer_data' => []]],
                ['order_address', 'to_customer_address', $orderBillingAddress, [], 'global', 'address_data'],
                ['order_address', 'to_customer_address', $orderShippingAddress, [], 'global', 'address_data'],
            ]
        ));

        $addressMock = $this->createMock(\Magento\Customer\Api\Data\AddressInterface::class);
        $addressMock->expects($this->any())
            ->method('setIsDefaultBilling')
            ->with(true)
            ->willReturnSelf();
        $addressMock->expects($this->any())
            ->method('setIsDefaultShipping')
            ->with(true)
            ->willReturnSelf();

        $this->addressFactory->expects($this->any())->method('create')->with(['data' => 'address_data'])->will(
            $this->returnValue($addressMock)
        );
        $customerMock = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customerMock->expects($this->any())->method('getId')->will($this->returnValue('customer_id'));
        $this->customerFactory->expects($this->once())->method('create')->with(
            ['data' => ['customer_data' => [], 'addresses' => [$addressMock, $addressMock]]]
        )->will($this->returnValue($customerMock));
        $this->accountManagement->expects($this->once())->method('createAccount')->with($customerMock)->will(
            $this->returnValue($customerMock)
        );
        $orderMock->expects($this->once())->method('setCustomerId')->with('customer_id');
        $this->orderRepository->expects($this->once())->method('save')->with($orderMock);
        $this->assertEquals($customerMock, $this->service->create(1));
    }

    /**
     * Get mock for abstract class with methods.
     *
     * @param string $className
     * @param array $methods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createPartialMockForAbstractClass($className, $methods = [])
    {
        return $this->getMockForAbstractClass(
            $className,
            [],
            '',
            true,
            true,
            true,
            $methods
        );
    }
}
