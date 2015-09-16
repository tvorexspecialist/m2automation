/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/grid/editing/bulk'
], function (Bulk) {
    'use strict';

    describe('Magento_Ui/js/grid/editing/bulk', function () {
        var bulkObj,
            temp;

        beforeEach(function () {
            bulkObj = new Bulk();
        });
        it('has initObservable', function () {
            expect(bulkObj).toBeDefined();
        });
        it('has apply method', function () {
            spyOn(bulkObj, 'isValid');
            temp = bulkObj.apply();
            expect(bulkObj.isValid).toHaveBeenCalled();
            expect(temp).toBeDefined();
        });
        it('can apply data', function () {
            spyOn(bulkObj, 'getData');
            bulkObj.applyData();
            expect(bulkObj.getData).toHaveBeenCalled();
        });
        it('has updateState method', function () {
            spyOn(bulkObj, 'hasData');
            temp = bulkObj.updateState();
            expect(bulkObj.hasData).toHaveBeenCalled();
            expect(temp).toBeDefined();
        });
    })
});