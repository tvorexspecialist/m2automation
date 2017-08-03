<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\File\Address;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Model\FileUploader;
use Magento\Customer\Model\FileUploaderFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class \Magento\Customer\Controller\Adminhtml\File\Address\Upload
 *
 * @since 2.1.3
 */
class Upload extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var FileUploaderFactory
     * @since 2.1.3
     */
    private $fileUploaderFactory;

    /**
     * @var AddressMetadataInterface
     * @since 2.1.3
     */
    private $addressMetadataService;

    /**
     * @var LoggerInterface
     * @since 2.1.3
     */
    private $logger;

    /**
     * @param Context $context
     * @param FileUploaderFactory $fileUploaderFactory
     * @param AddressMetadataInterface $addressMetadataService
     * @param LoggerInterface $logger
     * @since 2.1.3
     */
    public function __construct(
        Context $context,
        FileUploaderFactory $fileUploaderFactory,
        AddressMetadataInterface $addressMetadataService,
        LoggerInterface $logger
    ) {
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->addressMetadataService = $addressMetadataService;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     * @since 2.1.3
     */
    public function execute()
    {
        try {
            if (empty($_FILES)) {
                throw new \Exception('$_FILES array is empty.');
            }

            // Must be executed before any operations with $_FILES!
            $this->convertFilesArray();

            $attributeCode = key($_FILES['address']['name']);
            $attributeMetadata = $this->addressMetadataService->getAttributeMetadata($attributeCode);

            /** @var FileUploader $fileUploader */
            $fileUploader = $this->fileUploaderFactory->create([
                'attributeMetadata' => $attributeMetadata,
                'entityTypeCode' => AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                'scope' => 'address',
            ]);

            $errors = $fileUploader->validate();
            if (true !== $errors) {
                $errorMessage = implode('</br>', $errors);
                throw new LocalizedException(__($errorMessage));
            }

            $result = $fileUploader->upload();
        } catch (LocalizedException $e) {
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode(),
            ];
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $result = [
                'error' => __('Something went wrong while saving file.'),
                'errorcode' => $e->getCode(),
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }

    /**
     * Update global $_FILES array. Convert data to standard form
     *
     * NOTE: This conversion is required to use \Magento\Framework\File\Uploader::_setUploadFileId($fileId) method.
     *
     * @return void
     * @since 2.1.3
     */
    private function convertFilesArray()
    {
        foreach ($_FILES['address'] as $itemKey => $item) {
            foreach ($item as $value) {
                if (is_array($value)) {
                    $_FILES['address'][$itemKey] = [
                        key($value) => current($value),
                    ];
                }
            }
        }
    }
}
