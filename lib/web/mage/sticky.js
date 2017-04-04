/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.sticky', {
        options: {
            container: '',
            spacingTop: 0,
            offsetTop: 0,
            stickyClass: '_sticky'
        },

        /**
         * Bind handlers to scroll event
         * @private
         */
        _create: function () {
            $(window).on({
                'scroll': $.proxy(this._stick, this),
                'resize': $.proxy(this.reset, this)
            });

            this.element.on('dimensionsChanged', $.proxy(this.reset, this));

            this.reset();
        },

        /**
         * float Block on windowScroll
         * @private
         */
        _stick: function () {
            var offset,
                isStatic;

            isStatic = this.element.css('position') === 'static';

            if (!isStatic && this.element.is(':visible')) {
                offset = $(document).scrollTop() - this.parentOffset;

                if (typeof this.options.spacingTop === 'function') {
                    offset += this.options.spacingTop();
                } else {
                    offset += this.options.spacingTop;
                }

                offset = Math.max(0, Math.min(offset, this.maxOffset));

                var stuck = this.element.hasClass(this.options.stickyClass);
                if (offset && this.options.offsetTop && !stuck) {
                    var offsetTop = 0;
                    if (typeof this.options.offsetTop === 'function') {
                        offsetTop = this.options.offsetTop();
                    } else {
                        offsetTop = this.options.offsetTop;
                    }

                    if (offset < offsetTop) {
                        offset = 0;
                    }
                }

                this.element
                    .toggleClass(this.options.stickyClass, (offset > 0))
                    .css('top', offset);
            }
        },

        /**
         * Defines maximum offset value of the element.
         * @private
         */
        _calculateDimens: function () {
            var $parent         = this.element.parent(),
                topMargin       = parseInt(this.element.css('margin-top'), 10),
                parentHeight    = $parent.height() - topMargin,
                height          = this.element.innerHeight(),
                maxScroll       = document.body.offsetHeight - window.innerHeight;

            if (this.options.container.length > 0) {
                maxScroll = $(this.options.container).height();
            }

            this.parentOffset   = $parent.offset().top + topMargin;
            this.maxOffset      = maxScroll - this.parentOffset;

            if (this.maxOffset + height >= parentHeight) {
                this.maxOffset = parentHeight - height;
            }

            return this;
        },

        /**
         * Facade method that palces sticky element where it should be.
         */
        reset: function () {
            this._calculateDimens()
                ._stick();
        }
    });

    return $.mage.sticky;
});
