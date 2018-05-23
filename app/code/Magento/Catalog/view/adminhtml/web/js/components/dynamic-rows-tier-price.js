/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function (_, DynamicRows) {
    'use strict';

    /**
     * @deprecated Parent method contains labels sorting.
     * @see Magento_Ui/js/dynamic-rows/dynamic-rows
     */
    return DynamicRows.extend({

        /**
         * Init header elements
         */
        initHeader: function () {
            var labels;

            this._super();
            labels = _.clone(this.labels());
            labels = _.sortBy(labels, function (label) {
                return label.sortOrder;
            });

            this.labels(labels);
        },

        /**
         * Change page
         *
         * @param {Number} page - current page
         */
        changePage: function (page) {
            this.clear();

            if (page === 1 && !this.recordData().length) {
                return false;
            }

            if (~~page > this.pages()) {
                this.currentPage(this.pages());

                return false;
            } else if (~~page < 1) {
                this.currentPage(1);

                return false;
            }

            this.initChildren();

            return true;
        }
    });
});
