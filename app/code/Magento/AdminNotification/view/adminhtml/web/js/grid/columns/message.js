/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/columns/column',
    'underscore'
], function (Column, _) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Magento_AdminNotification/grid/cells/message',
            messageIndex: 'text',
            fieldClass: {
                message: true
            },
            statusMap: {
                0: 'success',
                1: 'progress',
                2: 'info',
                3: 'error'
            }
        },

        /** @inheritdoc */
        getLabel: function (record) {
            return record[this.messageIndex];
        },

        /** @inheritdoc */
        getFieldClass: function ($row) {
            var status = this.statusMap[$row.status] || 'warning',
                result = {};

            result['message-' + status] = true;
            result = _.extend({}, this.fieldClass, result);

            return result;
        }
    });
});
