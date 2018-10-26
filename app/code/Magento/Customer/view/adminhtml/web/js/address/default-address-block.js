/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiElement'
], function($, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Customer/default-address'
        }
    });
});
