<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use \Magento\Framework\DB\Select;

/**
 * Interface for a data map
 */
interface DataMapInterface
{
    /**
     * Gets all data from a map identified by a category Id
     *
     * @param int $categoryId
     * @return array
     */
    public function getAllData($categoryId);

    /**
     * Gets data by criteria from a map identified by a category Id
     *
     * @param int $categoryId
     * @param string $key
     * @return array
     */
    public function getData($categoryId, $key);

    /**
     * Resets current map and it's dependencies
     *
     * @param int $categoryId
     * @return void
     */
    public function resetData($categoryId);
}
