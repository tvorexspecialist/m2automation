<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Model;

use Magento\Braintree\Model\Ui\ConfigProvider as BrainTreeConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\OneTouchOrdering\Model\CustomerData;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;

class PrepareQuote
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var CustomerBrainTreeManager
     */
    private $customerBrainTreeManager;

    /**
     * PrepareQuote constructor.
     * @param QuoteFactory $quoteFactory
     * @param StoreManagerInterface $storeManager
     * @param CustomerBrainTreeManager $customerBrainTreeManager
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        StoreManagerInterface $storeManager,
        CustomerBrainTreeManager $customerBrainTreeManager
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->customerBrainTreeManager = $customerBrainTreeManager;
    }

    /**
     * @param \Magento\OneTouchOrdering\Model\CustomerData $customerData
     * @return Quote
     */
    public function prepare(CustomerData $customerData): Quote
    {
        $store = $this->storeManager->getStore();
        $quote = $this->quoteFactory->create();

        $quote->setStore($store);
        $quote->setCurrency();
        $quote->assignCustomer($customerData->getCustomerDataModel());
        $quote->getBillingAddress()->importCustomerAddressData(
            $customerData->getDefaultBillingAddressDataModel()
        );
        $quote->getShippingAddress()->importCustomerAddressData(
            $customerData->getDefaultShippingAddressDataModel()
        );
        $quote->setInventoryProcessed(false);

        return $quote;
    }

    /**
     * @param Quote $quote
     * @throws LocalizedException
     */
    public function preparePayment(Quote $quote, $customerId)
    {
        $cc = $this->customerBrainTreeManager->getCustomerBrainTreeCard($customerId);
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
