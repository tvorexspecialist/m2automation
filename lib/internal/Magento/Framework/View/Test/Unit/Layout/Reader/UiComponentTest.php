<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\View\Layout\Reader\UiComponent
 */
namespace Magento\Framework\View\Test\Unit\Layout\Reader;

use Magento\Framework\Config\DataInterfaceFactory;
use Magento\Framework\Config\DataInterface;
use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\Reader\UiComponent;
use Magento\Framework\View\Layout\ReaderPool;
use Magento\Framework\View\Layout\ScheduledStructure;

class UiComponentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout\Reader\UiComponent
     */
    protected $model;

    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var DataInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataConfigFactory;

    /**
     * @var DataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataConfig;

    /**
     * @var ReaderPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerPool;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    protected function setUp()
    {
        $this->helper = $this->getMockBuilder(\Magento\Framework\View\Layout\ScheduledStructure\Helper::class)
            ->setMethods(['scheduleStructure'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Layout\Reader\Context::class)
            ->setMethods(['getScheduledStructure', 'setElementToIfconfigList'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataConfigFactory = $this->getMockBuilder(DataInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->dataConfig = $this->getMockBuilder(DataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readerPool = $this->getMockBuilder(ReaderPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new UiComponent($this->helper, $this->dataConfigFactory, $this->readerPool, 'scope');
    }

    public function testGetSupportedNodes()
    {
        $data[] = UiComponent::TYPE_UI_COMPONENT;
        $this->assertEquals($data, $this->model->getSupportedNodes());
    }

    /**
     * @param Element $element
     *
     * @dataProvider interpretDataProvider
     */
    public function testInterpret($element)
    {
        $scheduleStructure = $this->getMockBuilder(ScheduledStructure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())->method('getScheduledStructure')->will(
            $this->returnValue($scheduleStructure)
        );
        $this->helper->expects($this->any())->method('scheduleStructure')->with(
            $scheduleStructure,
            $element,
            $element->getParent()
        )->willReturn($element->getAttribute('name'));

        $scheduleStructure->expects($this->once())->method('setStructureElementData')->with(
            $element->getAttribute('name'),
            ['attributes' => ['group' => '', 'component' => 'listing', 'acl' => 'test', 'condition' => 'test']]
        );
        $scheduleStructure->expects($this->once())->method('setElementToIfconfigList')->with(
            $element->getAttribute('name'),
            'config_path',
            'scope'
        );
        $this->dataConfigFactory->expects($this->once())
            ->method('create')
            ->with(['componentName' => $element->getAttribute('name')])
            ->willReturn($this->dataConfig);
        $xml = '<?xml version="1.0"?>'
            . '<layout xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<block/>'
            . '</layout>';
        $this->dataConfig->expects($this->once())
            ->method('get')
            ->with($element->getAttribute('name'))
            ->willReturn([
                'children' => [
                    'testComponent' => [
                        'arguments' => [
                            'block' => [
                                'layout' => $xml
                            ]
                        ]
                    ]
                ]
            ]);

        $this->readerPool->expects($this->once())
            ->method('interpret')
            ->with($this->context, $this->isInstanceOf(Element::class));

        $this->model->interpret($this->context, $element);
    }

    public function interpretDataProvider()
    {
        return [
            [
                $this->getElement(
                    '<uiComponent
                        name="cms_block_listing"
                        acl="test" condition="test"
                        component="listing"
                        ifconfig="config_path"
                    />',
                    'uiComponent'
                ),
            ]
        ];
    }

    /**
     * @param string $xml
     * @param string $elementType
     * @return Element
     */
    protected function getElement($xml, $elementType)
    {
        $xml = simplexml_load_string(
            '<parent_element>' . $xml . '</parent_element>',
            Element::class
        );
        return $xml->{$elementType};
    }
}
