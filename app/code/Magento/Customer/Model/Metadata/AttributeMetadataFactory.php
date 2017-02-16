<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterfaceFactory;
use Magento\Customer\Api\Data\OptionInterface;
use Magento\Customer\Api\Data\OptionInterfaceFactory;
use Magento\Customer\Api\Data\ValidationRuleInterfaceFactory;

/**
 * Factory for AttributeMetadataInterface
 */
class AttributeMetadataFactory
{
    /**
     * @var AttributeMetadataInterfaceFactory
     */
    private $attributeMetadataFactory;

    /**
     * @var OptionInterfaceFactory
     */
    private $optionFactory;

    /**
     * @var ValidationRuleInterfaceFactory
     */
    private $validationRuleFactory;

    /**
     * Constructor
     *
     * @param AttributeMetadataInterfaceFactory $attributeMetadataFactory
     * @param OptionInterfaceFactory $optionFactory
     * @param ValidationRuleInterfaceFactory $validationRuleFactory
     */
    public function __construct(
        AttributeMetadataInterfaceFactory $attributeMetadataFactory,
        OptionInterfaceFactory $optionFactory,
        ValidationRuleInterfaceFactory $validationRuleFactory
    ) {
        $this->attributeMetadataFactory = $attributeMetadataFactory;
        $this->optionFactory = $optionFactory;
        $this->validationRuleFactory = $validationRuleFactory;
    }

    /**
     * Create and populate with data AttributeMetadataInterface
     *
     * @param array $data
     * @return AttributeMetadataInterface
     */
    public function create($data)
    {
        if (isset($data[AttributeMetadataInterface::OPTIONS])) {
            $data[AttributeMetadataInterface::OPTIONS] = $this->createOptions(
                $data[AttributeMetadataInterface::OPTIONS]
            );
        }
        if (isset($data[AttributeMetadataInterface::VALIDATION_RULES])) {
            $validationRules = [];
            foreach ($data[AttributeMetadataInterface::VALIDATION_RULES] as $validationRuleData) {
                $validationRules[] = $this->validationRuleFactory->create(['data' => $validationRuleData]);
            }
            $data[AttributeMetadataInterface::VALIDATION_RULES] = $validationRules;
        }
        return $this->attributeMetadataFactory->create(['data' => $data]);
    }

    /**
     * Create and populate with data OptionInterface
     *
     * @param array $data
     * @return OptionInterface[]
     */
    private function createOptions($data)
    {
        foreach ($data as $key => $optionData) {
            if (isset($optionData[OptionInterface::OPTIONS])) {
                $optionData[OptionInterface::OPTIONS] = $this->createOptions($optionData[OptionInterface::OPTIONS]);
            }
            $data[$key] = $this->optionFactory->create(['data' => $optionData]);
        }
        return $data;
    }
}
