<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\File\Customer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\FileUploader;
use Magento\Customer\Model\FileUploaderFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class \Magento\Customer\Controller\Adminhtml\File\Customer\Upload
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
     * @var CustomerMetadataInterface
     * @since 2.1.3
     */
    private $customerMetadataService;

    /**
     * @var LoggerInterface
     * @since 2.1.3
     */
    private $logger;

    /**
     * @param Context $context
     * @param FileUploaderFactory $fileUploaderFactory
     * @param CustomerMetadataInterface $customerMetadataService
     * @param LoggerInterface $logger
     * @since 2.1.3
     */
    public function __construct(
        Context $context,
        FileUploaderFactory $fileUploaderFactory,
        CustomerMetadataInterface $customerMetadataService,
        LoggerInterface $logger
    ) {
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->customerMetadataService = $customerMetadataService;
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

            $attributeCode = key($_FILES['customer']['name']);
            $attributeMetadata = $this->customerMetadataService->getAttributeMetadata($attributeCode);

            /** @var FileUploader $fileUploader */
            $fileUploader = $this->fileUploaderFactory->create([
                'attributeMetadata' => $attributeMetadata,
                'entityTypeCode' => CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                'scope' => 'customer',
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
}
