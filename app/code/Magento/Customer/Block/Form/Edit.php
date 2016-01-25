<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Form;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Helper\Config as CustomerConfigHelper;

/**
 * Customer edit form block
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Edit extends \Magento\Customer\Block\Account\Dashboard
{
    /**
     * @var CustomerConfigHelper
     */
    protected $customerConfigHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerConfigHelper $customerConfigHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        CustomerConfigHelper $customerConfigHelper,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->subscriberFactory = $subscriberFactory;
        $this->customerRepository = $customerRepository;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerConfigHelper = $customerConfigHelper;
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $data
        );
    }

    /**
     * Retrieve form data
     *
     * @return array
     */
    protected function getFormData()
    {
        $data = $this->getData('form_data');
        if ($data === null) {
            $formData = $this->customerSession->getCustomerFormData(true);
            $data = [];
            if ($formData) {
                $data['data'] = $formData;
                $data['customer_data'] = 1;
            }
            $this->setData('form_data', $data);
        }
        return $data;
    }

    /**
     * Restore entity data from session. Entity and form code must be defined for the form.
     *
     * @param \Magento\Customer\Model\Metadata\Form $form
     * @param null $scope
     * @return \Magento\Customer\Block\Form\Register
     */
    public function restoreSessionData(\Magento\Customer\Model\Metadata\Form $form, $scope = null)
    {
        $formData = $this->getFormData();
        if (isset($formData['customer_data']) && $formData['customer_data']) {
            $request = $form->prepareRequest($formData['data']);
            $data = $form->extractData($request, $scope, false);
            $form->restoreData($data);
        }

        return $this;
    }

    /**
     * Return whether the form should be opened in an expanded mode showing the change password fields
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getChangePassword()
    {
        return $this->customerSession->getChangePassword();
    }

    /**
     * Get minimum password length
     *
     * @return string
     */
    public function getMinimumPasswordLength()
    {
        return $this->customerConfigHelper->getMinimumPasswordLength();
    }

    /**
     * Get minimum password length
     *
     * @return string
     */
    public function getRequiredCharacterClassesNumber()
    {
        return $this->customerConfigHelper->getRequiredCharacterClassesNumber();
    }
}
