<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Checks;

use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use Magento\Payment\Model\Checks\CanUseForCountry\CountryProvider;

/**
 * Checks possibility to use payment method at particular country
 *
 * @api
 * @since 2.0.0
 */
class CanUseForCountry implements SpecificationInterface
{
    /**
     * @var CountryProvider
     * @since 2.0.0
     */
    protected $countryProvider;

    /**
     * @param CountryProvider $countryProvider
     * @since 2.0.0
     */
    public function __construct(CountryProvider $countryProvider)
    {
        $this->countryProvider = $countryProvider;
    }

    /**
     * Check whether payment method is applicable to quote
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     * @return bool
     * @since 2.0.0
     */
    public function isApplicable(MethodInterface $paymentMethod, Quote $quote)
    {
        return $paymentMethod->canUseForCountry($this->countryProvider->getCountry($quote));
    }
}
