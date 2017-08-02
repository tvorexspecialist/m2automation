<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassSubscribe
 * @since 2.0.0
 */
class MassSubscribe extends AbstractMassAction
{
    /**
     * @var CustomerRepositoryInterface
     * @since 2.0.0
     */
    protected $customerRepository;

    /**
     * @var SubscriberFactory
     * @since 2.0.0
     */
    protected $subscriberFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param SubscriberFactory $subscriberFactory
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CustomerRepositoryInterface $customerRepository,
        SubscriberFactory $subscriberFactory
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->customerRepository = $customerRepository;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * Customer mass subscribe action
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    protected function massAction(AbstractCollection $collection)
    {
        $customersUpdated = 0;
        foreach ($collection->getAllIds() as $customerId) {
            // Verify customer exists
            $this->customerRepository->getById($customerId);
            $this->subscriberFactory->create()->subscribeCustomerById($customerId);
            $customersUpdated++;
        }

        if ($customersUpdated) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', $customersUpdated));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}
