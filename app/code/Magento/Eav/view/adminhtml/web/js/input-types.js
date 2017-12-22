/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    return function (config) {
        // disabled select only
        $('select#frontend_input:disabled').each(function () {
            var select = $(this),
                currentValue = select.find('option:selected').val(),
                compatibleTypes = config.inputTypes,
                enabledTypes = [], iterator,
                warning = $('<label>')
                    .hide()
                    .text($.mage.__('These changes affect all related products.'))
                    .addClass('mage-error')
                    .attr({
                        generated: true, for: select.attr('id')
                    }),

            /**
             * Toggle hint about changes types
             */
            toggleWarning = function () {
                if (select.find('option:selected').val() === currentValue) {
                    warning.hide();
                } else {
                    warning.show();
                }
            },

            /**
             * Remove unsupported options
             */
            removeOption = function () {
                if (!~enabledTypes.indexOf($(this).val())) {
                    $(this).remove();
                }
            };

            // find enabled types for switching dor current input type
            for (iterator = 0; iterator < compatibleTypes.length; iterator++) {
                if (compatibleTypes[iterator].indexOf(currentValue) >= 0) {
                    enabledTypes = compatibleTypes[iterator];
                }
            }

            // Check current type (allow only compatible types)
            if (!~enabledTypes.indexOf(currentValue)) {
                return;
            }

            // Enable select and keep only available options (all other will be removed)
            select.removeAttr('disabled').find('option').each(removeOption);

            // Add warning on page and event for show/hide it
            select.after(warning).on('change', toggleWarning);
        });
    };
});
