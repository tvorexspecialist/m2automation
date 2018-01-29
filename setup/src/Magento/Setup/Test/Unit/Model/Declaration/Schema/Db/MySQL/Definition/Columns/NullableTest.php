<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns\Nullable;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\Boolean;

class NullableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Nullable
     */
    private $nullable;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->nullable = $this->objectManager->getObject(
            Nullable::class
        );
    }

    /**
     * Test conversion to definition of nullable column.
     */
    public function testToDefinition()
    {
        /** @var Boolean|\PHPUnit_Framework_MockObject_MockObject $column */
        $column = $this->getMockBuilder(Boolean::class)
            ->disableOriginalConstructor()
            ->setMethods(['isNullable'])
            ->getMock();
        $column->expects($this->any())
            ->method('isNullable')
            ->willReturn(true);
        $this->assertEquals(
            'NULL',
            $this->nullable->toDefinition($column)
        );
    }

    /**
     * Test conversion to definition for not nullable column.
     */
    public function testToDefinitionNotNull()
    {
        /** @var Boolean|\PHPUnit_Framework_MockObject_MockObject $column */
        $column = $this->getMockBuilder(Boolean::class)
            ->disableOriginalConstructor()
            ->setMethods(['isNullable'])
            ->getMock();
        $column->expects($this->any())
            ->method('isNullable')
            ->willReturn(false);
        $this->assertEquals(
            'NOT NULL',
            $this->nullable->toDefinition($column)
        );
    }

    /**
     * Test conversion to definition of not nullable aware class.
     */
    public function testToDefinitionNotNullableAware()
    {
        /** @var \Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface|\PHPUnit_Framework_MockObject_MockObject $column */
        $column = $this->getMockBuilder(\Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertEquals(
            '',
            $this->nullable->toDefinition($column)
        );
    }
}
