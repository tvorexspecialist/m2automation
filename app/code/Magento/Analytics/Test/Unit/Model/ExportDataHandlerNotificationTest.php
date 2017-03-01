<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\Connector;
use Magento\Analytics\Model\ExportDataHandlerInterface;
use Magento\Analytics\Model\ExportDataHandlerNotification;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ExportDataHandlerNotificationTest
 */
class ExportDataHandlerNotificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     * @return void
     */
    public function testThatNotifyExecuted()
    {
        $expectedResult = true;
        $notifyCommandName = 'notifyDataChanged';
        $exportDataHandlerMockObject = $this->createExportDataHandlerMock();
        $analyticsConnectorMockObject = $this->createAnalyticsConnectorMock();
        /**
         * @var $exportDataHandlerNotification ExportDataHandlerNotification
         */
        $exportDataHandlerNotification = $this->objectManagerHelper->getObject(
            ExportDataHandlerNotification::class,
            [
                'exportDataHandler' => $exportDataHandlerMockObject,
                'connector' => $analyticsConnectorMockObject,
            ]
        );
        $exportDataHandlerMockObject->expects($this->once())
            ->method('prepareExportData')
            ->willReturn($expectedResult);
        $analyticsConnectorMockObject->expects($this->once())
            ->method('execute')
            ->with($notifyCommandName);
        $this->assertEquals($expectedResult, $exportDataHandlerNotification->prepareExportData());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createExportDataHandlerMock()
    {
        return $this->getMockBuilder(ExportDataHandlerInterface::class)->getMockForAbstractClass();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createAnalyticsConnectorMock()
    {
        return $this->getMockBuilder(Connector::class)->disableOriginalConstructor()->getMock();
    }
}
