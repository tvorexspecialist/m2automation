<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Resource helper for specific requests to MySQL DB
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Log\Model\Resource;

class Helper extends \Magento\Framework\DB\Helper
{
    /**
     * Returns information about table in DB
     *
     * @param string $table
     * @return array
     */
    public function getTableInfo($table)
    {
        $connection = $this->getConnection();
        $tableName = $connection->getTableName($table);

        $query = $connection->quoteInto('SHOW TABLE STATUS LIKE ?', $tableName);
        $status = $connection->fetchRow($query);
        if (!$status) {
            return [];
        }

        return [
            'name' => $tableName,
            'rows' => $status['Rows'],
            'data_length' => $status['Data_length'],
            'index_length' => $status['Index_length']
        ];
    }
}
