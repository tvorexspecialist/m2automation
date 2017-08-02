<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\Dimension;

/**
 * Interface \Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface
 *
 * @since 2.0.0
 */
interface DataProviderInterface
{
    /**
     * @param BucketInterface $bucket
     * @param Dimension[] $dimensions
     * @param Table $entityIdsTable
     * @return Select
     * @since 2.0.0
     */
    public function getDataSet(
        BucketInterface $bucket,
        array $dimensions,
        Table $entityIdsTable
    );

    /**
     * Executes query and return raw response
     *
     * @param Select $select
     * @return array
     * @since 2.0.0
     */
    public function execute(Select $select);
}
