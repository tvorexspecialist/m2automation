<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata;

use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class \Magento\Customer\Model\Metadata\AttributeResolver
 *
 * @since 2.0.0
 */
class AttributeResolver
{
    /**
     * @var AttributeMetadataDataProvider
     * @since 2.0.0
     */
    protected $attributeMetadataDataProvider;

    /**
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @since 2.0.0
     */
    public function __construct(
        AttributeMetadataDataProvider $attributeMetadataDataProvider
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
    }

    /**
     * Get attribute model by attribute data object
     *
     * @param string $entityType
     * @param AttributeMetadataInterface $attribute
     * @return Attribute
     * @throws NoSuchEntityException
     * @since 2.0.0
     */
    public function getModelByAttribute($entityType, AttributeMetadataInterface $attribute)
    {
        /** @var Attribute $model */
        $model = $this->attributeMetadataDataProvider->getAttribute(
            $entityType,
            $attribute->getAttributeCode()
        );
        if ($model) {
            return $model;
        } else {
            throw new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
                    [
                        'fieldName' => 'entityType',
                        'fieldValue' => $entityType,
                        'field2Name' => 'attributeCode',
                        'field2Value' => $attribute->getAttributeCode()
                    ]
                )
            );
        }
    }
}
