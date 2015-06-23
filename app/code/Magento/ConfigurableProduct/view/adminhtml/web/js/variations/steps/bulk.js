/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/lib/collapsible'
], function (Component, $, ko, _, Collapsible) {
    'use strict';

    //TODO: where unique id for options
    var viewModel;
    viewModel = Component.extend({
        attributes: ko.observableArray([]),
        sections: ko.observableArray([
            {
                label: 'images',
                type: ko.observable('none'),
                value: ko.observable(),
                attribute: ko.observable()
            },
            {
                label: 'pricing',
                type: ko.observable('none'),
                value: ko.observable(),
                attribute: ko.observable()
            },
            {
                label: 'inventory',
                type: ko.observable('none'),
                value: ko.observable(),
                attribute: ko.observable()
            }
        ]),
        render: function (wizard) {
            viewModel.prototype.attributes(wizard.data.attributesValues);
            viewModel.prototype.attributes.each(function (attribute) {
                attribute.options.each(function (option) {
                    option.sections = ko.observable({images:'',pricing:'',inventory:''});
                });
            });
        },
        force: function (wizard) {
        },
        back: function (wizard) {
        }
    });
    return viewModel;
});
