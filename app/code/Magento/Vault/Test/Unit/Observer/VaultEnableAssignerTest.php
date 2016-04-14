<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use Magento\Vault\Observer\VaultEnableAssigner;

class VaultEnableAssignerTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteNoActiveCode()
    {
        $dataObject = new DataObject();

        $observer = $this->getPreparedObserverWithMap(
            [
                [AbstractDataAssignObserver::DATA_CODE, $dataObject]
            ]
        );

        $vaultEnableAssigner = new VaultEnableAssigner();

        $vaultEnableAssigner->execute($observer);
    }

    /**
     * @param string $activeCode
     * @param boolean $expectedBool
     * @dataProvider booleanDataProvider
     */
    public function testExecute($activeCode, $expectedBool)
    {
        $dataObject = new DataObject(
            [
                VaultConfigProvider::IS_ACTIVE_CODE => $activeCode
            ]
        );
        $paymentModel = $this->getMock(InfoInterface::class);

        $paymentModel->expects(static::once())
            ->method('setAdditionalInformation')
            ->with(
                VaultConfigProvider::IS_ACTIVE_CODE,
                $expectedBool
            );

        $observer = $this->getPreparedObserverWithMap(
            [
                [AbstractDataAssignObserver::DATA_CODE, $dataObject],
                [AbstractDataAssignObserver::MODEL_CODE, $paymentModel]
            ]
        );

        $vaultEnableAssigner = new VaultEnableAssigner();

        $vaultEnableAssigner->execute($observer);
    }

    /**
     * @return array
     */
    public function booleanDataProvider()
    {
        return [
            ['true', true],
            ['1', true],
            ['on', true],
            ['false', false],
            ['0', false],
            ['off', false]
        ];
    }

    /**
     * @param array $returnMap
     * @return \PHPUnit_Framework_MockObject_MockObject|Observer
     */
    private function getPreparedObserverWithMap(array $returnMap)
    {
        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();

        $observer->expects(static::atLeastOnce())
            ->method('getEvent')
            ->willReturn($event);
        $event->expects(static::atLeastOnce())
            ->method('getDataByKey')
            ->willReturnMap(
                $returnMap
            );

        return $observer;
    }
}
