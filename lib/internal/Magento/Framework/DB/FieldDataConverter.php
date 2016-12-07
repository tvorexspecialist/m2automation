<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\DataConverter\DataConverterInterface;

/**
 * Convert field data from one representation to another
 */
class FieldDataConverter
{
    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var Generator
     */
    private $queryGenerator;

    /**
     * @var DataConverterInterface
     */
    private $dataConverter;

    /**
     * Constructor
     *
     * @param AdapterInterface $connection
     * @param Generator $queryGenerator
     * @param DataConverterInterface $dataConverter
     */
    public function __construct(
        AdapterInterface $connection,
        Generator $queryGenerator,
        DataConverterInterface $dataConverter
    ) {
        $this->connection = $connection;
        $this->queryGenerator = $queryGenerator;
        $this->dataConverter = $dataConverter;
    }

    /**
     * Convert field data from one representation to another
     *
     * @param string $table
     * @param string $identifier
     * @param string $field
     * @return void
     */
    public function convert($table, $identifier, $field)
    {
        $select = $this->connection->select()
            ->from($table, [$identifier, $field])
            ->where($field . ' IS NOT NULL');
        $iterator = $this->queryGenerator->generate($identifier, $select);
        foreach ($iterator as $selectByRange) {
            $rows = $this->connection->fetchAll($selectByRange);
            foreach ($rows as $row) {
                $bind = [$field => $this->dataConverter->convert($row[$field])];
                $where = [$identifier . ' = ?' => (int) $row[$identifier]];
                $this->connection->update($table, $bind, $where);
            }
        }
    }
}
