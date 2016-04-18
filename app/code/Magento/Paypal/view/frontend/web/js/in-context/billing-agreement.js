/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Customer/js/customer-data'
], function ($, confirm, customerData) {
    'use strict';

    $.widget('mage.billingAgreement', {
        options: {
            invalidateOnCreate: false,
            cancelButtonSelector: '.block-billing-agreements-view button.cancel',
            cancelMessage: '',
            cancelUrl: ''
        },

        /**
         * Initialize billing agreements events
         * @private
         */
        _create: function () {
            var self = this;

            if (this.options.invalidateOnCreate) {
                this.invalidate();
            }
            this.element.on('click', function () {
                confirm({
                    content: self.options.cancelMessage,
                    actions: {
                        /**
                         * 'Confirm' action handler.
                         */
                        confirm: function () {
                            self.invalidate();
                            window.location.href = self.options.cancelUrl;
                        }
                    }
                });

                return false;
            });
        },

        /**
         * clear paypal billing agreement customer data
         * @returns void
         */
        invalidate: function () {
            customerData.invalidate(['paypal-billing-agreement']);
        }
    });

    return $.mage.billingAgreement;
});
