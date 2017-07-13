/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'Magento_Ui/js/dynamic-rows/dynamic-rows'
], function (DynamicRows) {
    'use strict';

    var model,

        /**
         * @param {Number} index
         * @returns {Object}
         * @constructor
         */
        ElementMock = function (index) {
            return {
                /**
                 * @return void
                 */
                destroy: function () {},
                index: index
            };
        };

    /**
     * Run before each test method
     *
     * @return void
     */
    beforeEach(function () {
        model = new DynamicRows({});
    });

    /**
     * Testing changePage and delete record methods
     *
     * @return void
     */
    describe('Magento_Ui/js/dynamic-rows/dynamic-rows', function () {
        it('Calls "setMaxPosition" method without position.', function () {
            model.maxPosition = 0;
            model.setMaxPosition(false);

            expect(model.maxPosition).toEqual(1);
        });

        it('Calls "setMaxPosition" method with position 0.', function () {
            var elem = {};

            model.checkMaxPosition = jasmine.createSpy();
            model.sort = jasmine.createSpy();
            model.setMaxPosition(0, elem);

            expect(model.checkMaxPosition).toHaveBeenCalledWith(0);
            expect(model.sort).toHaveBeenCalledWith(0, elem);
        });

        it('changePage without Records', function () {
            /**
             * Mock function which return length of record data
             *
             * @returns {Object}
             */
            model.recordData = function () {
                return {
                    length: 0
                };
            };

            expect(model.changePage(1)).toBeFalsy();
        });

        it('changePage with Fake Page', function () {
            /**
             * Mock function, which return the number of pages
             *
             * @returns {Number}
             */
            model.pages = function () {
                return 3;
            };

            expect(model.changePage(4)).toBeFalsy();
        });

        it('changePage', function () {
            model.startIndex = 0;
            model.pageSize = 3;
            model.relatedData = [
                {
                    'a': 'b'
                },
                {
                    'b': 'c'
                },
                {
                    'v': 'g'
                }
            ];

            /**
             * @returns {Number}
             */
            model.pages = function () {
                return 3;
            };
            model.changePage(2);

            expect(model.templates.record.recordId).toBe(2);//last record number is 3
        });

        it('deleteRecord with Delete Property', function () {
            var elems,
                recordInstanceMock = new ElementMock(1),
                elem2 = new ElementMock(2);

            spyOn(recordInstanceMock, 'destroy');
            model.recordData({
                1: {}
            });
            elems = [
                recordInstanceMock,
                elem2
            ];
            model.elems(elems);
            model.deleteProperty = true;
            model.deleteRecord(1, 1);
            expect(model.recordData()).toEqual([]);
        });
    });
});
