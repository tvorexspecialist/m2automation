<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface;

/**
 * Float type resolver.
 */
class FloatType implements ResolverInterface
{
    /**
     * @var ConverterInterface
     */
    private $fieldTypeConverter;

    /**
     * @param ConverterInterface $fieldTypeConverter
     */
    public function __construct(ConverterInterface $fieldTypeConverter)
    {
        $this->fieldTypeConverter = $fieldTypeConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldType(AttributeAdapter $attribute): ?string
    {
        if ($attribute->isFloatType()) {
            return $this->fieldTypeConverter->convert(ConverterInterface::INTERNAL_DATA_TYPE_FLOAT);
        }

        return null;
    }
}
