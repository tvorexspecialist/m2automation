/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    '../model/quote'
], function (quote) {
    'use strict';

    return function (paymentMethod) {
        quote.paymentMethod(paymentMethod);
    };
});
