<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Payment\Test\Unit\Model;

use Magento\Payment\Model\MethodList;
use Magento\Payment\Model\Method\AbstractMethod;

class MethodListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MethodList
     */
    protected $methodList;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $specificationFactoryMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->paymentHelperMock = $this->getMock(\Magento\Payment\Helper\Data::class, [], [], '', false);
        $this->specificationFactoryMock = $this->getMock(
            \Magento\Payment\Model\Checks\SpecificationFactory::class, [], [], '', false
        );
        $this->methodList = $this->objectManager->getObject(
            \Magento\Payment\Model\MethodList::class,
            [
                'paymentHelper' => $this->paymentHelperMock,
                'specificationFactory' => $this->specificationFactoryMock
            ]
        );
    }

    public function testGetAvailableMethods()
    {
        $storeId = 1;
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);
        $quoteMock->expects($this->once())->method('getStoreId')->will($this->returnValue($storeId));
        $quoteMock->expects($this->atLeastOnce())
            ->method('getPayment')
            ->will($this->returnValue($this->getMock(\Magento\Quote\Model\Quote\Payment::class, [], [], '', false)));

        $methodMock = $this->getMock(\Magento\Payment\Model\Method\AbstractMethod::class, [], [], '', false);

        $compositeMock = $this->getMock(\Magento\Payment\Model\Checks\Composite::class, [], [], '', false);
        $compositeMock->expects($this->atLeastOnce())
            ->method('isApplicable')
            ->with($methodMock, $quoteMock)
            ->will($this->returnValue(true));

        $this->specificationFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->with([
                AbstractMethod::CHECK_USE_CHECKOUT,
                AbstractMethod::CHECK_USE_FOR_COUNTRY,
                AbstractMethod::CHECK_USE_FOR_CURRENCY,
                AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX
            ])
            ->will($this->returnValue($compositeMock));

        $storeMethods = [$methodMock];

        $this->paymentHelperMock->expects($this->once())
            ->method('getStoreMethods')
            ->with($storeId, $quoteMock)
            ->will($this->returnValue($storeMethods));

        $methodMock->expects($this->atLeastOnce())
            ->method('setInfoInstance')
            ->with($this->getMock(\Magento\Quote\Model\Quote\Payment::class, [], [], '', false))
            ->will($this->returnSelf());

        $this->assertEquals([$methodMock], $this->methodList->getAvailableMethods($quoteMock));
    }
}
