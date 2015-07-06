/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        '../model/shipping-rates-validator',
        '../model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        fedexShippingRatesValidator,
        fedexShippingRatesValidationRules
    ) {
        "use strict";
        defaultShippingRatesValidator.registerValidator('fedex', fedexShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('fedex', fedexShippingRatesValidationRules);
        return Component;
    }
);
