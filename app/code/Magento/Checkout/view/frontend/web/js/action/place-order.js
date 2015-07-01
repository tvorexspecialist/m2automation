/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'mage/url',
        'Magento_Ui/js/model/messageList',
        'Magento_Customer/js/model/customer',
        'underscore'
    ],
    function (quote, urlBuilder, storage, url, messageList, customer, _) {
        'use strict';

        return function (paymentData, redirectOnSuccess) {
            var serviceUrl, payload;

            redirectOnSuccess = redirectOnSuccess === false ? false : true;
            /**
             * Checkout for guest and registered customer.
             */
            if (!customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/payment-information', {
                    quoteId: quote.getQuoteId()
                });
                payload = {
                    cartId: quote.getQuoteId(),
                    email: quote.guestEmail,
                    paymentMethod: paymentData,
                    billingAddress: quote.billingAddress()
                };
            } else {
                serviceUrl = urlBuilder.createUrl('/carts/mine/payment-information', {});
                payload = {
                    cartId: quote.getQuoteId(),
                    paymentMethod: paymentData,
                    billingAddress: quote.billingAddress()
                };
            }
            storage.post(
                serviceUrl, JSON.stringify(payload)
            ).done(
                function () {
                    if (redirectOnSuccess) {
                        window.location.replace(url.build('checkout/onepage/success/'));
                    }
                }
            ).fail(
                function (response) {
                    var error = JSON.parse(response.responseText);
                    messageList.addErrorMessage(error);
                }
            );
        };
    }
);
