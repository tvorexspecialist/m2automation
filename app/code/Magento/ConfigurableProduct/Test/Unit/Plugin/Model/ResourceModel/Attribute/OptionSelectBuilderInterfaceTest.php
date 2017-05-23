<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\Model\ResourceModel\Attribute;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilder;
use Magento\ConfigurableProduct\Plugin\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class OptionSelectBuilderInterfaceTest.
 */
class OptionSelectBuilderInterfaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OptionSelectBuilderInterface
     */
    private $model;

    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;
    
    /**
     * @var Status|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockStatusResourceMock;

    /**
     * @var OptionSelectBuilder
     */
    private $optionSelectBuilderMock;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $selectMock;
    
    protected function setUp()
    {
        $this->stockStatusResourceMock = $this->getMockBuilder(Status::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionSelectBuilderMock = $this->getMockBuilder(OptionSelectBuilder::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManager($this);
        $this->model = $this->objectManagerHelper->getObject(
            OptionSelectBuilderInterface::class,
            [
                'stockStatusResource' => $this->stockStatusResourceMock,
            ]
        );
    }

    /**
     * Test for method afterGetSelect.
     */
    public function testAfterGetSelect()
    {
        $this->stockStatusResourceMock->expects($this->once())
            ->method('getMainTable')
            ->willReturn('stock_table_name');

        $this->selectMock->expects($this->once())
            ->method('joinInner')
            ->willReturnSelf();
        $this->selectMock->expects($this->once())
            ->method('where')
            ->willReturnSelf();

        $this->assertEquals(
            $this->selectMock,
            $this->model->afterGetSelect($this->optionSelectBuilderMock, $this->selectMock)
        );
    }
}
