/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'underscore',
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/quote',
        'Magento_Braintree/js/view/payment/adapter',
        'mage/translate',
        'Magento_Braintree/js/validator',
        'Magento_Braintree/js/view/payment/validator-handler',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (
        _,
        $,
        Component,
        quote,
        braintree,
        $t,
        validator,
        validatorManager,
        fullScreenLoader
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                active: false,
                braintreeClient: null,
                braintreeDeviceData: null,
                paymentMethodNonce: null,
                lastBillingAddress: null,
                validatorManager: validatorManager,
                code: 'braintree',

                /**
                 * Additional payment data
                 *
                 * {Object}
                 */
                additionalData: {},

                /**
                 * Braintree client configuration
                 *
                 * {Object}
                 */
                clientConfig: {

                    /**
                     * Triggers on payment nonce receive
                     * @param {Object} response
                     */
                    onPaymentMethodReceived: function (response) {
                        this.beforePlaceOrder(response);
                    },

                    /**
                     * Triggers on any Braintree error
                     */
                    onError: function () {
                        this.paymentMethodNonce = null;
                    },

                    /**
                     * Triggers when customer click "Cancel"
                     */
                    onCancelled: function () {
                        this.paymentMethodNonce = null;
                    }
                },
                imports: {
                    onActiveChange: 'active'
                }
            },

            /**
             * Set list of observable attributes
             *
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                validator.setConfig(window.checkoutConfig.payment[this.getCode()]);
                this._super()
                    .observe(['active']);
                this.validatorManager.initialize();
                this.initBraintree();

                return this;
            },

            /**
             * Get payment name
             *
             * @returns {String}
             */
            getCode: function () {
                return this.code;
            },

            /**
             * Check if payment is active
             *
             * @returns {Boolean}
             */
            isActive: function () {
                var active = this.getCode() === this.isChecked();

                this.active(active);

                return active;
            },

            /**
             * Triggers when payment method change
             * @param {Boolean} isActive
             */
            onActiveChange: function (isActive) {
                if (!isActive || this.isSingleUse()) {
                    return;
                }

                this.reInitBraintree();
            },

            /**
             * Init config
             */
            initClientConfig: function () {
                // Advanced fraud tools settings
                if (this.hasFraudProtection()) {
                    this.clientConfig = _.extend(this.clientConfig, this.kountConfig());
                }

                _.each(this.clientConfig, function (fn, name) {
                    if (typeof fn === 'function') {
                        this.clientConfig[name] = fn.bind(this);
                    }
                }, this);
            },

            /**
             * Create Braintree configuration
             */
            initBraintree: function () {
                this.initClientConfig();
                braintree.config = _.extend(braintree.config, this.clientConfig);
            },

            /**
             * Re-init Braintree configuration
             */
            reInitBraintree: function () {
                var intervalId = setInterval(function () {
                    // stop loader when frame will be loaded
                    if ($('#braintree-hosted-field-number').length) {
                        clearInterval(intervalId);
                        fullScreenLoader.stopLoader();
                    }
                }, 500);

                fullScreenLoader.startLoader();
                braintree.setConfig(this.clientConfig);
                braintree.setup();
            },

            /**
             * @returns {Object}
             */
            kountConfig: function () {
                var config = {
                    dataCollector: {
                        kount: {
                            environment: this.getEnvironment()
                        }
                    },

                    /**
                     * Device data initialization
                     *
                     * @param {Object} braintreeInstance
                     */
                    onReady: function (braintreeInstance) {
                        this.additionalData['device_data'] = braintreeInstance.deviceData;
                    }
                };

                if (this.getKountMerchantId()) {
                    config.dataCollector.kount.merchantId = this.getKountMerchantId();
                }

                return config;
            },

            /**
             * Get full selector name
             *
             * @param {String} field
             * @returns {String}
             */
            getSelector: function (field) {
                return '#' + this.getCode() + '_' + field;
            },

            /**
             * Get list of available CC types
             *
             * @returns {Object}
             */
            getCcAvailableTypes: function () {
                var availableTypes = validator.getAvailableCardTypes(),
                    billingAddress = quote.billingAddress(),
                    billingCountryId;

                this.lastBillingAddress = quote.shippingAddress();

                if (!billingAddress) {
                    billingAddress = this.lastBillingAddress;
                }

                billingCountryId = billingAddress.countryId;

                if (billingCountryId && validator.getCountrySpecificCardTypes(billingCountryId)) {

                    return validator.collectTypes(
                        availableTypes, validator.getCountrySpecificCardTypes(billingCountryId)
                    );
                }

                return availableTypes;
            },

            /**
             * @returns {Boolean}
             */
            hasFraudProtection: function () {
                return window.checkoutConfig.payment[this.getCode()].hasFraudProtection;
            },

            /**
             * @returns {String}
             */
            getEnvironment: function () {
                return window.checkoutConfig.payment[this.getCode()].environment;
            },

            /**
             * @returns {String}
             */
            getKountMerchantId: function () {
                return window.checkoutConfig.payment[this.getCode()].kountMerchantId;
            },

            /**
             * Get data
             *
             * @returns {Object}
             */
            getData: function () {
                var data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'payment_method_nonce': this.paymentMethodNonce
                    }
                };

                data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

                return data;
            },

            /**
             * Set payment nonce
             * @param {String} paymentMethodNonce
             */
            setPaymentMethodNonce: function (paymentMethodNonce) {
                this.paymentMethodNonce = paymentMethodNonce;
            },

            /**
             * Prepare data to place order
             * @param {Object} data
             */
            beforePlaceOrder: function (data) {
                this.setPaymentMethodNonce(data.nonce);
                this.placeOrder();
            },

            /**
             * Action to place order
             * @param {String} key
             */
            placeOrder: function (key) {
                var self = this;

                if (key) {
                    return self._super();
                }
                // place order on success validation
                self.validatorManager.validate(self, function () {
                    return self.placeOrder('parent');
                });

                return false;
            },

            /**
             * Check if Braintree configured without PayPal
             * @returns {Boolean}
             */
            isSingleUse: function () {
                return window.checkoutConfig.payment[this.getCode()].isSingleUse;
            }
        });
    }
);
