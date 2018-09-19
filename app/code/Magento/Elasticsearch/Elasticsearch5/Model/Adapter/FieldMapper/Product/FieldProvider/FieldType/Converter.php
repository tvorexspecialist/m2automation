<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface;

/**
 * Field type converter from internal data types to elastic service.
 */
class Converter implements ConverterInterface
{
    /**#@+
     * Text flags for Elasticsearch field types
     */
    private const ES_DATA_TYPE_TEXT = 'text';
    private const ES_DATA_TYPE_KEYWORD = 'keyword';
    private const ES_DATA_TYPE_FLOAT = 'float';
    private const ES_DATA_TYPE_INT = 'integer';
    private const ES_DATA_TYPE_DATE = 'date';
    /**#@-*/

    /**
     * Mapping between internal data types and elastic service.
     *
     * @var array
     */
    private $mapping = [
        self::INTERNAL_DATA_TYPE_STRING => self::ES_DATA_TYPE_TEXT,
        self::INTERNAL_DATA_TYPE_KEYWORD => self::ES_DATA_TYPE_KEYWORD,
        self::INTERNAL_DATA_TYPE_FLOAT => self::ES_DATA_TYPE_FLOAT,
        self::INTERNAL_DATA_TYPE_INT => self::ES_DATA_TYPE_INT,
        self::INTERNAL_DATA_TYPE_DATE => self::ES_DATA_TYPE_DATE,
    ];

    /**
     * {@inheritdoc}
     */
    public function convert(string $internalType): string
    {
        return $this->mapping[$internalType];
    }
}
