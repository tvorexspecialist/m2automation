/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'jquery',
    'Magento_Ui/js/form/element/image-uploader',
    'mage/adminhtml/browser'
], function ($, ImageUploader, browser) {
    'use strict';

    describe('Magento_Ui/js/form/element/file-uploader', function () {
        var component;

        beforeEach(function () {
            component = new ImageUploader({
                dataScope: 'abstract'
            });
        });

        xdescribe('initConfig method', function () {
            it('sets mediaGalleryUid', function () {
                component.initConfig();
                expect(component.mediaGalleryUid).toBeDefined();
            });
        });

        xdescribe('addFileFromMediaGallery method', function () {
            it('adds file', function () {
                var $el = $('div');

                spyOn(component, 'addFile');

                $el.data({
                    'size': 1024,
                    'mime-type': 'image/png'
                });

                $el.val('/pub/media/something.png');

                component.addFileFromMediaGallery(null, {
                    target: $el
                });

                expect(component.addFile).toHaveBeenCalledWith({
                    type: 'image/png',
                    name: 'something.png',
                    url: '/pub/media/something.png',
                    size: 1024
                });
            });
        });

        xdescribe('openMediaBrowserDialog method', function () {
            it('opens browser dialog', function () {
                var $el = $('div');

                $el.attr('id', 'theTargetId');

                component.mediaGallery = {
                    openDialogUrl: 'http://example.com/',
                    openDialogTitle: 'Hello world',
                    storeId: 3
                };

                spyOn(browser, 'openDialog');

                component.openMediaBrowserDialog(null, {
                    target: $el
                });

                expect(browser.openDialog).toHaveBeenCalledWith(
                    'http://example.com/target_element_id/theTargetId/store/3/type/image/?isAjax=true',
                    null,
                    null,
                    'Hello world'
                );
            });
        });
    });
});
