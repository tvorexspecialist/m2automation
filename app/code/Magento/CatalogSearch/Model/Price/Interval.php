<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Price;

use Magento\Framework\Search\Dynamic\IntervalInterface;

/**
 * Class \Magento\CatalogSearch\Model\Price\Interval
 *
 * @since 2.0.0
 */
class Interval implements IntervalInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
     * @since 2.0.0
     */
    private $resource;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource
     * @since 2.0.0
     */
    public function __construct(\Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource)
    {
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function load($limit, $offset = null, $lower = null, $upper = null)
    {
        $prices = $this->resource->loadPrices($limit, $offset, $lower, $upper);
        return $this->arrayValuesToFloat($prices);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function loadPrevious($data, $index, $lower = null)
    {
        $prices = $this->resource->loadPreviousPrices($data, $index, $lower);
        return $this->arrayValuesToFloat($prices);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function loadNext($data, $rightIndex, $upper = null)
    {
        $prices = $this->resource->loadNextPrices($data, $rightIndex, $upper);
        return $this->arrayValuesToFloat($prices);
    }

    /**
     * @param array $prices
     * @return array
     * @since 2.0.0
     */
    private function arrayValuesToFloat($prices)
    {
        $returnPrices = [];
        if (is_array($prices) && !empty($prices)) {
            $returnPrices = array_map('floatval', $prices);
        }
        return $returnPrices;
    }
}
