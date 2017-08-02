<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api;

/**
 * Interface AttributeRepositoryInterface
 * @api
 * @since 2.0.0
 */
interface AttributeRepositoryInterface
{
    /**
     * Retrieve all attributes for entity type
     *
     * @param string $entityTypeCode
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Eav\Api\Data\AttributeSearchResultsInterface
     * @since 2.0.0
     */
    public function getList($entityTypeCode, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Retrieve specific attribute
     *
     * @param string $entityTypeCode
     * @param string $attributeCode
     * @return \Magento\Eav\Api\Data\AttributeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @since 2.0.0
     */
    public function get($entityTypeCode, $attributeCode);

    /**
     * Create attribute data
     *
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @return string
     * @throws \Magento\Framework\Exception\StateException
     * @since 2.0.0
     */
    public function save(\Magento\Eav\Api\Data\AttributeInterface $attribute);

    /**
     * Delete Attribute
     *
     * @param Data\AttributeInterface $attribute
     * @return bool True if the entity was deleted
     * @throws \Magento\Framework\Exception\StateException
     * @since 2.0.0
     */
    public function delete(Data\AttributeInterface $attribute);

    /**
     * Delete Attribute By Id
     *
     * @param int $attributeId
     * @return bool True if the entity was deleted
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     * @since 2.0.0
     */
    public function deleteById($attributeId);
}
