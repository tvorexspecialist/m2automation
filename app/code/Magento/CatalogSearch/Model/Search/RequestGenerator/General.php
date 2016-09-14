<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\RequestGenerator;


use Magento\Catalog\Model\Entity\Attribute;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\FilterInterface;

class General implements GeneratorInterface
{

    /**
     * Generate filter data for specific attribute
     * @param Attribute $attribute
     * @param string $filterName
     * @return array
     */
    public function getFilterData(Attribute $attribute, $filterName)
    {
        return [
            'type' => FilterInterface::TYPE_TERM,
            'name' => $filterName,
            'field' => $attribute->getAttributeCode(),
            'value' => '$' . $attribute->getAttributeCode() . '$',
        ];
    }

    /**
     * Generate aggregations data for specific attribute
     * @param Attribute $attribute
     * @param string $bucketName
     * @return array
     */
    public function getAggregationData(Attribute $attribute, $bucketName)
    {
        return [
            'type' => BucketInterface::TYPE_TERM,
            'name' => $bucketName,
            'field' => $attribute->getAttributeCode(),
            'metric' => [['type' => 'count']],
        ];
    }
}
