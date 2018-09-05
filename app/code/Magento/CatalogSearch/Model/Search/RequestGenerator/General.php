<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\RequestGenerator;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\FilterInterface;

/**
 * @deprecated
 * @see ElasticSearch module is default search engine starting from 2.3. CatalogSearch would be removed in 2.4
 */
class General implements GeneratorInterface
{
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
