<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product;

use \Magento\CatalogImportExport\Model\Import\Product;
use \Magento\Framework\Validator\AbstractValidator;

class Validator extends AbstractValidator implements RowValidatorInterface
{
    /**
     * @var RowValidatorInterface[]|AbstractValidator[]
     */
    protected $validators = [];

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $context;

    /**
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * @var array
     */
    protected $_uniqueAttributes;

    /**
     * @var array
     */
    protected $_rowData;

    /**
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param RowValidatorInterface[] $validators
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        $validators = []
    ) {
        $this->string = $string;
        $this->validators = $validators;
    }

    /**
     * @param mixed $attrCode
     * @param string $type
     * @return bool
     */
    protected function textValidation($attrCode, $type)
    {
        $val = $this->string->cleanString($this->_rowData[$attrCode]);
        if ($type == 'text') {
            $valid = $this->string->strlen($val) < Product::DB_MAX_TEXT_LENGTH;
        } else {
            $valid = $this->string->strlen($val) < Product::DB_MAX_VARCHAR_LENGTH;
        }
        if (!$valid) {
            $this->_addMessages([RowValidatorInterface::ERROR_EXCEEDED_MAX_LENGTH]);
        }
        return $valid;
    }

    /**
     * @param mixed $attrCode
     * @param string $type
     * @return bool
     */
    protected function numericValidation($attrCode, $type)
    {
        $val = trim($this->_rowData[$attrCode]);
        if ($type == 'int') {
            $valid = (string)(int)$val === $val;
        } else {
            $valid = is_numeric($val);
        }
        if (!$valid) {
            $this->_addMessages([sprintf($this->context->retrieveMessageTemplate(RowValidatorInterface::ERROR_INVALID_ATTRIBUTE_TYPE), $attrCode, $type)]);
        }
        return $valid;
    }

    /**
     * @param string $attrCode
     * @param array $attrParams
     * @param array $rowData
     * @return bool
     */
    public function isAttributeValid($attrCode, array $attrParams, array $rowData)
    {
        $this->_rowData = $rowData;
        if ($attrParams['is_required']) {
            if (!isset($rowData[$attrCode]) && !strlen(trim($rowData[$attrCode]))) {
                $valid = false;
                $this->_addMessages([RowValidatorInterface::ERROR_VALUE_IS_REQUIRED]);
                return $valid;
            }
        }
        if (!isset($rowData[$attrCode])) {
            return true;
        }
        switch ($attrParams['type']) {
            case 'varchar':
            case 'text':
                $valid = $this->textValidation($attrCode, $attrParams['type']);
                break;
            case 'decimal':
            case 'int':
                $valid = $this->numericValidation($attrCode, $attrParams['type']);
                break;
            case 'select':
            case 'multiselect':
                $valid = isset($attrParams['options'][strtolower($rowData[$attrCode])]);
                if (!$valid) {
                    $this->_addMessages([sprintf($this->context->retrieveMessageTemplate(RowValidatorInterface::ERROR_INVALID_ATTRIBUTE_OPTION), $attrCode)]);
                }
                break;
            case 'datetime':
                $val = trim($rowData[$attrCode]);
                $valid = strtotime($val) !== false;
                if (!$valid) {
                    $this->_addMessages([RowValidatorInterface::ERROR_INVALID_ATTRIBUTE_TYPE]);
                }
                break;
            default:
                $valid = true;
                break;
        }

        if ($valid && !empty($attrParams['is_unique'])) {
            if (isset($this->_uniqueAttributes[$attrCode][$rowData[$attrCode]]) && ($this->_uniqueAttributes[$attrCode][$rowData[$attrCode]] != $rowData[Product::COL_SKU])) {
                $this->_addMessages([RowValidatorInterface::ERROR_DUPLICATE_UNIQUE_ATTRIBUTE]);
                return false;
            }
            $this->_uniqueAttributes[$attrCode][$rowData[$attrCode]] = $rowData[Product::COL_SKU];
        }
        return (bool)$valid;

    }

    /**
     * @return bool
     */
    protected function isValidAttributes()
    {
        $rowData = $this->_rowData;
        $this->_clearMessages();
        $sku = $rowData['sku'];
        $newSku = $this->context->getNewSku($sku);
        $rowData[Product::COL_ATTR_SET] = $newSku['attr_set_code'];
        $productType = $this->context->retrieveProductTypeByName($newSku['type_id']);
        $attributes = $productType->prepareAttributesWithDefaultValueForSave($rowData);
        foreach ($attributes as $attributeCode => $attributeValue) {
            $attrParams = $productType->retrieveAttribute($attributeCode, $newSku['attr_set_code']);
            $this->isAttributeValid($attributeCode, $attrParams, [$attributeCode => $attributeValue]);
        }
        if ($this->getMessages()) {
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($value)
    {
        $this->_rowData = $value;
        $returnValue = true;
        $this->_clearMessages();
        foreach ($this->validators as $validator) {
            if (!$validator->isValid($value)) {
                $returnValue = false;
                $this->_addMessages($validator->getMessages());
            }
        }
        return $returnValue;
    }

    /**
     * @param \Magento\CatalogImportExport\Model\Import\Product $context
     * @return $this
     */
    public function init($context)
    {
        $this->context = $context;
        foreach ($this->validators as $validator) {
            $validator->init($context);
        }
    }
}
