<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Attribute\ConditionBuilder;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Model\Entity\ScopeInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as CatalogEavAttribute;

/**
 * Class \Magento\Catalog\Model\ResourceModel\AttributePersistor
 *
 * @since 2.1.0
 */
class AttributePersistor extends \Magento\Eav\Model\ResourceModel\AttributePersistor
{
    /**
     * @var ConditionBuilder
     * @since 2.2.0
     */
    private $conditionBuilder;

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function __construct(
        FormatInterface $localeFormat,
        AttributeRepositoryInterface $attributeRepository,
        MetadataPool $metadataPool,
        ConditionBuilder $conditionBuilder = null
    ) {
        parent::__construct($localeFormat, $attributeRepository, $metadataPool);
        $this->conditionBuilder = $conditionBuilder ?: ObjectManager::getInstance()->get(ConditionBuilder::class);
    }

    /**
     * @param ScopeInterface $scope
     * @param AbstractAttribute $attribute
     * @param bool $useDefault
     * @return string
     * @since 2.1.0
     */
    protected function getScopeValue(ScopeInterface $scope, AbstractAttribute $attribute, $useDefault = false)
    {
        if ($attribute instanceof CatalogEavAttribute) {
            $useDefault = $useDefault || $attribute->isScopeGlobal();
        }
        return parent::getScopeValue($scope, $attribute, $useDefault);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    protected function buildUpdateConditions(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        array $scopes,
        $linkFieldValue
    ) {
        if ($this->isWebsiteAttribute($attribute)) {
            return $this->conditionBuilder->buildExistingAttributeWebsiteScope(
                $attribute,
                $metadata,
                $scopes,
                $linkFieldValue
            );
        }

        return parent::buildUpdateConditions($attribute, $metadata, $scopes, $linkFieldValue);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    protected function buildInsertConditions(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        array $scopes,
        $linkFieldValue
    ) {
        if ($this->isWebsiteAttribute($attribute)) {
            return $this->conditionBuilder->buildNewAttributesWebsiteScope(
                $attribute,
                $metadata,
                $scopes,
                $linkFieldValue
            );
        }
        return parent::buildInsertConditions($attribute, $metadata, $scopes, $linkFieldValue);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    protected function buildDeleteConditions(
        AbstractAttribute $attribute,
        EntityMetadataInterface $metadata,
        array $scopes,
        $linkFieldValue
    ) {
        if ($this->isWebsiteAttribute($attribute)) {
            return $this->conditionBuilder->buildExistingAttributeWebsiteScope(
                $attribute,
                $metadata,
                $scopes,
                $linkFieldValue
            );
        }

        return parent::buildDeleteConditions($attribute, $metadata, $scopes, $linkFieldValue);
    }

    /**
     * @param AbstractAttribute $attribute
     * @return bool
     * @since 2.2.0
     */
    private function isWebsiteAttribute(AbstractAttribute $attribute)
    {
        return $attribute instanceof CatalogEavAttribute && $attribute->isScopeWebsite();
    }
}
