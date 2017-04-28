/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "underscore",
    "uiElement",
    'Magento_Customer/js/customer-data'
], function (_, Element, customerData) {
    "use strict";

    return Element.extend({
        initialize: function () {
            this._super();
            this.process(customerData);
        },

        /**
         * Process all rules in loop, each rule can invalidate some sections in customer data
         *
         * @param {Object} customerData
         */
        process: function (customerData) {
            _.each(this.invalidationRules, function (rule, ruleName) {
                _.each(rule, function (ruleArgs, rulePath) {
                    require([rulePath], this.initRule.bind(this));
                }, this);
            }, this);
        },

        initRule: function () {
            var rule = new Rule(ruleArgs);

            if (!_.isFunction(rule.process)) {
                throw new Error("Rule " + ruleName + " should implement invalidationProcessor interface");
            }
            rule.process(customerData);
        }
    });
});
