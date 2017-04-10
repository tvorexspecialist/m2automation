/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    './entry'
], function (LogEntry) {
    'use strict';

    return {
        /**
         * @param {String} message
         * @param {Number} level
         * @param {Object} [messageData]
         * @returns {LogEntry}
         */
        createEntry: function (message, level, messageData) {
            return new LogEntry(message, level, messageData);
        }
    };
});
