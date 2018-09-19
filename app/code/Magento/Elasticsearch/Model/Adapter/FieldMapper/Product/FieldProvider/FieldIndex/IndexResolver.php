<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;

/**
 * Field index resolver that provide index type for attribute in mapping.
 * For example we need to set 'no'/false in case when attribute must be present in index data,
 * but stay as not indexable.
 */
class IndexResolver implements ResolverInterface
{
    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @param ConverterInterface $converter
     */
    public function __construct(ConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldIndex(AttributeAdapter $attribute)
    {
        $index = null;
        if (!$attribute->isSearchable() && !$attribute->isAlwaysIndexable()) {
            $index = $this->converter->convert(ConverterInterface::INTERNAL_NO_INDEX_VALUE);
        }

        return $index;
    }
}
