/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint max-nested-callbacks: 0 */
define(['squire'], function (Squire) {
    'use strict';

    var injector = new Squire(),
        mocks = {
            'Magento_Checkout/js/model/totals': {
                /**
                 * @returns {*}
                 */
                totals: function () {
                    return {
                        'items_qty': 13.0765
                    };
                },

                /**
                 * @returns {*}
                 */
                getItems: function () {
                    var observable = function () {};

                    observable.subscribe = function () {};

                    return observable;
                }
            },
            'Magento_Checkout/js/model/quote': {
                /**
                 * @returns {Boolean}
                 */
                isVirtual: function () {

                    return false;
                }
            },
            'Magento_Checkout/js/model/step-navigator': {
                /**
                 * @returns {Boolean}
                 */
                isProcessed: function () {

                    return true;
                }
            }
        },
        obj;

    window.checkoutConfig = {
        maxCartItemsToDisplay: 1,
        cartUrl: 'url/to/cart'
    };

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Checkout/js/view/summary/cart-items'], function (Constr) {
            obj = new Constr({
                provider: 'provName',
                name: '',
                index: '',
                itemsTestStorage: [],

                /**
                 * @param {*} items
                 */
                items: function (items) {
                    this.itemsTestStorage = items;
                }
            });
            done();
        });
    });

    describe('Magento_Checkout/js/view/summary/cart-items', function () {
        describe('"getItemsQty" method', function () {
            it('Check for return value.', function () {
                expect(obj.getItemsQty()).toBe(13.0765);
            });
        });

        describe('"isItemsBlockExpanded" method', function () {
            it('Check for return value.', function () {
                expect(obj.isItemsBlockExpanded()).toBeTruthy();
            });
        });

        describe('"setItems" method', function () {
            it('Check for return value.', function () {
                var items = [
                    {
                        itemId: 1
                    },
                    {
                        itemId: 2
                    }
                    ],
                    expectedResult = JSON.stringify([
                        {
                            itemId: 2
                        }
                    ]);

                expect(obj.setItems(items)).toBeUndefined();
                expect(JSON.stringify(obj.itemsTestStorage)).toBe(expectedResult);
            });
        });
    });
});
