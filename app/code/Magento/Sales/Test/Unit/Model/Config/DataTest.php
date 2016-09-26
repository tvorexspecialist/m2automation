<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Config;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var \Magento\Framework\Json\JsonInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonMock;

    protected function setUp()
    {
        $this->_readerMock = $this->getMockBuilder(
            \Magento\Sales\Model\Config\Reader::class
        )->disableOriginalConstructor()->getMock();
        $this->_cacheMock = $this->getMockBuilder(
            \Magento\Framework\App\Cache\Type\Config::class
        )->disableOriginalConstructor()->getMock();

        $this->jsonMock = $this->getMock(\Magento\Framework\Json\JsonInterface::class);
        \Magento\Sales\Model\Config\Data::setJson($this->jsonMock);
    }

    public function testGet()
    {
        $expected = ['someData' => ['someValue', 'someKey' => 'someValue']];
        $this->_cacheMock->expects($this->once())
            ->method('load');

        $this->jsonMock->method('decode')
            ->willReturn($expected);

        $configData = new \Magento\Sales\Model\Config\Data($this->_readerMock, $this->_cacheMock);

        $this->assertEquals($expected, $configData->get());
    }
}
