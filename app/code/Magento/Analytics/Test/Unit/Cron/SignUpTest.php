<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Cron;

use Magento\Analytics\Model\Connector;
use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\AdminNotification\Model\InboxFactory;
use Magento\AdminNotification\Model\ResourceModel\Inbox as InboxResource;
use Magento\Analytics\Model\FlagManager;
use Magento\Analytics\Cron\SignUp;
use Magento\AdminNotification\Model\Inbox;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SignUpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectorMock;

    /**
     * @var WriterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configWriterMock;

    /**
     * @var InboxFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inboxFactoryMock;

    /**
     * @var Inbox|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inboxMock;

    /**
     * @var InboxResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inboxResourceMock;

    /**
     * @var FlagManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $flagManagerMock;

    /**
     * @var ReinitableConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reinitableConfigMock;

    /**
     * @var SignUp
     */
    private $signUp;

    protected function setUp()
    {
        $this->connectorMock =  $this->getMockBuilder(Connector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configWriterMock =  $this->getMockBuilder(WriterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inboxFactoryMock =  $this->getMockBuilder(InboxFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->inboxResourceMock =  $this->getMockBuilder(InboxResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->flagManagerMock =  $this->getMockBuilder(FlagManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inboxMock =  $this->getMockBuilder(Inbox::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->reinitableConfigMock = $this->getMockBuilder(ReinitableConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->signUp = new SignUp(
            $this->connectorMock,
            $this->configWriterMock,
            $this->inboxFactoryMock,
            $this->inboxResourceMock,
            $this->flagManagerMock,
            $this->reinitableConfigMock
        );
    }

    public function testExecute()
    {
        $attemptsCount = 10;

        $this->flagManagerMock->expects($this->once())
            ->method('getFlagData')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE)
            ->willReturn($attemptsCount);

        $attemptsCount -= 1;
        $this->flagManagerMock->expects($this->once())
            ->method('saveFlag')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE, $attemptsCount);
        $this->connectorMock->expects($this->once())
            ->method('execute')
            ->with('signUp')
            ->willReturn(true);
        $this->addDeleteAnalyticsCronExprAsserts();
        $this->flagManagerMock->expects($this->once())
            ->method('deleteFlag')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
        $this->assertTrue($this->signUp->execute());
    }

    public function testExecuteFlagNotExist()
    {
        $this->flagManagerMock->expects($this->once())
            ->method('getFlagData')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE)
            ->willReturn(null);
        $this->addDeleteAnalyticsCronExprAsserts();
        $this->assertFalse($this->signUp->execute());
    }

    public function testExecuteZeroAttempts()
    {
        $attemptsCount = 0;
        $this->flagManagerMock->expects($this->once())
            ->method('getFlagData')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE)
            ->willReturn($attemptsCount);
        $this->addDeleteAnalyticsCronExprAsserts();
        $this->flagManagerMock->expects($this->once())
            ->method('deleteFlag')
            ->with(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE);
        $this->inboxFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->inboxMock);
        $this->inboxMock->expects($this->once())
            ->method('addNotice');
        $this->inboxResourceMock->expects($this->once())
            ->method('save')
            ->with($this->inboxMock);
        $this->assertFalse($this->signUp->execute());
    }

    /**
     * Add assertions for method deleteAnalyticsCronExpr.
     *
     * @return void
     */
    private function addDeleteAnalyticsCronExprAsserts()
    {
        $this->configWriterMock
            ->expects($this->once())
            ->method('delete')
            ->with(SubscriptionHandler::CRON_STRING_PATH)
            ->willReturn(true);
        $this->reinitableConfigMock
            ->expects($this->once())
            ->method('reinit')
            ->willReturnSelf();
    }
}
