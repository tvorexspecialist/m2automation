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
            externalFormName: '${ $.ns }.${ $.ns }',
            pageActionsSelector: '.page-actions',
            exports: {
                prefix: '${ $.externalFormName }:selectorPrefix'
            },
            imports: {
                toolbarSection: '${ $.toolbarContainer }:toolbarSection',
                prefix: '${ $.toolbarContainer }:rootSelector'
            },
            settings: {
                ajax: {
                    ajaxSave: true,
                    exports: {
                        ajaxSave: '${ $.externalFormName }:ajaxSave'
                    },
                    imports: {
                        responseStatus: '${ $.externalFormName }:responseStatus',
                        responseData: '${ $.externalFormName }:responseData'
                    }
                }
            },
            modules: {
                externalForm: '${ $.externalFormName }'
            }
        },

        /** @inheritdoc */
        initObservable: function () {
            return this._super()
                .observe('responseStatus');
        },

        /** @inheritdoc */
        initConfig: function (config) {
            var defaults = this.constructor.defaults;

            utils.extend(defaults, defaults.settings[config.formSubmitType] || {});

            return this._super();
        },

        /** @inheritdoc*/
        destroyInserted: function () {
            if (this.isRendered) {
                this.externalForm().destroy();
                this.removeActions();
                this.responseStatus(undefined);
                this.responseData = {};
            }

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
            this.formHeader = $(elem);
            $(this.toolbarSection).append(this.formHeader);
        },

        /**
         * Remove actions tollbar.
         */
        removeActions: function () {
            $(this.formHeader).remove();
            this.formHeader = $();
        },

        /**
         * Reset external form data and response status.
         */
        resetForm: function () {
            if (this.externalSource()) {
                this.externalSource().trigger('data.reset');
                this.responseStatus(undefined);
            }
        }
    });
});
