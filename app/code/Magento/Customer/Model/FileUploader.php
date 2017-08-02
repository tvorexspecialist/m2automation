<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\FileProcessorFactory;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class \Magento\Customer\Model\FileUploader
 *
 * @since 2.2.0
 */
class FileUploader
{
    /**
     * @var CustomerMetadataInterface
     * @since 2.2.0
     */
    private $customerMetadataService;

    /**
     * @var AddressMetadataInterface
     * @since 2.2.0
     */
    private $addressMetadataService;

    /**
     * @var ElementFactory
     * @since 2.2.0
     */
    private $elementFactory;

    /**
     * @var FileProcessorFactory
     * @since 2.2.0
     */
    private $fileProcessorFactory;

    /**
     * @var AttributeMetadataInterface
     * @since 2.2.0
     */
    private $attributeMetadata;

    /**
     * @var string
     * @since 2.2.0
     */
    private $entityTypeCode;

    /**
     * @var string
     * @since 2.2.0
     */
    private $scope;

    /**
     * @param CustomerMetadataInterface $customerMetadataService
     * @param AddressMetadataInterface $addressMetadataService
     * @param ElementFactory $elementFactory
     * @param FileProcessorFactory $fileProcessorFactory
     * @param AttributeMetadataInterface $attributeMetadata
     * @param string $entityTypeCode
     * @param string $scope
     * @since 2.2.0
     */
    public function __construct(
        CustomerMetadataInterface $customerMetadataService,
        AddressMetadataInterface $addressMetadataService,
        ElementFactory $elementFactory,
        FileProcessorFactory $fileProcessorFactory,
        AttributeMetadataInterface $attributeMetadata,
        $entityTypeCode,
        $scope
    ) {
        $this->customerMetadataService = $customerMetadataService;
        $this->addressMetadataService = $addressMetadataService;
        $this->elementFactory = $elementFactory;
        $this->fileProcessorFactory = $fileProcessorFactory;
        $this->attributeMetadata = $attributeMetadata;
        $this->entityTypeCode = $entityTypeCode;
        $this->scope = $scope;
    }

    /**
     * Validate uploaded file
     *
     * @return array|bool
     * @since 2.2.0
     */
    public function validate()
    {
        $formElement = $this->elementFactory->create(
            $this->attributeMetadata,
            null,
            $this->entityTypeCode
        );

        $errors = $formElement->validateValue($this->getData());
        return $errors;
    }

    /**
     * Execute file uploading
     *
     * @return \string[]
     * @throws LocalizedException
     * @since 2.2.0
     */
    public function upload()
    {
        /** @var FileProcessor $fileProcessor */
        $fileProcessor = $this->fileProcessorFactory->create([
            'entityTypeCode' => $this->entityTypeCode,
            'allowedExtensions' => $this->getAllowedExtensions(),
        ]);

        $result = $fileProcessor->saveTemporaryFile($this->scope . '[' . $this->getAttributeCode() . ']');

        // Update tmp_name param. Required for attribute validation!
        $result['tmp_name'] = $result['path'] . '/' . ltrim($result['file'], '/');

        $result['url'] = $fileProcessor->getViewUrl(
            FileProcessor::TMP_DIR . '/' . ltrim($result['name'], '/'),
            $this->attributeMetadata->getFrontendInput()
        );

        return $result;
    }

    /**
     * Get attribute code
     *
     * @return string
     * @since 2.2.0
     */
    private function getAttributeCode()
    {
        return key($_FILES[$this->scope]['name']);
    }

    /**
     * Retrieve data from global $_FILES array
     *
     * @return array
     * @since 2.2.0
     */
    private function getData()
    {
        $data = [];

        $fileAttributes = $_FILES[$this->scope];
        foreach ($fileAttributes as $attributeName => $attributeValue) {
            $data[$attributeName] = $attributeValue[$this->getAttributeCode()];
        }

        return $data;
    }

    /**
     * Get allowed extensions
     *
     * @return array
     * @since 2.2.0
     */
    private function getAllowedExtensions()
    {
        $allowedExtensions = [];

        $validationRules = $this->attributeMetadata->getValidationRules();
        foreach ($validationRules as $validationRule) {
            if ($validationRule->getName() == 'file_extensions') {
                $allowedExtensions = explode(',', $validationRule->getValue());
                array_walk($allowedExtensions, function (&$value) {
                    $value = strtolower(trim($value));
                });
                break;
            }
        }

        return $allowedExtensions;
    }
}
