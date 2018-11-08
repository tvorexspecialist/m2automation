<?php
declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Address;

use Magento\Backend\App\Action;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;

/**
 * Abstract class for customer default addresses changing
 */
abstract class AbstractDefaultAddress extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Action\Context $context
     * @param AddressRepositoryInterface $addressRepository
     * @param LoggerInterface $logger
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        AddressRepositoryInterface $addressRepository,
        LoggerInterface $logger,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute action to set customer default billing or shipping address
     *
     * @return Json
     */
    public function execute(): Json
    {
        $customerId = $this->getRequest()->getParam('parent_id', false);
        $addressId = $this->getRequest()->getParam('id', false);
        $error = false;
        $message = '';

        if ($addressId) {
            try {
                $address = $this->addressRepository->getById($addressId)->setCustomerId($customerId);
                $this->setAddressAsDefault($address);
                $this->addressRepository->save($address);
                $message = $this->getSuccessMessage();
            } catch (\Exception $e) {
                $error = true;
                $message = $this->getExceptionMessage();
                $this->logger->critical($e);
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            [
                'message' => $message,
                'error' => $error,
            ]
        );

        return $resultJson;
    }

    /**
     * Set passed address as customer's default address
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return $this
     */
    abstract protected function setAddressAsDefault($address);

    /**
     * Get success message about default address changed
     *
     * @return \Magento\Framework\Phrase
     */
    abstract protected function getSuccessMessage(): Phrase;

    /**
     * Get error message about unsuccessful attempt to change default address
     *
     * @return \Magento\Framework\Phrase
     */
    abstract protected function getExceptionMessage(): Phrase;
}
