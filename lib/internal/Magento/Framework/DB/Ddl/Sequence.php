<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Ddl;

/**
 * Class Sequence represents DDL for manage sequences
 */
class Sequence
{
    /**
     *  Default table engine for sequences tables.
     */
    const DEFAULT_ENGINE = 'INNODB';

    /**
     * Return SQL for create sequence
     *
     * @param string $name The name of table in create statement
     * @param int $startNumber The auto increment start number
     * @param string $columnType Type of sequence_value column
     * @param bool|true $unsigned Flag to set sequence_value as UNSIGNED field
     * @param string $dbEngine Database table engine for creating sequence table
     * @return string
     */
    public function getCreateSequenceDdl(
        $name,
        $startNumber = 1,
        $columnType = Table::TYPE_INTEGER,
        $unsigned = true,
        $dbEngine = self::DEFAULT_ENGINE
    ) {
        $format = "CREATE TABLE %s (
                     sequence_value %s %s NOT NULL AUTO_INCREMENT,
                     PRIMARY KEY (sequence_value)
            ) AUTO_INCREMENT = %d ENGINE = %s";

        return sprintf($format, $name, $columnType, $unsigned ? 'UNSIGNED' : '', $startNumber, $dbEngine);
    }

    /**
     * Return SQL for drop sequence
     *
     * @param string $name
     * @return string
     */
    public function dropSequence($name)
    {
        $format = "DROP TABLE %s";
        return sprintf($format, $name);
    }
}
