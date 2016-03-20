<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Attribute;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Validate;
use Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\AttributeTest;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Framework\Escaper;
use Magento\Framework\View\LayoutInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateTest extends AttributeTest
{
    /**
     * @var ResultJsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonFactoryMock;

    /**
     * @var ResultJson|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJson;

    /**
     * @var LayoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutFactoryMock;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;

    /**
     * @var AttributeSet|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeSetMock;

    /**
     * @var Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    protected function setUp()
    {
        parent::setUp();
        $this->resultJsonFactoryMock = $this->getMockBuilder(ResultJsonFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJson = $this->getMockBuilder(ResultJson::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutFactoryMock = $this->getMockBuilder(LayoutFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeSetMock = $this->getMockBuilder(AttributeSet::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
    }

    /**
     * {@inheritdoc}
     */
    protected function getModel()
    {
        return $this->objectManager->getObject(Validate::class, [
            'context' => $this->contextMock,
            'attributeLabelCache' => $this->attributeLabelCacheMock,
            'coreRegistry' => $this->coreRegistryMock,
            'resultPageFactory' => $this->resultPageFactoryMock,
            'resultJsonFactory' => $this->resultJsonFactoryMock,
            'layoutFactory' => $this->layoutFactoryMock,
        ]);
    }

    public function testExecute()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['frontend_label', null, 'test_frontend_label'],
                ['attribute_code', null, 'test_attribute_code'],
                ['new_attribute_set_name', null, 'test_attribute_set_name'],
            ]);
        $this->objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap([
                ['Magento\Catalog\Model\ResourceModel\Eav\Attribute', [], $this->attributeMock],
                ['Magento\Eav\Model\Entity\Attribute\Set', [], $this->attributeSetMock]
            ]);
        $this->attributeMock->expects($this->once())
            ->method('loadByCode')
            ->willReturnSelf();
        $this->requestMock->expects($this->once())
            ->method('has')
            ->with('new_attribute_set_name')
            ->willReturn(true);
        $this->attributeSetMock->expects($this->once())
            ->method('setEntityTypeId')
            ->willReturnSelf();
        $this->attributeSetMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->attributeSetMock->expects($this->once())
            ->method('getId')
            ->willReturn(false);
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
        $this->resultJson->expects($this->once())
            ->method('setJsonData')
            ->willReturnSelf();

        $this->assertInstanceOf(ResultJson::class, $this->getModel()->execute());
    }
}
