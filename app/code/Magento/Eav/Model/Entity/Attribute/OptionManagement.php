<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class OptionManagement implements \Magento\Eav\Api\AttributeOptionManagementInterface
{
    /**
     * @var \Magento\Eav\Model\AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $resourceModel;

    /**
     * @param \Magento\Eav\Model\AttributeRepository $attributeRepository
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $resourceModel
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Eav\Model\AttributeRepository $attributeRepository,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $resourceModel
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->resourceModel = $resourceModel;
    }

    /**
     * {@inheritdoc}
     */
    public function add($entityType, $attributeCode, $option)
    {
        if (empty($attributeCode)) {
            throw new InputException(__('Empty attribute code'));
        }

        $attribute = $this->attributeRepository->get($entityType, $attributeCode);
        if (!$attribute->usesSource()) {
            throw new StateException(__('Attribute %1 doesn\'t work with options', $attributeCode));
        }

        $optionLabel = $option->getLabel();
        $optionId = $this->getOptionId($option);
        $options = [];
        $options['value'][$optionId][0] = $optionLabel;
        $options['order'][$optionId] = $option->getSortOrder();

        if (is_array($option->getStoreLabels())) {
            foreach ($option->getStoreLabels() as $label) {
                $options['value'][$optionId][$label->getStoreId()] = $label->getLabel();
            }
        }

        if ($option->getIsDefault()) {
            $attribute->setDefault([$optionId]);
        }

        $attribute->setOption($options);
        try {
            $this->resourceModel->save($attribute);
            if ($optionLabel && $attribute->getAttributeCode()) {
                $this->setOptionValue($option, $attribute, $optionLabel);
            }
        } catch (\Exception $e) {
            throw new StateException(__('Cannot save attribute %1', $attributeCode));
        }

        return $option;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($entityType, $attributeCode, $optionId)
    {
        if (empty($attributeCode)) {
            throw new InputException(__('Empty attribute code'));
        }

        $attribute = $this->attributeRepository->get($entityType, $attributeCode);
        if (!$attribute->usesSource()) {
            throw new StateException(__('Attribute %1 doesn\'t have any option', $attributeCode));
        }
        $this->validateOption($attribute, $optionId);

        $removalMarker = [
            'option' => [
                'value' => [$optionId => []],
                'delete' => [$optionId => '1'],
            ],
        ];
        $attribute->addData($removalMarker);
        try {
            $this->resourceModel->save($attribute);
        } catch (\Exception $e) {
            throw new StateException(__('Cannot save attribute %1', $attributeCode));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($entityType, $attributeCode)
    {
        if (empty($attributeCode)) {
            throw new InputException(__('Empty attribute code'));
        }
        $attribute = $this->attributeRepository->get($entityType, $attributeCode);

        try {
            $options = $attribute->getOptions();
        } catch (\Exception $e) {
            throw new StateException(__('Cannot load options for attribute %1', $attributeCode));
        }

        return $options;
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @param int $optionId
     * @throws NoSuchEntityException
     * @return void
     */
    protected function validateOption($attribute, $optionId)
    {
        if (!$attribute->getSource()->getOptionText($optionId)) {
            throw new NoSuchEntityException(
                __('Attribute %1 does not contain option with Id %2', $attribute->getAttributeCode(), $optionId)
            );
        }
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface $option
     * @return string
     */
    private function getOptionId($option)
    {
        return $option->getValue() ?: 'new_option';
    }

    /**
     * @param \Magento\Eav\Api\Data\AttributeOptionInterface $option
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @param string $optionLabel
     * @return void
     */
    public function setOptionValue($option, $attribute, $optionLabel)
    {
        if ($optionId = $attribute->getSource()->getOptionId($optionLabel)) {
            $option->setValue($attribute->getSource()->getOptionId($optionId));
        } else {
            if (is_array($option->getStoreLabels())) {
                foreach ($option->getStoreLabels() as $label) {
                    if ($optionId = $attribute->getSource()->getOptionId($label->getLabel())) {
                        $option->setValue($attribute->getSource()->getOptionId($optionId));
                        break;
                    }
                }
            }
        }
    }
}
