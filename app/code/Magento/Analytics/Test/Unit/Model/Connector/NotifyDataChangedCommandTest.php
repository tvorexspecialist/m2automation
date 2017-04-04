<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\Http\ClientInterface;
use Magento\Analytics\Model\Connector\NotifyDataChangedCommand;
use Magento\Config\Model\Config;
use Magento\Framework\HTTP\ZendClient;
use Psr\Log\LoggerInterface;

class NotifyDataChangedCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NotifyDataChangedCommand
     */
    private $notifyDataChangedCommand;

    /**
     * @var AnalyticsToken|\PHPUnit_Framework_MockObject_MockObject
     */
    private $analyticsTokenMock;

    /**
     * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClientMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    public $configMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    protected function setUp()
    {
        $this->analyticsTokenMock =  $this->getMockBuilder(AnalyticsToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpClientMock =  $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock =  $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock =  $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseMock =  $this->getMockBuilder(\Zend_Http_Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->notifyDataChangedCommand = new NotifyDataChangedCommand(
            $this->analyticsTokenMock,
            $this->httpClientMock,
            $this->configMock,
            $this->loggerMock
        );
    }

    public function testExecuteSuccess()
    {
        $configVal = "Config val";
        $token = "Secret token!";
        $requestJson = sprintf('{"access-token":"%s","url":"%s"}', $token, $configVal);
        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(true);
        $this->configMock->expects($this->any())
            ->method('getConfigDataValue')
            ->willReturn($configVal);
        $this->analyticsTokenMock->expects($this->once())
            ->method('getToken')
            ->willReturn($token);
        $this->httpClientMock->expects($this->once())
            ->method('request')
            ->with(
                ZendClient::POST,
                $configVal,
                $requestJson,
                ['Content-Type: application/json']
            )->willReturn($this->responseMock);
        $this->assertTrue($this->notifyDataChangedCommand->execute());
    }

    public function testExecuteWithoutToken()
    {
        $this->analyticsTokenMock->expects($this->once())
            ->method('isTokenExist')
            ->willReturn(false);
        $this->assertFalse($this->notifyDataChangedCommand->execute());
    }
}
