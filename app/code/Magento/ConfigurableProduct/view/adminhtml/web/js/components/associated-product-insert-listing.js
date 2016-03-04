/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/insert-listing'
], function (insertListing) {
    'use strict';

    return insertListing.extend({
        defaults: {
            gridInitialized: false,
            paramsUpdated: false,
            showMassActionColumn: true,
            dataScopeAssociatedProduct: 'data.associated_product_ids',
            modules: {
                productsProvider: '${ $.productsProvider }',
                productsColumns: '${ $.productsColumns }',
                productsMassAction: '${ $.productsMassAction }'
            },
            exports: {
                externalProviderParams: '${ $.externalProvider }:params'
            },
            listens: {
                '${ $.externalProvider }:params': '_setFilters _setVisibilityMassActionColumn',
                '${ $.productsProvider }:data': '_handleManualGridOpening',
                '${ $.productsMassAction }:selected': '_handleManualGridSelect'
            }
        },

        getUsedProductIds: function () {
            return this.source.get(this.dataScopeAssociatedProduct);
        },

        /**
         * Request for render content.
         *
         * @returns {Object}
         */
        doRender: function (showMassActionColumn) {
            this.showMassActionColumn = showMassActionColumn;
            if (this.gridInitialized) {
                this.paramsUpdated = false;
                this._setFilters(this.externalProviderParams);
                this._setVisibilityMassActionColumn();
            }

            return this.render();
        },

        _setVisibilityMassActionColumn: function () {
            this.productsMassAction(function (massActionComponent) {
                this.productsColumns().elems().each(function (rowElement) {
                    rowElement.disableAction = this.showMassActionColumn;
                }, this);
                massActionComponent.visible = this.showMassActionColumn;
            }.bind(this));
        },

        _setFilters: function (params) {
            if (!this.paramsUpdated) {
                this.gridInitialized = true;
                this.paramsUpdated = true;

                var filter = {},
                    attrCodes = this._getAttributesCodes();

                filter['entity_id'] = {
                    'condition_type': 'nin', value: this.getUsedProductIds()
                };
                attrCodes.each(function (code) {
                    filter[code] = {
                        'condition_type': 'notnull'
                    };
                });

                params['attributes_codes'] = attrCodes;

                this.set('externalProviderParams', params);
                this.set('externalFiltersModifier', filter);
            }
        },

        _getAttributesCodes: function () {
            var attrCodes = this.source.get('data.attribute_codes');

            return attrCodes ? attrCodes : [];
        },

        _getProductVariations: function () {
            var matrix = this.source.get('data.configurable-matrix');

            return matrix ? matrix : [];
        },

        /**
         * Handle manual grid after opening
         * @private
         */
        _handleManualGridOpening: function (data) {
            if (data.items.length) {
                this.productsColumns().elems().each(function (rowElement) {
                    rowElement.disableAction = true;
                });

                this._disableRows(data.items);
            }
        },

        /**
         * @private
         */
        _handleManualGridSelect: function (selected) {
            var selectedRows = _.filter(this.productsProvider().data.items, function (row) {
                    return selected.indexOf(row['entity_id']) != -1;
                }),
                selectedVariationKeys = _.values(this._getVariationKeyMap(selectedRows));
            this._disableRows(this.productsProvider().data.items, selectedVariationKeys, selected);
        },

        /**
         * Disable rows in grid for products with the same variation key
         *
         * @param {Array} items
         * @param {Array} selectedVariationKeys
         * @param {Array} selected
         * @private
         */
        _disableRows: function (items, selectedVariationKeys, selected) {
            selectedVariationKeys = selectedVariationKeys === undefined ? [] : selectedVariationKeys;
            selected = selected === undefined ? [] : selected;
            this.productsMassAction(function (massaction) {
                var configurableVariationKeys = _.union(
                    selectedVariationKeys,
                    _.pluck(this._getProductVariations(), 'variationKey')
                    ),
                    variationKeyMap = this._getVariationKeyMap(items),
                    rowsForDisable = _.keys(_.pick(
                        variationKeyMap,
                        function (variationKey) {
                            return configurableVariationKeys.indexOf(variationKey) != -1;
                        }
                    ));

                massaction.disabled(_.difference(rowsForDisable, selected));
            }.bind(this));
        },

        /**
         * Get variation key map used in manual grid.
         *
         * @param items
         * @returns {Array} [{entity_id: variation-key}, ...]
         * @private
         */
        _getVariationKeyMap: function (items) {
            var variationKeyMap = {};

            _.each(items, function (row) {
                variationKeyMap[row['entity_id']] = _.values(
                    _.pick(row, this._getAttributesCodes())
                ).sort().join('-');

            }, this);

            return variationKeyMap;
        }
    });
});