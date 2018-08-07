<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote as QuoteEntity;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage as OrderAmountValidationMessage;
use \Magento\Store\Model\ScopeInterface;

/**
 * @api
 * @since 100.0.2
 */
class QuoteValidator
{
    /**
     * Maximum available number
     */
    const MAXIMUM_AVAILABLE_NUMBER = 99999999;

    /**
     * @var AllowedCountries
     */
    private $allowedCountryReader;

    /**
     * @var OrderAmountValidationMessage
     */
    private $minimumAmountMessage;

    /**
     * QuoteValidator constructor.
     *
     * @param AllowedCountries|null $allowedCountryReader
     * @param OrderAmountValidationMessage|null $minimumAmountMessage
     */
    public function __construct(
        AllowedCountries $allowedCountryReader = null,
        OrderAmountValidationMessage $minimumAmountMessage = null
    ) {
        $this->allowedCountryReader = $allowedCountryReader ?: ObjectManager::getInstance()
            ->get(AllowedCountries::class);
        $this->minimumAmountMessage = $minimumAmountMessage ?: ObjectManager::getInstance()
            ->get(OrderAmountValidationMessage::class);
    }

    /**
     * Validate quote amount
     *
     * @param QuoteEntity $quote
     * @param float $amount
     * @return $this
     */
    public function validateQuoteAmount(QuoteEntity $quote, $amount)
    {
        if (!$quote->getHasError() && $amount >= self::MAXIMUM_AVAILABLE_NUMBER) {
            $quote->setHasError(true);
            $quote->addMessage(__('This item price or quantity is not valid for checkout.'));
        }
        return $this;
    }

    /**
     * Validate quote before submit
     *
     * @param Quote $quote
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateBeforeSubmit(QuoteEntity $quote)
    {
        if (!$quote->isVirtual()) {
            $address = $quote->getShippingAddress();
            $address->setStoreId($quote->getStoreId());
            if ($address->validate() !== true) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __(
                        'Please check the shipping address information. %1',
                        implode(' ', $address->validate())
                    )
                );
            }

            // Checks if country id present in the allowed countries list.
            if (!in_array(
                $address->getCountryId(),
                $this->allowedCountryReader->getAllowedCountries(ScopeInterface::SCOPE_STORE, $quote->getStoreId())
            )) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Some addresses can't be used due to the configurations for specific countries.")
                );
            }

            $method = $address->getShippingMethod();
            $rate = $address->getShippingRateByCode($method);
            if (!$method || !$rate) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The shipping method is missing. Select the shipping method and try again.')
                );
            }
        }
        $billingAddress = $quote->getBillingAddress();
        $billingAddress->setStoreId($quote->getStoreId());
        if ($billingAddress->validate() !== true) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Please check the billing address information. %1',
                    implode(' ', $quote->getBillingAddress()->validate())
                )
            );
        }
        if (!$quote->getPayment()->getMethod()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Enter a valid payment method and try again. ')
            );
        }
        if (!$quote->validateMinimumAmount($quote->getIsMultiShipping())) {
            throw new LocalizedException($this->minimumAmountMessage->getMessage());
        }

        return $this;
    }
}
