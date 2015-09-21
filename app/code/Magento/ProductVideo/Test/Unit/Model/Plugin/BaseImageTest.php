<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ProductVideo\Test\Unit\Model\Plugin;

class BaseImageTest extends \PHPUnit_Framework_TestCase
{
    /*
     * @var \Magento\Framework\View\Element\Template
     */
    protected $templateMock;

    /*
     * @var \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\BaseImage
     */
    protected $baseImageMock;

    public function setUp()
    {
        $this->templateMock = $this->getMock('\Magento\Framework\View\Element\Template', ['assign'], [], '', false);
        $this->baseImageMock =
            $this->getMock('\Magento\Catalog\Block\Adminhtml\Product\Helper\Form\BaseImage', [], [], '', false);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->pluginObject = $objectManager->getObject(
            '\Magento\ProductVideo\Model\Plugin\BaseImage',
            [

            ]
        );
    }

    public function testAfterAssignBlockVariables()
    {
        $this->templateMock->expects($this->once())->method('assign')->willReturn($this->templateMock);
        $this->pluginObject->afterAssignBlockVariables($this->baseImageMock, $this->templateMock);
    }

    public function testAfterCreateElementHtmlOutputBlock()
    {
        $this->templateMock->expects($this->any())->method('setTemplate')->willReturn(
            'Magento_ProductVideo::product/edit/base_image.phtml'
        );
        $this->pluginObject->afterCreateElementHtmlOutputBlock($this->baseImageMock, $this->templateMock);
    }

}
