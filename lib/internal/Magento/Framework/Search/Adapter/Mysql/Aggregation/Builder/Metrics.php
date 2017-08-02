<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder;

use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;

/**
 * Class \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Metrics
 *
 * @since 2.0.0
 */
class Metrics
{
    /**
     * Available metrics
     *
     * @var string[]
     * @since 2.2.0
     */
    private $allowedMetrics = ['count', 'sum', 'min', 'max', 'avg'];

    /**
     * Build metrics for Select->columns
     *
     * @param RequestBucketInterface $bucket
     * @return string[]
     * @since 2.0.0
     */
    public function build(RequestBucketInterface $bucket)
    {
        $selectAggregations = [];
        /** @var \Magento\Framework\Search\Request\Aggregation\Metric[] $metrics */
        $metrics = $bucket->getMetrics();

        foreach ($metrics as $metric) {
            $metricType = $metric->getType();
            if (in_array($metricType, $this->allowedMetrics, true)) {
                $selectAggregations[$metricType] = "$metricType(main_table.value)";
            }
        }

        return $selectAggregations;
    }
}
