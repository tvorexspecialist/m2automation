<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Operations;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;
use Magento\Setup\Model\Declaration\Schema\Db\DbSchemaWriterInterface;
use Magento\Setup\Model\Declaration\Schema\Db\DefinitionAggregator;
use Magento\Setup\Model\Declaration\Schema\Db\Statement;
use Magento\Setup\Model\Declaration\Schema\Dto\Column;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\Integer;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Index;
use Magento\Setup\Model\Declaration\Schema\Dto\TableElementInterface;
use Magento\Setup\Model\Declaration\Schema\ElementHistory;
use Magento\Setup\Model\Declaration\Schema\OperationInterface;

/**
 * Add column to table
 */
class AddColumn implements OperationInterface
{
    /**
     * Operation name
     */
    const OPERATION_NAME = 'add_column';

    /**
     * @var DefinitionAggregator
     */
    private $definitionAggregator;

    /**
     * @var DbSchemaWriterInterface
     */
    private $dbSchemaWriter;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param DefinitionAggregator $definitionAggregator
     * @param DbSchemaWriterInterface $dbSchemaWriter
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        DefinitionAggregator $definitionAggregator,
        DbSchemaWriterInterface $dbSchemaWriter,
        ResourceConnection $resourceConnection
    ) {
        $this->definitionAggregator = $definitionAggregator;
        $this->dbSchemaWriter = $dbSchemaWriter;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function getOperationName()
    {
        return self::OPERATION_NAME;
    }

    /**
     * Setup triggers if column have onCreate syntax
     *
     * @param Statement $statement
     * @param Column $column
     * @return array
     */
    private function setupTriggersIfExists(Statement $statement, Column $column)
    {
        $statements = [$statement];
        if (preg_match('/migrateDataFrom\(([^\)]+)\)/', $column->getOnCreate(), $matches)) {
            $isAutoIncrement = $column instanceof Integer && $column->isIdentity();
            if ($isAutoIncrement) {
                $indexStatement = $this->dbSchemaWriter->addElement(
                    'AUTO_INCREMENT_TMP_INDEX',
                    $column->getTable()->getResource(),
                    $column->getTable()->getName(),
                    sprintf('INDEX `AUTO_INCREMENT_TMP_INDEX` (%s)', $column->getName()),
                    Index::TYPE
                );
                array_unshift($statements, $indexStatement);
            }

            $callback = function() use ($column, $matches, $isAutoIncrement) {
                $tableName = $column->getTable()->getName();
                $adapter = $this->resourceConnection->getConnection(
                    $column->getTable()->getResource()
                );
                $adapter->update(
                    $tableName,
                    [
                        $column->getName() => new Expression($matches[1])
                    ]
                );
            };

            $statement->addTrigger($callback);
        }

        return $statements;
    }

    /**
     * @inheritdoc
     */
    public function doOperation(ElementHistory $elementHistory)
    {
        /**
         * @var Column $element
         */
        $element = $elementHistory->getNew();
        $definition = $this->definitionAggregator->toDefinition($element);

        $statement = $this->dbSchemaWriter->addElement(
            $element->getName(),
            $element->getTable()->getResource(),
            $element->getTable()->getName(),
            $definition,
            Column::TYPE
        );
        $statements = $this->setupTriggersIfExists($statement, $element);
        return $statements;
    }
}
