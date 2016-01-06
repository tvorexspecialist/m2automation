/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    './insert',
    'mageUtils',
    'jquery'
], function (Insert, utils, $) {
    'use strict';

    /**
     * Get page actions element.
     *
     * @param {String} elem
     * @param {String} actionsSelector
     * @returns {String}
     */
    function getPageActions(elem, actionsSelector) {
        var $el = $('<div/>').html(elem),
            $wrapper = $('<div/>').addClass('page-main-actions');

        return $wrapper.html($el.find(actionsSelector)).get(0).outerHTML;
    }

    /**
     * Return element without page actions toolbar
     *
     * @param {String} elem
     * @param {String} actionsSelector
     * @returns {String}
     */
    function removePageActions(elem, actionsSelector) {
        var $el = $('<div/>').html(elem);

        $el.find(actionsSelector).remove();

        return $el.html();
    }

    return Insert.extend({
        defaults: {
            externalForm: '${ $.ns }.${ $.ns }',
            pageActionsSelector: '.page-actions',
            exports: {
                prefix: '${ $.externalForm }:selectorPrefix'
            },
            imports: {
                toolbarSection: '${ $.toolbarContainer }:toolbarSection',
                prefix: '${ $.toolbarContainer }:rootSelector'
            },
            settings: {
                ajax: {
                    ajaxSave: true,
                    exports: {
                        ajaxSave: '${ $.externalForm }:ajaxSave'
                    },
                    imports: {
                        _responseStatus: '${ $.externalForm }:responseStatus',
                        responseData: '${ $.externalForm }:responseData'
                    },
                    listens: {
                        _responseStatus: 'onResponseStatus'
                    }
                }
            }
        },

        /** @inheritdoc */
        initConfig: function (config) {
            var defaults = this.constructor.defaults;

            utils.extend(defaults, defaults.settings[config.formSubmitType] || {});

            return this._super();
        },

        /** @inheritdoc */
        onRender: function (data) {
            var actions = getPageActions(data, this.pageActionsSelector);

            if (!data.length) {
                return this;
            }
            data = removePageActions(data, this.pageActionsSelector);
            this.renderActions(actions);
            this._super(data);
        },

        /**
         * Insert actions in toolbar.
         *
         * @param {String} elem
         */
        renderActions: function (elem) {
            $(this.toolbarSection).append(elem);
        },

        /**
         * Convert status to boolean.
         * @param {String} status
         */
        onResponseStatus: function (status) {
            this.set('responseStatus', status === 'success');
        },

        /**
         * Reset external form data.
         */
        resetForm: function () {
            this.externalSource().trigger('data.reset');
        }
    });
});
