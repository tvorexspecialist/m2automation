<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Cron;

use Magento\Analytics\Cron\CollectData;
use Magento\Analytics\Model\ExportDataHandler;
use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class CollectDataTest
 */
class CollectDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExportDataHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $exportDataHandlerMock;

    /**
     * @var SubscriptionStatusProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subscriptionStatusMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var CollectData
     */
    private $collectData;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->exportDataHandlerMock = $this->getMockBuilder(ExportDataHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriptionStatusMock = $this->getMockBuilder(SubscriptionStatusProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->collectData = $this->objectManagerHelper->getObject(
            CollectData::class,
            [
                'exportDataHandler' => $this->exportDataHandlerMock,
                'subscriptionStatus' => $this->subscriptionStatusMock,
            ]
        );
    }

    /**
     * @param string $status
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute($status)
    {
        $this->subscriptionStatusMock
            ->expects($this->once())
            ->method('getStatus')
            ->with()
            ->willReturn($status);
        $this->exportDataHandlerMock
            ->expects(($status === SubscriptionStatusProvider::ENABLED) ? $this->once() : $this->never())
            ->method('prepareExportData')
            ->with();

        $this->assertTrue($this->collectData->execute());
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            'Subscription is enabled' => [SubscriptionStatusProvider::ENABLED],
            'Subscription is disabled' => [SubscriptionStatusProvider::DISABLED],
        ];
    }
}
