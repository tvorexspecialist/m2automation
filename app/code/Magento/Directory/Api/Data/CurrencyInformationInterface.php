<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Api\Data;

/**
 * Currency Information interface.
 *
 * @api
 */
interface CurrencyInformationInterface
{
    /**
     * Get the base currency code for the store.
     *
     * @return string
     */
    public function getBaseCurrencyCode();

    /**
     * Set the base currency code for the store.
     *
     * @param string $code
     * @return $this
     */
    public function setBaseCurrencyCode($code);

    /**
     * Get the currency symbol of the base currency for the store.
     *
     * @return string
     */
    public function getBaseCurrencySymbol();

    /**
     * Set the currency symbol of the base currency for the store.
     *
     * @param string $symbol
     * @return $this
     */
    public function setBaseCurrencySymbol($symbol);

    /**
     * Get the default display currency code for the store.
     *
     * @return string
     */
    public function getDefaultDisplayCurrencyCode();

    /**
     * Set the default display currency code for the store.
     *
     * @param string $code
     * @return $this
     */
    public function setDefaultDisplayCurrencyCode($code);

    /**
     * Get the currency symbol of the default display currency for the store.
     *
     * @return string
     */
    public function getDefaultDisplayCurrencySymbol();

    /**
     * Set the currency symbol of the default display currency for the store.
     *
     * @param string $symbol
     * @return $this
     */
    public function setDefaultDisplayCurrencySymbol($symbol);

    /**
     * Get the list of allowed currency codes for the store.
     *
     * @return string[]
     */
    public function getAvailableCurrencyCodes();

    /**
     * Set the list of allowed currency codes for the store.
     *
     * @param string[] $codes
     * @return $this
     */
    public function setAvailableCurrencyCodes(array $codes = null);

    /**
     * Get the list of exchange rate information for the store.
     *
     * @return \Magento\Directory\Api\Data\ExchangeRateInterface[]
     */
    public function getExchangeRates();

    /**
     * Set the list of exchange rate information for the store.
     *
     * @param \Magento\Directory\Api\Data\ExchangeRateInterface[] $exchangeRates
     * @return $this
     */
    public function setExchangeRates(array $exchangeRates = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Directory\Api\Data\CurrencyInformationInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Directory\Api\Data\CurrencyInformationInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Directory\Api\Data\CurrencyInformationInterface $extensionAttributes
    );
}
