/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global Variables, updateElementAtCursor */
define([
    'jquery',
    'mage/translate',
    'wysiwygAdapter',
    'uiRegistry',
    'ko',
    'mage/apply/main',
    'mageUtils',
    'jquery/ui',
    'prototype'
], function (jQuery, $t, wysiwyg, registry, ko, mageApply, utils) {
    'use strict';

    window.Variables = {
        textareaElementId: null,
        variablesContent: null,
        dialogWindow: null,
        dialogWindowId: 'variables-chooser',
        overlayShowEffectOptions: null,
        overlayHideEffectOptions: null,
        insertFunction: 'Variables.insertVariable',

        /**
         * @param {*} textareaElementId
         * @param {Function} insertFunction
         */
        init: function (textareaElementId, insertFunction) {
            if ($(textareaElementId)) {
                this.textareaElementId = textareaElementId;
            }

            if (insertFunction) {
                this.insertFunction = insertFunction;
            }
        },

        /**
         * reset data.
         */
        resetData: function () {
            this.variablesContent = null;
            this.dialogWindow = null;
        },

        /**
         * @param {Object} variables
         */
        openVariableChooser: function (variables) {
            if (variables) {
                this.openDialogWindow(variables);
            }
        },

        getButtonHtml: function () {

        },

        /**
         * Close dialog window.
         */
        closeDialogWindow: function () {
            jQuery('#' + this.dialogWindowId).modal('closeModal');
        },

        /**
         * Init ui component grid on the form
         *
         * @return void
         */
        initUiGrid: function () {
            mageApply.apply(document.getElementById(this.dialogWindow));
            jQuery('#' + this.dialogWindowId).applyBindings();
        },

        /**
         * @param {*} variablesContent
         */
        openDialogWindow: function (variablesContent, variableCode) {
            var html = utils.copy(variablesContent);

            jQuery('<div id="' + this.dialogWindowId + '">' + html + '</div>').modal({
                title: variableCode === undefined ? $t('Insert Variable...') : $t('Edit variable...'),
                type: 'slide',
                buttons: []
            });

            jQuery('#' + this.dialogWindowId).modal('openModal');

            if (typeof variableCode !== undefined) {
                //@TODO: workaround should be replaced
                registry.get('variables_modal.variables_modal.variables.variable_selector', function (radioSelect) {
                    radioSelect.selectedVariableCode(variableCode);
                });
            }
        },

        /**
         * @param {String} varValue
         * @param {*} varLabel
         * @return {String}
         */
        prepareVariableRow: function (varValue, varLabel) {
            var value = varValue.replace(/"/g, '&quot;').replace(/'/g, '\\&#39;'),
                content = '<a href="#" onclick="' +
                    this.insertFunction +
                    '(\'' +
                    value +
                    '\');return false;">' +
                    varLabel +
                    '</a>';

            return content;
        },

        /**
         * @param {*} value
         */
        insertVariable: function (value) {
            var windowId = this.dialogWindowId,
                textareaElm, scrollPos;

            jQuery('#' + windowId).modal('closeModal');
            textareaElm = $(this.textareaElementId);

            if (typeof wysiwyg != 'undefined' && wysiwyg.activeEditor()) {
                wysiwyg.activeEditor().execCommand('mceInsertContent', false,
                    value);
            } else if (textareaElm) {
                scrollPos = textareaElm.scrollTop;
                updateElementAtCursor(textareaElm, value);
                textareaElm.focus();
                textareaElm.scrollTop = scrollPos;
                jQuery(textareaElm).change();
                textareaElm = null;
            }

            return;
        }
    };

    window.MagentovariablePlugin = {
        editor: null,
        variables: null,
        textareaId: null,

        /**
         * @param {*} editor
         */
        setEditor: function (editor) {
            this.editor = editor;
        },

        /**
         * @param {String} url
         * @param {*} textareaId
         *
         */
        loadChooser: function (url, textareaId, variableCode) {
            this.textareaId = textareaId;

            if (this.variablesContent == null) {
                new Ajax.Request(url, {
                    parameters: {},
                    onComplete: function (transport) {
                        Variables.init(null, 'MagentovariablePlugin.insertVariable');
                        this.variablesContent = transport.responseText;
                        Variables.openDialogWindow(this.variablesContent, variableCode);
                        Variables.initUiGrid();
                    }.bind(this)
                });
            } else {
                Variables.openDialogWindow(this.variablesContent, variableCode);
            }

            return;
        },

        /**
         * @param {*} value
         */
        insertVariable: function (value) {
            if (this.textareaId) {
                Variables.init(this.textareaId);
                Variables.insertVariable(value);
            } else {
                Variables.closeDialogWindow();
                Variables.insertVariable(value);
            }

            return;
        }
    };
});
