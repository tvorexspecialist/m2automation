/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Tax/js/view/checkout/summary/tax',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, totals) {
        'use strict';

        var isTaxDisplayedInGrandTotal = window.checkoutConfig.includeTaxInGrandTotal,
            isFullTaxSummaryDisplayed = window.checkoutConfig.isFullTaxSummaryDisplayed,
            isZeroTaxDisplayed = window.checkoutConfig.isZeroTaxDisplayed;

        return Component.extend({

            /**
             * @override
             */
            ifShowValue: function () {
                if (this.getPureValue() === 0) {
                    return isZeroTaxDisplayed;
                }

                return true;
            },

            /**
             * @override
             */
            ifShowDetails: function () {
                return isTaxDisplayedInGrandTotal && this.getPureValue() > 0 && isFullTaxSummaryDisplayed;
            },

            /**
             * @override
             */
            isCalculated: function () {
                return this.totals() && totals.getSegment('tax') !== null;
            }
        });
    }
);
