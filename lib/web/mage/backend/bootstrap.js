/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global FORM_KEY */
define([
    'jquery',
    'mage/apply/main',
    'Magento_Ui/js/lib/knockout/bootstrap',
    'mage/mage'
], function ($, mage) {
    'use strict';

    var bootstrap;

    $.ajaxSetup({
        /*
         * @type {string}
         */
        type: 'POST',

        /**
         * Ajax before send callback.
         *
         * @param {Object} jqXHR - The jQuery XMLHttpRequest object returned by $.ajax()
         * @param {Object} settings
         */
        beforeSend: function (jqXHR, settings) {
            var formKey = typeof FORM_KEY !== 'undefined' ? FORM_KEY : null;

            if (!settings.url.match(new RegExp('[?&]isAjax=true',''))) {
                settings.url = settings.url.match(
                    new RegExp('\\?', 'g')) ?
                    settings.url + '&isAjax=true' :
                    settings.url + '?isAjax=true';
            }

            if (!settings.data) {
                settings.data = {
                    'form_key': formKey
                };
            } else if ($.type(settings.data) === 'string' &&
                settings.data.indexOf('form_key=') === -1) {
                settings.data += '&' + $.param({
                    'form_key': formKey
                });
            } else if ($.isPlainObject(settings.data) && !settings.data['form_key']) {
                settings.data['form_key'] = formKey;
            }
        },

        /**
         * Ajax complete callback.
         *
         * @param {Object} jqXHR - The jQuery XMLHttpRequest object returned by $.ajax()
         */
        complete: function (jqXHR) {
            var jsonObject;

            if (jqXHR.readyState === 4) {
                try {
                    jsonObject = $.parseJSON(jqXHR.responseText);

                    if (jsonObject.ajaxExpired && jsonObject.ajaxRedirect) { //eslint-disable-line max-depth
                        window.location.replace(jsonObject.ajaxRedirect);
                    }
                } catch (e) {}
            }
        }
    });

    /**
     * Bootstrap application.
     */
    bootstrap = function () {
        /**
         * Init all components defined via data-mage-init attribute
         * and subscribe init action on contentUpdated event
         */
        mage.apply();

        /*
         * Initialization of notification widget
         */
        $('body').mage('notification');
    };

    $(bootstrap);
});
