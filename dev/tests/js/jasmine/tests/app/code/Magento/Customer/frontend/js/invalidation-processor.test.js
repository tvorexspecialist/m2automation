define([
    'squire'
], function (Squire) {
    'use strict';

    var injector = new Squire(),
        mocks = {
            'Magento_Customer/js/customer-data': {
                get: jasmine.createSpy().and.returnValue({})
            }
        },
        processor;

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Customer/js/invalidation-processor'], function (Constr) {
            processor = new Constr({
                name: 'processor'
            });
            done();
        });
    });

    describe('Magento_Customer/js/invalidation-processor', function () {

        describe('"process" method', function () {
            it('record status is 1', function () {
                var requireTmp = require;

                processor.invalidationRules = {
                    'website-rule': {
                        'Magento_Customer/js/invalidation-rules/website-rule': {
                            process: jasmine.createSpy()
                        }
                    }
                };

                require = jasmine.createSpy();
                processor.process();

                expect(require).toHaveBeenCalled();
                require = requireTmp;
            });
        });
    });
});
