<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\Db;

/**
 * This class is responsible for read different schema
 * structural elements: indexes, constraints, talbe names and columns
 */
interface DbSchemaWriterInterface
{
    /**
     * Type for all alter statements
     */
    const ALTER_TYPE = 'alter';

    /**
     * Type for all create statements
     */
    const CREATE_TYPE = 'create';

    /**
     * Type for all drop statements
     */
    const DROP_TYPE = 'drop';

    /**
     * Create table from SQL fragments, like columns, constraints, foreign keys, indexes, etc
     *
     * @param $tableName
     * @param $resource
     * @param  array $definition
     * @param array $options
     * @return Statement
     */
    public function createTable($tableName, $resource, array $definition, array $options);

    /**
     * Drop table from SQL database
     *
     * @param string $tableName
     * @param string $resource
     * @return Statement
     */
    public function dropTable($tableName, $resource);

    /**
     * Add generic element to table (table must be specified in elementOptions)
     *
     * Can be: column, constraint, index
     *
     * @param string $elementName
     * @param string $resource
     * @param string $tableName
     * @param string $elementDefinition , for example: like CHAR(200) NOT NULL
     * @param string $elementType
     * @return Statement
     */
    public function addElement($elementName, $resource, $tableName, $elementDefinition, $elementType);

    /**
     * Modify column and change it definition
     *
     * Please note: that from all structural elements only column can be modified
     *
     * @param string $columnName
     * @param string $resource
     * @param string $tableName
     * @param  string $columnDefinition
     * @return Statement
     */
    public function modifyColumn($columnName, $resource, $tableName, $columnDefinition);

    /**
     * Drop any element (constraint, column, index) from index
     *
     * @param string $resource
     * @param string $elementName
     * @param string $tableName
     * @param string $type
     * @return Statement
     */
    public function dropElement($resource, $elementName, $tableName, $type);

    /**
     * Compile statements and make SQL request from them
     *
     * @param StatementAggregator $statementAggregator
     * @return void
     */
    public function compile(StatementAggregator $statementAggregator);
}
