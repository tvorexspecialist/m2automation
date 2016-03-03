<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Design\Config;

use Magento\Theme\Model\Design\Config\ValueProcessor;

class ValueProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Theme\Model\Design\BackendModelFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendModelFactory;

    /** @var \Magento\Framework\App\Config\Value|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendModel;

    /** @var ValueProcessor */
    protected $valueProcessor;

    public function setUp()
    {
        $this->backendModelFactory = $this->getMockBuilder('Magento\Theme\Model\Design\BackendModelFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendModel = $this->getMockBuilder('Magento\Framework\App\Config\Value')
            ->disableOriginalConstructor()
            ->setMethods(['getValue', 'afterLoad'])
            ->getMock();

        $this->valueProcessor = new ValueProcessor($this->backendModelFactory);
    }

    public function testProcess()
    {
        $path = 'design/head/logo';
        $value = 'path/to/logo';

        $this->backendModelFactory->expects($this->once())
            ->method('createByPath')
            ->with($path, ['value' => $value])
            ->willReturn($this->backendModel);
        $this->backendModel->expects($this->once())
            ->method('afterLoad');
        $this->backendModel->expects($this->once())
            ->method('getValue')
            ->willReturn($value);
        $this->assertEquals($value, $this->valueProcessor->process($value, $path));
    }
}
