/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'underscore',
    'uiLayout',
    'uiComponent'
], function (ko, _, layout, Component) {
    'use strict';

    /**
     * Returns closest existing page number to page argument
     * @param {Number} value
     * @param {Number} max
     * @returns {Number} closest existing page number
     */
    function getInRange(value, max) {
        return Math.min(Math.max(1, value), max);
    }

    return Component.extend({
        defaults: {
            template: 'ui/grid/paging/paging',
            pageSize: 20,
            current: 1,
            selectProvider: '',

            sizesConfig: {
                component: 'Magento_Ui/js/grid/paging/sizes',
                name: '${ $.names }_sizes'
            },

            imports: {
                pageSize: '${ $.sizesConfig.name }:value',
                totalSelected: '${ $.selectProvider }:totalSelected',
                totalRecords: '${ $.provider }:data.totalRecords'
            },

            exports: {
                pageSize: '${ $.provider }:params.paging.pageSize',
                current: '${ $.provider }:params.paging.current',
                pages: '${ $.provider }:data.pages'
            },

            listens: {
                'pages': 'onPagesChange',
                'pageSize totalRecords': 'countPages',
                '${ $.provider }:params.filters': 'goFirst'
            },

            modules: {
                sizes: '${ $.sizesConfig.name }'
            }
        },

        /**
         * Initializes paging component.
         *
         * @returns {Paging} Chainable.
         */
        initialize: function () {
            this._super()
                .initSizes()
                .countPages();

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Paging} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe([
                    'totalSelected',
                    'totalRecords',
                    'pageSize',
                    'current',
                    'pages',
                    'options'
                ]);

            this._current = ko.pureComputed({
                read: function () {
                    return +this.current();
                },

                /**
                 * Validates page change according to user's input.
                 * Sets current observable to result of validation.
                 * Calls reload method then.
                 */
                write: function (value) {
                    var valid;

                    value = +value;
                    valid = !isNaN(value) ? getInRange(value, this.pages()) : 1;

                    this.current(valid);
                    this._current.notifySubscribers(value);
                },

                owner: this
            });

            return this;
        },

        /**
         * Initializes sizes component.
         *
         * @returns {Paging} Chainable.
         */
        initSizes: function () {
            _.extend(this.sizesConfig, {
                options: this.options,
                pageSize: this.pageSize
            });

            layout([this.sizesConfig]);

            return this;
        },

        /**
         * Goes to the first page.
         *
         * @returns {Paging} Chainable.
         */
        goFirst: function () {
            this.current(1);

            return this;
        },

        /**
         * Goes to the last page.
         *
         * @returns {Paging} Chainable.
         */
        goLast: function () {
            this.current(this.pages());

            return this;
        },

        /**
         * Increments current page value.
         *
         * @returns {Paging} Chainable.
         */
        next: function () {
            this.current(this.current() + 1);

            return this;
        },

        /**
         * Decrements current page value.
         *
         * @returns {Paging} Chainable.
         */
        prev: function () {
            this.current(this.current() - 1);

            return this;
        },

        /**
         * Checks if current page is the first page.
         *
         * @returns {Boolean}
         */
        isFirst: function () {
            return this.current() === 1;
        },

        /**
         * Checks if current page is the last page.
         *
         * @returns {Boolean}
         */
        isLast: function () {
            return this.current() === this.pages();
        },

        /**
         * Calculates number of pages.
         */
        countPages: function () {
            var pages = Math.ceil(this.totalRecords() / this.pageSize());

            this.pages(pages || 1);
        },

        /**
         * Listens changes of the 'pages' property.
         * Might change current page if its' value
         * is greater than total amount of pages.
         *
         * @param {Number} pages - Total amount of pages.
         */
        onPagesChange: function (pages) {
            var current = this.current;

            current(getInRange(current(), pages));
        }
    });
});
