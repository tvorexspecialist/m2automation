<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Braintree\Model\Ui\ConfigProvider as BrainTreeConfigProvider;

class PrepareQuote
{
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\OneTouchOrdering\Model\CustomerBrainTreeManager
     */
    protected $customerBrainTreeManager;
    /**
     * @var CustomerData
     */
    protected $customerData;

    public function __construct(
        \Magento\OneTouchOrdering\Model\CustomerData $customerData,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\OneTouchOrdering\Model\CustomerBrainTreeManager $customerBrainTreeManager
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->customerBrainTreeManager = $customerBrainTreeManager;
        $this->customerData = $customerData;
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function prepare($params)
    {
        $store = $this->storeManager->getStore();
        $quote = $this->quoteFactory->create();

        $quote->setStore($store);
        $quote->setCurrency();
        $quote->assignCustomer($this->customerData->getCustomerDataModel());
        $quote->getBillingAddress()->importCustomerAddressData(
            $this->customerData->getDefaultBillingAddressDataModel()
        );

        if ($addressId = $params->getCustomerAddress()) {
            $shippingAddressData = $this->customerData->getShippingAddressDataModel($addressId);
        } else {
            $shippingAddressData = $this->customerData->getDefaultShippingAddressDataModel();
        }

        $quote->getShippingAddress()->importCustomerAddressData(
            $shippingAddressData
        );
        $quote->setInventoryProcessed(false);

        return $quote;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function preparePayment(\Magento\Quote\Model\Quote $quote)
    {
        $customerId = $this->customerData->getCustomerId();
        $cc = $this->customerBrainTreeManager->getCustomerBrainTreeCard($customerId);

        if (!$cc) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('There are no credit cards available.')
            );
        }
        $publicHash = $cc->getPublicHash();
        $quote->getPayment()->setQuote($quote)->importData(
            ['method' => BrainTreeConfigProvider::CC_VAULT_CODE]
        )->setAdditionalInformation([
                'customer_id' => $customerId,
                'public_hash' => $publicHash,
                'payment_method_nonce' => $this->customerBrainTreeManager->getNonce($publicHash, $customerId),
                'is_active_payment_token_enabler' => true
        ]);
        $quote->collectTotals();
    }
}
