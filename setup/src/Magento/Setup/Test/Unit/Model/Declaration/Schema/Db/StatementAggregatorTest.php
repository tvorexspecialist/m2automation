<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Declaration\Schema\Db;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Setup\Model\Declaration\Schema\Db\ReferenceStatement;
use Magento\Setup\Model\Declaration\Schema\Db\Statement;
use Magento\Setup\Model\Declaration\Schema\Db\StatementAggregator;

class StatementAggregatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Setup\Model\Declaration\Schema\Db\StatementAggregator */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp()
    {
        
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = new StatementAggregator();
    }

    public function testAddStatementsInOneBank()
    {
        $statementOne = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementTwo = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementThree = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementsBank = [$statementOne, $statementTwo, $statementThree];
        $statementOne->expects(self::exactly(2))
            ->method('getTableName')
            ->willReturn('first_table');
        $statementTwo->expects(self::exactly(2))
            ->method('getTableName')
            ->willReturn('first_table');
        $statementThree->expects(self::exactly(2))
            ->method('getTableName')
            ->willReturn('first_table');
        $this->model->addStatements($statementsBank);
        self::assertEquals(
            [$statementsBank],
            $this->model->getStatementsBank()
        );
    }

    public function testAddStatementsForDifferentTables()
    {
        $statementOne = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementTwo = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementThree = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementOne->expects(self::exactly(2))
            ->method('getTableName')
            ->willReturn('first_table');
        $statementTwo->expects(self::exactly(1))
            ->method('getTableName')
            ->willReturn('second_table');
        $statementThree->expects(self::exactly(1))
            ->method('getTableName')
            ->willReturn('first_table');
        $this->model->addStatements([$statementOne, $statementTwo, $statementThree]);
        self::assertEquals(
            [[$statementOne, $statementThree], [$statementTwo]],
            $this->model->getStatementsBank()
        );
    }

    public function testAddStatementsForDifferentResources()
    {
        $statementOne = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementTwo = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementThree = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementOne->expects(self::exactly(2))
            ->method('getResource')
            ->willReturn('non_default');
        $this->model->addStatements([$statementOne, $statementTwo, $statementThree]);
        self::assertEquals(
            [[$statementOne], [$statementTwo, $statementThree]],
            $this->model->getStatementsBank()
        );
    }

    public function testAddStatementsWithTriggersInLastStatement()
    {
        $statementOne = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementTwo = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementThree = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementThree->expects(self::exactly(0))
            ->method('getTriggers')
            ->willReturn([function() {}]);
        $this->model->addStatements([$statementOne, $statementTwo, $statementThree]);
        self::assertEquals(
            [[$statementOne, $statementTwo, $statementThree]],
            $this->model->getStatementsBank()
        );
    }

    public function testAddStatementsWithTriggers()
    {
        $statementOne = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementTwo = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementThree = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementOne->expects(self::exactly(2))
            ->method('getTriggers')
            ->willReturn([function() {}]);
        $this->model->addStatements([$statementOne, $statementTwo, $statementThree]);
        self::assertEquals(
            [[$statementOne], [$statementTwo, $statementThree]],
            $this->model->getStatementsBank()
        );
    }

    public function testAddReferenceStatements()
    {
        $statementOne = $this->getMockBuilder(ReferenceStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementTwo = $this->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementThree = $this->getMockBuilder(ReferenceStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statementThree->expects(self::exactly(1))
            ->method('getName')
            ->willReturn('some_foreign_key');
        $statementOne->expects(self::exactly(1))
            ->method('getName')
            ->willReturn('some_foreign_key');
        $this->model->addStatements([$statementOne, $statementTwo, $statementThree]);
        self::assertEquals(
            [[$statementOne, $statementTwo], [$statementThree]],
            $this->model->getStatementsBank()
        );
    }
}
