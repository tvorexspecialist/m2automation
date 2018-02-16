<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema\DataSavior;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;
use Magento\Setup\Model\Declaration\Schema\Dto\Table;

/**
 * Allows to dump and restore data for specific table
 */
class TableSavior implements DataSaviorInterface
{
    /**
     * @var SelectGeneratorFactory
     */
    private $selectGeneratorFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DumpAccessorInterface
     */
    private $dumpAccessor;

    /**
     * TableDump constructor.
     * @param ResourceConnection $resourceConnection
     * @param SelectGeneratorFactory $selectGeneratorFactory
     * @param DumpAccessorInterface $dumpAccessor
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        SelectGeneratorFactory $selectGeneratorFactory,
        DumpAccessorInterface $dumpAccessor
    ) {
        $this->selectGeneratorFactory = $selectGeneratorFactory;
        $this->resourceConnection = $resourceConnection;
        $this->dumpAccessor = $dumpAccessor;
    }

    /**
     * Prepare select to database
     *
     * @param Table $table
     * @return \Magento\Framework\DB\Select
     */
    private function prepareTableSelect(Table $table)
    {
        $adapter = $this->resourceConnection->getConnection($table->getResource());
        $select = $adapter
            ->select()
            ->setPart('disable_staging_preview', true)
            ->from($table->getName());

        return $select;
    }

    /**
     * @inheritdoc
     * @param Table | ElementInterface $table
     * @return void
     */
    public function dump(ElementInterface $table)
    {
        $connectionName = $table->getResource();
        $select = $this->prepareTableSelect($table);
        $selectGenerator = $this->selectGeneratorFactory->create();
        $resourceSignature = $this->generateDumpFileSignature($table);

        foreach ($selectGenerator->generator($select, $connectionName) as $data) {
            $this->dumpAccessor->save($resourceSignature, $data);
        }
    }

    /**
     * Prepare list of column names
     *
     * @param Table $table
     * @return array
     */
    private function getTableColumnNames(Table $table)
    {
        $columns = [];
        /**
         * Prepare all table fields
         */
        foreach ($table->getColumns() as $column) {
            $columns[] = $column->getName();
        }

        return $columns;
    }

    /**
     * Do Insert to table, that should be restored
     *
     * @param Table $table
     * @param array $data
     */
    private function applyDumpChunk(Table $table, $data)
    {
        $columns = $this->getTableColumnNames($table);
        $adapter = $this->resourceConnection->getConnection($table->getResource());
        $adapter->insertArray($table->getName(), $columns, $data);
    }

    /**
     * @param Table $table
     * @return string
     */
    private function generateDumpFileSignature(Table $table)
    {
        return $table->getName();
    }

    /**
     * @param Table | ElementInterface $table
     * @inheritdoc
     */
    public function restore(ElementInterface $table)
    {
        $file = $this->generateDumpFileSignature($table);
        $generator = $this->dumpAccessor->read($file);

        while ($generator->valid()) {
            $data = $generator->current();
            $this->applyDumpChunk($table, $data);
            $generator->next();
        }

        $this->dumpAccessor->destruct($file);
    }

    /**
     * @inheritdoc
     */
    public function isAcceptable(ElementInterface $element)
    {
        return $element instanceof Table;
    }
}
