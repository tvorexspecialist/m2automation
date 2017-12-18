<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto;

/**
 * Structure is aggregation root, which holds all structural elements
 * and allow access to tables by their names
 */
class Structure
{
    /**
     * @var Table[]
     */
    private $tables = [];

    /**
     * Retrieve all tables, that presents in schema
     *
     * @return Table[]
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * Add table by name key to tables registry
     *
     * @param Table $table
     * @return $this
     */
    public function addTable(Table $table)
    {
        $this->tables[$table->getName()] = $table;
        return $this;
    }

    /**
     * Retrieve table by it name
     *
     * @param $name
     * @return bool|Table
     */
    public function getTableByName($name)
    {
        return isset($this->tables[$name]) ? $this->tables[$name] : false;
    }
}
