<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns\Identity;

/**
 * Test for Identity DTO class.
 *
 * @package Magento\Setup\Test\Unit\Model\Declaration\Schema\Db\MySQL\Definition\Columns
 */
class IdentityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Setup\Model\Declaration\Schema\Db\MySQL\Definition\Columns\Comment
     */
    private $identity;

    /**
     * @var \Magento\Setup\Model\Declaration\Schema\Dto\Column|\PHPUnit_Framework_MockObject_MockObject
     */
    private $columnMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->columnMock = $this->getMockBuilder(\Magento\Setup\Model\Declaration\Schema\Dto\Column::class)
            ->disableOriginalConstructor()
            ->setMethods(['isIdentity'])
            ->getMock();
        $this->identity = $this->objectManager->getObject(
            Identity::class
        );
    }

    /**
     * Test conversion to definition.
     */
    public function testToDefinition()
    {
        $this->columnMock->expects($this->any())
            ->method('isIdentity')
            ->willReturn(true);
        $this->assertEquals(
            'AUTO_INCREMENT',
            $this->identity->toDefinition($this->columnMock)
        );
    }

    /**
     * Test conversion to definition.
     */
    public function testToDefinitionFalse()
    {
        $this->columnMock->expects($this->any())
            ->method('isIdentity')
            ->willReturn(false);
        $this->assertEquals(
            '',
            $this->identity->toDefinition($this->columnMock)
        );
    }

    /**
     * Test from definition.
     */
    public function testFromDefinition()
    {
        $data = [
            'extra' => 'NOT NULL AUTO_INCREMENT'
        ];
        $expectedData = $data;
        $expectedData['identity'] = true;
        $this->assertEquals(
            $expectedData,
            $this->identity->fromDefinition($data)
        );
    }
}
