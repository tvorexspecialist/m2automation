/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/single-checkbox',
    'uiRegistry'
], function (Checkbox, rg) {
    'use strict';

    return Checkbox.extend({
        defaults: {
            inputCheckBoxName: '',
            value: '',
            prefixElementName: '',
            parentDynamicRowName: 'visual_swatch'
        },

        /**
         * Parses options and merges the result with instance
         *
         * @param  {Object} config
         * @returns {Object} Chainable.
         */
        initConfig: function (config) {
            this._super();

            this.configureDataScope();

            return this;
        },

        configureDataScope: function () {
            var recordId,
                value;

            recordId = this.parentName.split('.').last();
            value = this.prefixElementName + recordId;

            this.dataScope = 'data.' + this.inputCheckBoxName;
            this.inputName = this.dataScopeToHtmlArray(this.inputCheckBoxName);

            this.initialValue = value;

            this.links.value = this.provider + ':' + this.dataScope;
        },

        dataScopeToHtmlArray: function (dataScopeString) {
            var dataScopeArray, dataScope, reduceFunction;

            reduceFunction = function (prev, curr) {
                return prev + '[' + curr + ']';
            };

            dataScopeArray = dataScopeString.split('.');

            dataScope = dataScopeArray.shift();
            dataScope += dataScopeArray.reduce(reduceFunction, '');

            return dataScope;
        }
    });
});
