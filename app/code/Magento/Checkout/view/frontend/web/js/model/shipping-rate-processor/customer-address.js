/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (resourceUrlManager, quote, storage, shippingService, rateRegistry, errorProcessor) {
        "use strict";
        return {
            getRates: function(address) {
                var cache = rateRegistry.get(address.getKey());
                if (cache) {
                    shippingService.setShippingRates(cache);
                } else {
                    shippingService.isLoading(true);
                    storage.post(
                        resourceUrlManager.getUrlForEstimationShippingMethodsByAddressId(),
                        JSON.stringify({
                            addressId: address.customerAddressId
                        }),
                        false
                    ).done(
                        function(result) {
                            rateRegistry.set(address.getKey(), result);
                            shippingService.setShippingRates(result);
                        }

                    ).fail(
                        function(response) {
                            shippingService.setShippingRates([]);
                            errorProcessor.process(response);
                        }
                    ).always(
                        function () {
                            shippingService.isLoading(false);
                        }
                    );
                }
            }
        };
    }
);
