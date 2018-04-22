<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml\DB;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Responsible for Select object creation, works as a builder. Returns Select as result;
 * Used in SQL assemblers.
 */
class SelectBuilder
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var array
     */
    private $from;

    /**
     * @var array
     */
    private $group = [];

    /**
     * @var array
     */
    private $columns = [];

    /**
     * @var array
     */
    private $filters = [];

    /**
     * @var array
     */
    private $joins = [];

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var array
     */
    private $having = [];

    /**
     * SelectBuilder constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get join condition
     *
     * @return array
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * Set joins conditions
     *
     * @param array $joins
     * @return void
     */
    public function setJoins($joins)
    {
        $this->joins = $joins;
    }

    /**
     * Get connection name
     *
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * Set connection name
     *
     * @param string $connectionName
     * @return void
     */
    public function setConnectionName($connectionName)
    {
        $this->connectionName = $connectionName;
    }

    /**
     * Get columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Set columns
     *
     * @param array $columns
     * @return void
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * Get filters
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Set filters
     *
     * @param array $filters
     * @return void
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    /**
     * Get from condition
     *
     * @return array
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set from condition
     *
     * @param array $from
     * @return void
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * Process JOIN conditions
     *
     * @param Select $select
     * @param array $joinConfig
     * @return Select
     */
    private function processJoin(Select $select, $joinConfig)
    {
        switch ($joinConfig['link-type']) {
            case 'left':
                $select->joinLeft($joinConfig['table'], $joinConfig['condition'], []);
                break;
            case 'inner':
                $select->joinInner($joinConfig['table'], $joinConfig['condition'], []);
                break;
            case 'right':
                $select->joinRight($joinConfig['table'], $joinConfig['condition'], []);
                break;
        }
        return $select;
    }

    /**
     * Creates Select object
     *
     * @return Select
     */
    public function create()
    {
        $connection = $this->resourceConnection->getConnection($this->getConnectionName());
        $select = $connection->select();
        $select->from($this->getFrom(), []);
        $select->columns($this->getColumns());
        foreach ($this->getFilters() as $filter) {
            $select->where($filter);
        }
        foreach ($this->getJoins() as $joinConfig) {
            $select = $this->processJoin($select, $joinConfig);
        }
        if (!empty($this->getGroup())) {
            $select->group(implode(', ', $this->getGroup()));
        }
        return $select;
    }

    /**
     * Returns group
     *
     * @return array
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set group
     *
     * @param array $group
     * @return void
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * Get parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set parameters
     *
     * @param array $params
     * @return void
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Get having condition
     *
     * @return array
     */
    public function getHaving()
    {
        return $this->having;
    }

    /**
     * Set having condition
     *
     * @param array $having
     * @return void
     */
    public function setHaving($having)
    {
        $this->having = $having;
    }
}
