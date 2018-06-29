/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require([
    'jquery'
], function ($) {
    'use strict';

    $('body').on('catalogCategoryAddToCartRedirect', function (event, data) {
        $(data.form).find('select[name*="super"]').each(function (index, item) {
            var $item = $(item);

            data.redirectParameters.push($item.attr('data-attribute-code') + '=' + $item.val());
        });
    });
});
