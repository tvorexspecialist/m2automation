<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Repository implements \Magento\Catalog\Api\ProductAttributeRepositoryInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Attribute
     * @since 2.0.0
     */
    protected $attributeResource;

    /**
     * @var \Magento\Eav\Model\AttributeRepository
     * @since 2.0.0
     */
    protected $eavAttributeRepository;

    /**
     * @var \Magento\Eav\Model\Config
     * @since 2.0.0
     */
    protected $eavConfig;

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory
     * @since 2.0.0
     */
    protected $inputtypeValidatorFactory;

    /**
     * @var \Magento\Catalog\Helper\Product
     * @since 2.0.0
     */
    protected $productHelper;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     * @since 2.0.0
     */
    protected $filterManager;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     * @since 2.0.0
     */
    protected $searchCriteriaBuilder;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $attributeResource
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Attribute $attributeResource,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeResource = $attributeResource;
        $this->productHelper = $productHelper;
        $this->filterManager = $filterManager;
        $this->eavAttributeRepository = $eavAttributeRepository;
        $this->eavConfig = $eavConfig;
        $this->inputtypeValidatorFactory = $validatorFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function get($attributeCode)
    {
        return $this->eavAttributeRepository->get(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode
        );
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        return $this->eavAttributeRepository->getList(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteria
        );
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function save(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        if ($attribute->getAttributeId()) {
            $existingModel = $this->get($attribute->getAttributeCode());

            if (!$existingModel->getAttributeId()) {
                throw NoSuchEntityException::singleField('attribute_code', $existingModel->getAttributeCode());
            }

            // Attribute code must not be changed after attribute creation
            $attribute->setAttributeCode($existingModel->getAttributeCode());
            $attribute->setAttributeId($existingModel->getAttributeId());
            $attribute->setIsUserDefined($existingModel->getIsUserDefined());
            $attribute->setFrontendInput($existingModel->getFrontendInput());

            if (is_array($attribute->getFrontendLabels())) {
                $defaultFrontendLabel = $attribute->getDefaultFrontendLabel();
                $frontendLabel[0] = !empty($defaultFrontendLabel)
                    ? $defaultFrontendLabel
                    : $existingModel->getDefaultFrontendLabel();
                foreach ($attribute->getFrontendLabels() as $item) {
                    $frontendLabel[$item->getStoreId()] = $item->getLabel();
                }
                $attribute->setDefaultFrontendLabel($frontendLabel);
            }
        } else {
            $attribute->setAttributeId(null);

            if (!$attribute->getFrontendLabels() && !$attribute->getDefaultFrontendLabel()) {
                throw InputException::requiredField('frontend_label');
            }

            $frontendLabels = [];
            if ($attribute->getDefaultFrontendLabel()) {
                $frontendLabels[0] = $attribute->getDefaultFrontendLabel();
            }
            if ($attribute->getFrontendLabels() && is_array($attribute->getFrontendLabels())) {
                foreach ($attribute->getFrontendLabels() as $label) {
                    $frontendLabels[$label->getStoreId()] = $label->getLabel();
                }
                if (!isset($frontendLabels[0]) || !$frontendLabels[0]) {
                    throw InputException::invalidFieldValue('frontend_label', null);
                }

                $attribute->setDefaultFrontendLabel($frontendLabels);
            }
            $attribute->setAttributeCode(
                $attribute->getAttributeCode() ?: $this->generateCode($frontendLabels[0])
            );
            $this->validateCode($attribute->getAttributeCode());
            $this->validateFrontendInput($attribute->getFrontendInput());

            $attribute->setBackendType(
                $attribute->getBackendTypeByInput($attribute->getFrontendInput())
            );
            $attribute->setSourceModel(
                $this->productHelper->getAttributeSourceModelByInputType($attribute->getFrontendInput())
            );
            $attribute->setBackendModel(
                $this->productHelper->getAttributeBackendModelByInputType($attribute->getFrontendInput())
            );
            $attribute->setEntityTypeId(
                $this->eavConfig
                    ->getEntityType(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE)
                    ->getId()
            );
            $attribute->setIsUserDefined(1);
        }
        if (!empty($attribute->getData(AttributeInterface::OPTIONS))) {
            $options = [];
            $sortOrder = 0;
            $default = [];
            $optionIndex = 0;
            foreach ($attribute->getOptions() as $option) {
                $optionIndex++;
                $optionId = $option->getValue() ?: 'option_' . $optionIndex;
                $options['value'][$optionId][0] = $option->getLabel();
                $options['order'][$optionId] = $option->getSortOrder() ?: $sortOrder++;
                if (is_array($option->getStoreLabels())) {
                    foreach ($option->getStoreLabels() as $label) {
                        $options['value'][$optionId][$label->getStoreId()] = $label->getLabel();
                    }
                }
                if ($option->getIsDefault()) {
                    $default[] = $optionId;
                }
            }
            $attribute->setDefault($default);
            if (count($options)) {
                $attribute->setOption($options);
            }
        }
        $this->attributeResource->save($attribute);
        return $this->get($attribute->getAttributeCode());
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function delete(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        $this->attributeResource->delete($attribute);
        return true;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function deleteById($attributeCode)
    {
        $this->delete(
            $this->get($attributeCode)
        );
        return true;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function getCustomAttributesMetadata($dataObjectClassName = null)
    {
        return $this->getList($this->searchCriteriaBuilder->create())->getItems();
    }

    /**
     * Generate code from label
     *
     * @param string $label
     * @return string
     * @since 2.0.0
     */
    protected function generateCode($label)
    {
        $code = substr(preg_replace('/[^a-z_0-9]/', '_', $this->filterManager->translitUrl($label)), 0, 30);
        $validatorAttrCode = new \Zend_Validate_Regex(['pattern' => '/^[a-z][a-z_0-9]{0,29}[a-z0-9]$/']);
        if (!$validatorAttrCode->isValid($code)) {
            $code = 'attr_' . ($code ?: substr(md5(time()), 0, 8));
        }
        return $code;
    }

    /**
     * Validate attribute code
     *
     * @param string $code
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @since 2.0.0
     */
    protected function validateCode($code)
    {
        $validatorAttrCode = new \Zend_Validate_Regex(['pattern' => '/^[a-z][a-z_0-9]{0,30}$/']);
        if (!$validatorAttrCode->isValid($code)) {
            throw InputException::invalidFieldValue('attribute_code', $code);
        }
    }

    /**
     * Validate Frontend Input Type
     *
     * @param  string $frontendInput
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @since 2.0.0
     */
    protected function validateFrontendInput($frontendInput)
    {
        /** @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator $validator */
        $validator = $this->inputtypeValidatorFactory->create();
        if (!$validator->isValid($frontendInput)) {
            throw InputException::invalidFieldValue('frontend_input', $frontendInput);
        }
    }
}
