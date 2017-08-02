<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\FixtureGenerator;

/**
 * Class provides information about MySQL auto_increment configuration setting.
 * @since 2.2.0
 */
class AutoIncrement
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.2.0
     */
    private $resource;

    /**
     * @var int
     * @since 2.2.0
     */
    private $incrementValue;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @since 2.2.0
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get value of auto_increment_increment variable.
     *
     * @return int
     * @since 2.2.0
     */
    public function getIncrement()
    {
        if ($this->incrementValue === null) {
            $increment = $this->resource->getConnection()->fetchRow('SHOW VARIABLES LIKE "auto_increment_increment"');
            $this->incrementValue = !empty($increment['Value']) ? (int)$increment['Value'] : 1;
        }
        return $this->incrementValue;
    }
}
