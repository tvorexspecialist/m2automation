<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Test\Unit\Block\Adminhtml\Form\Field;

class ExportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflineShipping\Block\Adminhtml\Form\Field\Export
     */
    protected $_object;

    protected function setUp()
    {
        $backendUrl = $this->getMock(\Magento\Backend\Model\UrlInterface::class, [], [], '', false, false);
        $backendUrl->expects($this->once())->method('getUrl')->with("*/*/exportTablerates", ['website' => 1]);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_object = $objectManager->getObject(
            \Magento\OfflineShipping\Block\Adminhtml\Form\Field\Export::class,
            ['backendUrl' => $backendUrl]
        );
    }

    public function testGetElementHtml()
    {
        $expected = 'some test data';

        $form = $this->getMock(\Magento\Framework\Data\Form::class, ['getParent'], [], '', false, false);
        $parentObjectMock = $this->getMock(
            \Magento\Backend\Block\Template::class,
            ['getLayout'],
            [],
            '',
            false,
            false
        );
        $layoutMock = $this->getMock(\Magento\Framework\View\Layout::class, [], [], '', false, false);

        $blockMock = $this->getMock(\Magento\Backend\Block\Widget\Button::class, [], [], '', false, false);

        $requestMock = $this->getMock(\Magento\Framework\App\RequestInterface::class, [], [], '', false, false);
        $requestMock->expects($this->once())->method('getParam')->with('website')->will($this->returnValue(1));

        $mockData = $this->getMock(\StdClass::class, ['toHtml']);
        $mockData->expects($this->once())->method('toHtml')->will($this->returnValue($expected));

        $blockMock->expects($this->once())->method('getRequest')->will($this->returnValue($requestMock));
        $blockMock->expects($this->any())->method('setData')->will($this->returnValue($mockData));

        $layoutMock->expects($this->once())->method('createBlock')->will($this->returnValue($blockMock));
        $parentObjectMock->expects($this->once())->method('getLayout')->will($this->returnValue($layoutMock));
        $form->expects($this->once())->method('getParent')->will($this->returnValue($parentObjectMock));

        $this->_object->setForm($form);
        $this->assertEquals($expected, $this->_object->getElementHtml());
    }
}
