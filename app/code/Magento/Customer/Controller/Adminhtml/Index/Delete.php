<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\Customer\Controller\Adminhtml\Index\Delete
 *
 * @since 2.0.0
 */
class Delete extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Delete customer action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addError(__('Customer could not be deleted.'));
            return $resultRedirect->setPath('customer/index');
        }

        $customerId = $this->initCurrentCustomer();
        if (!empty($customerId)) {
            try {
                $this->_customerRepository->deleteById($customerId);
                $this->messageManager->addSuccess(__('You deleted the customer.'));
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('customer/index');
    }
}
