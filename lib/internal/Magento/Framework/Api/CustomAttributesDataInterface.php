<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Interface for entities which can be extended with custom attributes.
 *
 * @api
 * @since 2.0.0
 */
interface CustomAttributesDataInterface extends ExtensibleDataInterface
{
    /**
     * Array key for custom attributes
     */
    const CUSTOM_ATTRIBUTES = 'custom_attributes';

    /**
     * Get an attribute value.
     *
     * @param string $attributeCode
     * @return \Magento\Framework\Api\AttributeInterface|null
     * @since 2.0.0
     */
    public function getCustomAttribute($attributeCode);

    /**
     * Set an attribute value for a given attribute code
     *
     * @param string $attributeCode
     * @param mixed $attributeValue
     * @return $this
     * @since 2.0.0
     */
    public function setCustomAttribute($attributeCode, $attributeValue);

    /**
     * Retrieve custom attributes values.
     *
     * @return \Magento\Framework\Api\AttributeInterface[]|null
     * @since 2.0.0
     */
    public function getCustomAttributes();

    /**
     * Set array of custom attributes
     *
     * @param \Magento\Framework\Api\AttributeInterface[] $attributes
     * @return $this
     * @throws \LogicException
     * @since 2.0.0
     */
    public function setCustomAttributes(array $attributes);
}
