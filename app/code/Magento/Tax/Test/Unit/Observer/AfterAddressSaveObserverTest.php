<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Config;
use Magento\Tax\Helper\Data;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AfterAddressSaveObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Module manager
     *
     * @var Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleManagerMock;

    /**
     * Cache config
     *
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheConfigMock;

    /**
     * @var Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxHelperMock;

    /**
     * @var \Magento\Tax\Observer\AfterAddressSaveObserver
     */
    private $session;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerAddress'])
            ->getMock();

        $this->moduleManagerMock = $this->getMockBuilder(\Magento\Framework\Module\Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheConfigMock = $this->getMockBuilder(\Magento\PageCache\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxHelperMock = $this->getMockBuilder(\Magento\Tax\Helper\Data::class)
            ->setMethods([
                'isCatalogPriceDisplayAffectedByTax',
                'setAddressCustomerSessionAddressSave',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->session = $this->objectManager->getObject(
            \Magento\Tax\Observer\AfterAddressSaveObserver::class,
            [
                'taxHelper' => $this->taxHelperMock,
                'moduleManager' => $this->moduleManagerMock,
                'cacheConfig' => $this->cacheConfigMock,
            ]
        );
    }

    /**
     * @test
     * @dataProvider getExecuteDataProvider
     *
     * @param bool $isEnabledPageCache
     * @param bool $isEnabledConfigCache
     * @param bool $isCatalogPriceDisplayAffectedByTax
     * @param bool $isNeedSetAddress
     */
    public function testExecute(
        $isEnabledPageCache,
        $isEnabledConfigCache,
        $isCatalogPriceDisplayAffectedByTax,
        $isNeedSetAddress
    ) {
        $this->moduleManagerMock->expects($this->any())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn($isEnabledPageCache);

        $this->cacheConfigMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn($isEnabledConfigCache);

        $this->taxHelperMock->expects($this->any())
            ->method('isCatalogPriceDisplayAffectedByTax')
            ->willReturn($isCatalogPriceDisplayAffectedByTax);

        /* @var \Magento\Customer\Model\Address|\PHPUnit_Framework_MockObject_MockObject $address */
        $address = $this->getMockBuilder(\Magento\Customer\Model\Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observerMock->expects($this->any())
            ->method('getCustomerAddress')
            ->willReturn($address);

        $this->taxHelperMock->expects($isNeedSetAddress ? $this->once() : $this->never())
            ->method('setAddressCustomerSessionAddressSave')
            ->with($address);

        $this->session->execute($this->observerMock);
    }

    public function getExecuteDataProvider()
    {
        return [
            [false, false, false, false],
            [false, false, true, false],
            [false, true, false, false],
            [false, true, true, false],
            [true, false, false, false],
            [true, false, true, false],
            [true, true, false, false],
            [true, true, true, true],
        ];
    }
}
