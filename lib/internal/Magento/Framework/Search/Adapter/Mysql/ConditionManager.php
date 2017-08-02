<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @api
 * @since 2.0.0
 */
class ConditionManager
{
    const CONDITION_PATTERN_SIMPLE = '%s %s %s';
    const CONDITION_PATTERN_ARRAY = '%s %s (%s)';

    /**
     * @var AdapterInterface
     * @since 2.0.0
     */
    private $connection;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @since 2.0.0
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->connection = $resource->getConnection();
    }

    /**
     * @param string $query
     * @return string
     * @since 2.0.0
     */
    public function wrapBrackets($query)
    {
        return empty($query)
            ? $query
            : '(' . $query . ')';
    }

    /**
     * @param string[] $queries
     * @param string $unionOperator
     * @return string
     * @since 2.0.0
     */
    public function combineQueries(array $queries, $unionOperator)
    {
        return implode(
            ' ' . $unionOperator . ' ',
            array_filter($queries, 'strlen')
        );
    }

    /**
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @return string
     * @since 2.0.0
     */
    public function generateCondition($field, $operator, $value)
    {
        return sprintf(
            is_array($value) ? self::CONDITION_PATTERN_ARRAY : self::CONDITION_PATTERN_SIMPLE,
            $this->connection->quoteIdentifier($field),
            $operator,
            $this->connection->quote($value)
        );
    }
}
