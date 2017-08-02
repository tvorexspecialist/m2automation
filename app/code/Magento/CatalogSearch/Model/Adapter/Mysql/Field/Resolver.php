<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Mysql\Field;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as AttributeCollection;
use Magento\Framework\Search\Adapter\Mysql\Field\FieldFactory;
use Magento\Framework\Search\Adapter\Mysql\Field\FieldInterface;
use Magento\Framework\Search\Adapter\Mysql\Field\ResolverInterface;

/**
 * Class \Magento\CatalogSearch\Model\Adapter\Mysql\Field\Resolver
 *
 * @since 2.0.0
 */
class Resolver implements ResolverInterface
{
    /**
     * @var AttributeCollection
     * @since 2.0.0
     */
    private $attributeCollection;

    /**
     * @var FieldFactory
     * @since 2.0.0
     */
    private $fieldFactory;

    /**
     * @param AttributeCollection $attributeCollection
     * @param FieldFactory $fieldFactory
     * @since 2.0.0
     */
    public function __construct(
        AttributeCollection $attributeCollection,
        FieldFactory $fieldFactory
    ) {
        $this->attributeCollection = $attributeCollection;
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function resolve(array $fields)
    {
        $resolvedFields = [];
        $this->attributeCollection->addFieldToFilter('attribute_code', ['in' => $fields]);
        foreach ($fields as $field) {
            if ('*' === $field) {
                $resolvedFields = [
                    $this->fieldFactory->create(
                        [
                            'attributeId' => null,
                            'column' => 'data_index',
                            'type' => FieldInterface::TYPE_FULLTEXT
                        ]
                    )
                ];
                break;
            }
            $attribute = $this->attributeCollection->getItemByColumnValue('attribute_code', $field);
            $attributeId = $attribute ? $attribute->getId() : 0;
            $resolvedFields[$field] = $this->fieldFactory->create(
                [
                    'attributeId' => $attributeId,
                    'column' => 'data_index',
                    'type' => FieldInterface::TYPE_FULLTEXT
                ]
            );
        }
        return $resolvedFields;
    }
}
