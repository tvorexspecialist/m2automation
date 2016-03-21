<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Constraint;

/**
 * Checks that prices excl and incl tax on order review and customer order pages are equal to specified in dataset.
 */
class AssertTaxCalculationAfterCheckoutDownloadableExcludingIncludingTax extends
 AbstractAssertTaxCalculationAfterCheckoutDownloadable
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Get review totals.
     *
     * @param $actualPrices
     * @return array
     */
    public function getReviewTotals($actualPrices)
    {
        $reviewBlock = $this->checkoutOnepage->getReviewBlock();
        $actualPrices['subtotal_excl_tax'] = $reviewBlock->getSubtotalExclTax();
        $actualPrices['subtotal_incl_tax'] = $reviewBlock->getSubtotalInclTax();
        $actualPrices['discount'] = $reviewBlock->getDiscount();
        $actualPrices['shipping_excl_tax'] = $reviewBlock->getShippingExclTax();
        $actualPrices['shipping_incl_tax'] = $reviewBlock->getShippingInclTax();
        $actualPrices['tax'] = $reviewBlock->getTax();
        $actualPrices['grand_total_excl_tax'] = $reviewBlock->getGrandTotalExclTax();
        $actualPrices['grand_total_incl_tax'] = $reviewBlock->getGrandTotalInclTax();

        return $actualPrices;
    }

    /**
     * Get order totals.
     *
     * @param $actualPrices
     * @return array
     */
    public function getOrderTotals($actualPrices)
    {
        $viewBlock = $this->customerOrderView->getOrderViewBlock();
        $actualPrices['subtotal_excl_tax'] = $viewBlock->getSubtotalExclTax();
        $actualPrices['subtotal_incl_tax'] = $viewBlock->getSubtotalInclTax();
        $actualPrices['discount'] = $viewBlock->getDiscount();
        $actualPrices['shipping_excl_tax'] = $viewBlock->getShippingExclTax();
        $actualPrices['shipping_incl_tax'] = $viewBlock->getShippingInclTax();
        $actualPrices['tax'] = $viewBlock->getTax();
        $actualPrices['grand_total_excl_tax'] = $viewBlock->getGrandTotal();
        $actualPrices['grand_total_incl_tax'] = $viewBlock->getGrandTotalInclTax();

        return $actualPrices;
    }
}
