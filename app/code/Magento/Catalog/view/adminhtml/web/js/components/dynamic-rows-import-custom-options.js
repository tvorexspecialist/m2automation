/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows-grid',
    'underscore',
    'mageUtils'
], function (DynamicRows, _, utils) {
    'use strict';

    var maxId = 0,

    /**
     * Stores max option_id value of the options from recordData once on initialization
     *
     */
    initMaxId = function (data) {
        maxId = data && data.length ? ~~_.max(data, function (record) {
            return ~~record['option_id'];
        })['option_id'] : 0;
    };

    return DynamicRows.extend({
        defaults: {
            mappingSettings: {
                enabled: false,
                distinct: false
            },
            update: true,
            map: {
                'option_id': 'option_id'
            },
            identificationProperty: 'option_id',
            identificationDRProperty: 'option_id'
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            initMaxId(this.recordData());

            return this;
        },

        /** @inheritdoc */
        processingInsertData: function (data) {
            var options = [],
                currentOption;

            if (!data) {
                return;
            }
            data.each(function (item) {
                if (!item.options) {
                    return;
                }
                item.options.each(function (option) {
                    currentOption = utils.copy(option);

                    if (currentOption.hasOwnProperty('sort_order')) {
                        delete currentOption['sort_order'];
                    }
                    currentOption['option_id'] = ++maxId;
                    options.push(currentOption);
                });
            });

            if (!options.length) {
                return;
            }
            this.cacheGridData = options;
            options.each(function (opt) {
                this.mappingValue(opt);
            }, this);

            this.insertData([]);
        },

        /**
         * Set empty array to dataProvider
         */
        clearDataProvider: function () {
            this.source.set(this.dataProvider, []);
        },

        /** @inheritdoc */
        processingAddChild: function (ctx, index, prop) {
            if (ctx && !_.isNumber(ctx['option_id'])) {
                ctx['option_id'] = ++maxId;
            }
            this._super(ctx, index, prop);
        },

        /**
         * Mutes parent method
         */
        updateInsertData: function () {
            return false;
        }
    });
});
