/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'ko',
    'mageUtils',
    'uiComponent',
    'Magento_Checkout/js/model/payment/method-list',
    'Magento_Checkout/js/model/payment/renderer-list',
    'Magento_Ui/js/core/renderer/layout'
], function (_, ko, utils, Component, paymentMethods, rendererList, layout) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/payment-methods/list',
            visible: paymentMethods().length > 0
        },

        /**
         * Initialize view.
         *
         * @returns {Component} Chainable.
         */
        initialize: function () {
            this._super().initChildren();
            paymentMethods.subscribe(
                function (changes) {
                    _.each(changes, function (change) {
                        if (change.status === 'added') {
                            this.createRenderer(change.value);
                        } else if (change.status === 'deleted') {
                            this.removeRenderer(change.value.code);
                        }
                    }, this);
                }, this, 'arrayChange');

            return this;
        },

        /**
         * Create renders for child payment methods.
         *
         * @returns {Component} Chainable.
         */
        initChildren: function () {
            var self = this;
            _.each(paymentMethods(), function (paymentMethodData) {
                self.createRenderer(paymentMethodData);
            });

            return this;
        },

        /**
         * Create renderer.
         *
         * @param {Object} paymentMethodData
         */
        createRenderer: function (paymentMethodData) {
            var renderer = this.getRendererByType(paymentMethodData.code),
                rendererTemplate,
                rendererComponent,
                templateData;

            if (renderer) {
                templateData = {
                    parentName: this.name,
                    name: paymentMethodData.code
                };
                rendererTemplate = {
                    parent: '${ $.$data.parentName }',
                    name: '${ $.$data.name }',
                    displayArea: 'payment-method-items',
                    component: renderer.component
                };
                rendererComponent = utils.template(rendererTemplate, templateData);
                utils.extend(rendererComponent, {
                    item: paymentMethodData
                });
                layout([rendererComponent]);
            }
        },

        /**
         * Get renderer for payment method type.
         *
         * @param {String} paymentMethodCode
         * @returns {Object}
         */
        getRendererByType: function (paymentMethodCode) {
            var compatibleRenderer;
            _.find(rendererList(), function (renderer) {
                if (renderer.type === paymentMethodCode) {
                    compatibleRenderer = renderer;
                }
            });

            return compatibleRenderer;
        },

        /**
         * Remove view renderer.
         *
         * @param {String} paymentMethodCode
         */
        removeRenderer: function (paymentMethodCode) {
            var items = this.getRegion('payment-method-items');
            _.find(items(), function (value) {
                if (value.item.code === paymentMethodCode) {
                    this.removeChild(value);
                }
            }, this);
        }
    });
});
