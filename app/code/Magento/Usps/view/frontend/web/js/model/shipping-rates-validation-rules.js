/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([], function () {
    'use strict';

    return {
        /**
         * @return {Object}
         */
        getRules: function () {
            return {
                'country_id': {
                    'required': true
                },
                'postcode': {
                    'required': false
                }
            };
        }
    };
});
