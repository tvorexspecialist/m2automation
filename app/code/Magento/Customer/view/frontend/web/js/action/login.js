/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'mage/storage',
        'Magento_Ui/js/model/messageList',
        'Magento_Customer/js/customer-data'
    ],
    function($, storage, messageList, customerData) {
        'use strict';
        var callbacks = [],
            action = function(loginData, redirectUrl) {
                return storage.post(
                    '/customer/ajax/login',
                    JSON.stringify(loginData)
                ).done(function (response) {
                    if (response.errors) {
                        messageList.addErrorMessage(response);
                        callbacks.forEach(function(callback) {
                            callback(loginData);
                        });
                    } else {
                        callbacks.forEach(function(callback) {
                            callback(loginData);
                        });
                        customerData.invalidate(['customer']);
                        if (redirectUrl) {
                            window.location.href = redirectUrl;
                        } else {
                            location.reload();
                        }
                    }
                }).fail(function () {
                    messageList.addErrorMessage({'message': 'Could not authenticate. Please try again later'});
                    callbacks.forEach(function(callback) {
                        callback(loginData);
                    });
                });
            };

        action.registerLoginCallback = function(callback) {
            callbacks.push(callback);
        };

        return action;
    }
);
