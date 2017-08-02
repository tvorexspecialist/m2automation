<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Catalog product SKU backend attribute model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product;

/**
 * Class \Magento\Catalog\Model\Product\Attribute\Backend\Sku
 *
 * @since 2.0.0
 */
class Sku extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Maximum SKU string length
     *
     * @var string
     */
    const SKU_MAX_LENGTH = 64;

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     * @since 2.0.0
     */
    protected $string;

    /**
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Stdlib\StringUtils $string)
    {
        $this->string = $string;
    }

    /**
     * Validate SKU
     *
     * @param Product $object
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function validate($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);
        if ($this->getAttribute()->getIsRequired() && strlen($value) === 0) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The value of attribute "%1" must be set', $attrCode));
        }

        if ($this->string->strlen($object->getSku()) > self::SKU_MAX_LENGTH) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('SKU length should be %1 characters maximum.', self::SKU_MAX_LENGTH)
            );
        }
        return true;
    }

    /**
     * Generate and set unique SKU to product
     *
     * @param Product $object
     * @return void
     * @since 2.0.0
     */
    protected function _generateUniqueSku($object)
    {
        $attribute = $this->getAttribute();
        $entity = $attribute->getEntity();
        $attributeValue = $object->getData($attribute->getAttributeCode());
        $increment = null;
        while (!$entity->checkAttributeUniqueValue($attribute, $object)) {
            if ($increment === null) {
                $increment = $this->_getLastSimilarAttributeValueIncrement($attribute, $object);
            }
            $sku = trim($attributeValue);
            if (strlen($sku . '-' . ++$increment) > self::SKU_MAX_LENGTH) {
                $sku = substr($sku, 0, -strlen($increment) - 1);
            }
            $sku = $sku . '-' . $increment;
            $object->setData($attribute->getAttributeCode(), $sku);
        }
    }

    /**
     * Make SKU unique before save
     *
     * @param Product $object
     * @return $this
     * @since 2.0.0
     */
    public function beforeSave($object)
    {
        $this->_generateUniqueSku($object);
        return parent::beforeSave($object);
    }

    /**
     * Return increment needed for SKU uniqueness
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param Product $object
     * @return int
     * @since 2.0.0
     */
    protected function _getLastSimilarAttributeValueIncrement($attribute, $object)
    {
        $connection = $this->getAttribute()->getEntity()->getConnection();
        $select = $connection->select();
        $value = $object->getData($attribute->getAttributeCode());
        $bind = ['attribute_code' => trim($value) . '-%'];

        $select->from(
            $this->getTable(),
            $attribute->getAttributeCode()
        )->where(
            $attribute->getAttributeCode() . ' LIKE :attribute_code'
        )->order(
            ['entity_id DESC', $attribute->getAttributeCode() . ' ASC']
        )->limit(
            1
        );
        $data = $connection->fetchOne($select, $bind);
        return abs((int)str_replace($value, '', $data));
    }
}
