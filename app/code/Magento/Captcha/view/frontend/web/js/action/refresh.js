/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global define*/
define(
    ['mage/storage'],
    function(storage) {
        "use strict";
        return function(refreshUrl, formId, imageSource) {
            return storage.post(
                refreshUrl,
                JSON.stringify({'formId': formId}),
                false
            ).done(
                function (response) {
                    if (response.imgSrc) {
                        imageSource(response.imgSrc);
                    }
                }
            );
        };
    }
);
