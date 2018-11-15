/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/form/element/region'
], function (Region) {
    'use strict';

    return Region.extend({
        defaults: {
            regionScope: 'data.region'
        },

        setDifferedFromDefault: function (value) {
            this._super();

            if (parseFloat(value)) {
                this.source.set(this.regionScope, this.indexedOptions[value].label);
            }
        }
    });
});
