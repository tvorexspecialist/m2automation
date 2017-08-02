<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api;

/**
 * Interface AttributeManagementInterface
 * @api
 * @since 2.0.0
 */
interface AttributeManagementInterface
{
    /**
     * Assign attribute to attribute set
     *
     * @param string $entityTypeCode
     * @param int $attributeSetId
     * @param int $attributeGroupId
     * @param string $attributeCode
     * @param int $sortOrder
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 2.0.0
     */
    public function assign($entityTypeCode, $attributeSetId, $attributeGroupId, $attributeCode, $sortOrder);

    /**
     * Remove attribute from attribute set
     *
     * @param string $attributeSetId
     * @param string $attributeCode
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @return bool
     * @since 2.0.0
     */
    public function unassign($attributeSetId, $attributeCode);

    /**
     * Retrieve related attributes based on given attribute set ID
     *
     * @param string $entityTypeCode
     * @param string $attributeSetId
     * @throws \Magento\Framework\Exception\NoSuchEntityException If $attributeSetId is not found
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     * @since 2.0.0
     */
    public function getAttributes($entityTypeCode, $attributeSetId);
}
