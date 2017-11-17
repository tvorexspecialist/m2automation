<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Signifyd\Api\Data\CaseInterface;

/**
 * Creates all required table and keys for Signifyd case
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var string
     */
    private static $table = 'signifyd_case';

    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var AdapterInterface $connection */
        $connection = $setup->startSetup()->getConnection();

        $table = $connection->newTable($setup->getTable(static::$table));
        $table->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
        );
        $table->addColumn('order_id', Table::TYPE_INTEGER, null, ['unsigned' => true]);
        $table->addColumn('case_id', Table::TYPE_INTEGER, null, ['unsigned' => true]);
        $table->addColumn('guarantee_eligible', Table::TYPE_BOOLEAN, null);
        $table->addColumn('guarantee_disposition', Table::TYPE_TEXT, 32);
        $table->addColumn('status', Table::TYPE_TEXT, 32, ['default' => CaseInterface::STATUS_PENDING]);
        $table->addColumn('score', Table::TYPE_INTEGER, null, ['unsigned' => true]);
        $table->addColumn('associated_team', Table::TYPE_INTEGER, null, ['unsigned' => true]);
        $table->addColumn('review_disposition', Table::TYPE_TEXT, 32);
        $table->addColumn('created_at', Table::TYPE_TIMESTAMP);
        $table->addColumn('updated_at', Table::TYPE_TIMESTAMP);

        $table->addIndex(
            $setup->getIdxName(
                $setup->getTable(static::$table),
                'order_id',
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            'order_id',
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        );

        $table->addForeignKey(
            $setup->getFkName(
                $setup->getTable(static::$table),
                'order_id',
                $setup->getTable('sales_order'),
                'entity_id'
            ),
            'order_id',
            $setup->getTable('sales_order'),
            'entity_id',
            Table::ACTION_SET_NULL
        );
        $connection->createTable($table);
    }
}
