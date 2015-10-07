/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require(['jquery', 'jquery/ui', 'catalogGallery'], function ($) {
    'use strict';

    /**
     * @private
     */
    function parseHref(href) {
        var a = document.createElement('a');

        a.href = href;

        return a;
    }

    /**
     * @private
     */
    function parseURL(href, forceVideo) {
        var id,
            type,
            ampersandPosition;

        /**
         * Get youtube ID
         * @param {String} srchref
         * @returns {{}}
         */
        function _getYoutubeId(srchref) {
            var srcid = srchref.search.split('v=')[1];

            if (srcid) {
                ampersandPosition = srcid.indexOf('&');

                if (ampersandPosition === -1) {
                    return srcid;
                }

                srcid = srcid.substring(0, ampersandPosition);
            }

            return srcid;
        }

        if (typeof href !== 'string') {
            return href;
        }

        href = parseHref(href);

        if (href.host.match(/youtube\.com/) && href.search) {
            id = href.search.split('v=')[1];

            if (id) {
                id = _getYoutubeId(id);
                type = 'youtube';
            }
        } else if (href.host.match(/youtube\.com|youtu\.be/)) {
            id = href.pathname.replace(/^\/(embed\/|v\/)?/, '').replace(/\/.*/, '');
            type = 'youtube';
        } else if (href.host.match(/vimeo\.com/)) {
            type = 'vimeo';
            id = href.pathname.replace(/^\/(video\/)?/, '').replace(/\/.*/, '');
        }

        if ((!id || !type) && forceVideo) {
            id = href.href;
            type = 'custom';
        }

        return id ? {
            id: id, type: type, s: href.search.replace(/^\?/, '')
        } : false;
    }

    //create AddFotoramaVideoEvents widget
    $.widget('mage.AddFotoramaVideoEvents', {
        options: {
            VideoData: '',
            VideoSettings: ''
        },

        PV: 'product-video', // [CONST]
        VID: 'video', // [CONST]
        VI: 'vimeo', // [CONST]
        FTVC: 'fotorama__video-close',
        FTAR: 'fotorama__arr',
        Base: 0, //on check for video is base this setting become true if there is any video with base role
        MobileMaxWidth: 767,
        GP: 'gallery-placeholder', //gallery placeholder class is needed to find and erase <script> tag

        /**
         *
         * @private
         */
        _init: function () {
            if (this._checkForVideoExist()) {
                this._checkForVimeo();
                this._isVideoBase();
                this._initFotoramaVideo();
                this._attachFotoramaEvents();
            }
        },

        /**
         *
         * @param {Object} inputData
         * @param {bool} isJSON
         * @returns {{}}
         * @private
         */
        _createVideoData: function (inputData, isJSON) {
            var videoData = {},
                key,
                dataUrl,
                tmpVideoData,
                tmpInputData,
                inputDataKeys,
                i;

            if (isJSON) {
                inputData = $.parseJSON(inputData);
            }

            inputDataKeys = Object.keys(inputData);

            for (i = 0; i < inputDataKeys.length; i++) {
                key = inputDataKeys[i];
                tmpInputData = inputData[key];
                dataUrl = '';
                tmpVideoData = {
                    mediaType: '',
                    isBase: '',
                    id: '',
                    provider: ''
                };
                tmpVideoData.mediaType = this.VID;

                if (tmpInputData.mediaType !== 'external-video') {
                    tmpVideoData.mediaType = tmpInputData.mediaType;
                }

                tmpVideoData.isBase = tmpInputData.isBase;

                if (tmpInputData.videoUrl != null) {
                    dataUrl = tmpInputData.videoUrl;
                    dataUrl = parseURL(dataUrl);
                    tmpVideoData.id = dataUrl.id;
                    tmpVideoData.provider = dataUrl.type;
                }

                videoData[key] = tmpVideoData;
            }

            return videoData;
        },

        /**
         *
         * @param {Object} fotorama
         * @param {bool} isBase
         * @private
         */
        _createCloseVideo: function (fotorama, isBase) {
            var closeVideo;

            $(this.element).find('.' + this.FTVC).remove();
            $(this.element).append('<div class="' + this.FTVC + '"></div>');
            $(this.element).css('position', 'relative');
            closeVideo = $(this.element).find('.' + this.FTVC);
            this._closeVideoSetEvents(closeVideo, fotorama);

            if (
                isBase &&
                this.options.VideoData[fotorama.activeIndex].isBase &&
                $(window).width() > this.MobileMaxWidth) {
                this._showCloseVideo();
            }
        },

        /**
         *
         * @private
         */
        _hideCloseVideo: function () {
            $(this.element).find('.' + this.FTVC).css({
                opacity: 0,
                transform: 'translate3d(95px, -95px, 0)',
                display: 'none'
            });
            $('.' + this.FTAR).removeClass('hidden-video');
        },

        /**
         *
         * @private
         */
        _showCloseVideo: function () {
            $(this.element).find('.' + this.FTVC).css({
                opacity: 1,
                transform: 'translate3d(0px, 0px, 0)',
                display: 'block'
            });
            $('.' + this.FTAR).addClass('hidden-video');
        },

        /**
         *
         * @param {jQuery} $closeVideo
         * @param {jQuery} fotorama
         * @private
         */
        _closeVideoSetEvents: function ($closeVideo, fotorama) {
            $closeVideo.on('click', $.proxy(function () {
                this._unloadVideoPlayer(fotorama.activeFrame.$stageFrame.parent(), fotorama, true);
                this._hideCloseVideo();
            }, this));
        },

        /**
         *
         * @returns {Boolean}
         * @private
         */
        _checkForVideoExist: function () {
            var key, result, checker, videoSettings;

            if (!this.options.VideoData) {
                return false;
            }

            if (!this.options.VideoSettings) {
                return false;
            }
            result = this._createVideoData(this.options.VideoData, false),
                checker = false;
            videoSettings = this.options.VideoSettings[0];
            videoSettings.playIfBase = parseInt(videoSettings.playIfBase, 10);
            videoSettings.showRelated = parseInt(videoSettings.showRelated, 10);
            videoSettings.videoAutoRestart = parseInt(videoSettings.videoAutoRestart, 10);

            for (key in result) {
                if (result[key].mediaType === this.VID) {
                    checker = true;
                }
            }

            if (checker) {
                this.options.VideoData = result;
            }

            return checker;
        },

        /**
         *
         * @private
         */
        _checkForVimeo: function () {
            var allVideoData = this.options.VideoData,
                videoItem;

            for (videoItem in allVideoData) {
                if (allVideoData[videoItem].provider === this.VI) {
                    this._loadVimeoJSFramework();
                }
            }
        },

        /**
         *
         * @private
         */
        _isVideoBase: function () {
            var allVideoData = this.options.VideoData,
                videoItem,
                videoSettings,
                allVideoDataKeys,
                key,
                i;

            allVideoDataKeys = Object.keys(allVideoData);

            for (i = 0; i < allVideoDataKeys.length; i++) {
                key = allVideoDataKeys[i];
                videoItem = allVideoData[key];
                videoSettings = allVideoData[videoItem];

                if (
                    videoSettings.mediaType === this.VID && videoSettings.isBase &&
                    this.options.VideoSettings[0].playIfBase
                ) {
                    this.Base = true;
                }
            }

            this._createCloseVideo($(this.element).data('fotorama'), this.Base);
        },

        /**
         *
         * @private
         */
        _loadVimeoJSFramework: function () {
            var element = document.createElement('script'),
                scriptTag = document.getElementsByTagName('script')[0];

            element.async = true;
            element.src = 'https://f.vimeocdn.com/js/froogaloop2.min.js';
            scriptTag.parentNode.insertBefore(element, scriptTag);
        },

        /**
         *
         * @param {Event} e
         * @private
         */
        _initFotoramaVideo: function (e) {
            var fotorama = $(this.element).data('fotorama'),
                thumbsParent,
                thumbs,
                t,
                tmpVideoData;

            if (!fotorama.activeFrame.$navThumbFrame) {
                $(this.element).on('fotorama:showend', $.proxy(function (evt, fotoramaData) {
                    $(fotoramaData.activeFrame.$stageFrame).removeAttr('href');
                }, this));

                this._startPrepareForPlayer(e, fotorama);

                return null;
            }

            thumbsParent = fotorama.activeFrame.$navThumbFrame.parent(),
                thumbs = thumbsParent.find('.fotorama__nav__frame');

            for (t = 0; t < thumbs.length; t++) {
                tmpVideoData = this.options.VideoData[t];

                if (tmpVideoData.mediaType === this.VID) {
                    thumbsParent.find('.fotorama__nav__frame:eq(' + t + ')').addClass('video-thumb-icon');
                }
            }
            $(this.element).on('fotorama:showend', $.proxy(function (evt, fotoramaData) {
                $(fotoramaData.activeFrame.$stageFrame).removeAttr('href');
            }, this));

            this._startPrepareForPlayer(e, fotorama);
        },

        /**
         * Attach
         *
         * @private
         */
        _attachFotoramaEvents: function () {
            $(this.element).on('fotorama:showend', $.proxy(function (e, fotorama) {
                this._startPrepareForPlayer(e, fotorama);
            }, this));
        },

        /**
         * Start prepare for player
         *
         * @param {Event} e
         * @param {jQuery} fotorama
         * @private
         */
        _startPrepareForPlayer: function (e, fotorama) {
            this._unloadVideoPlayer(fotorama.activeFrame.$stageFrame.parent(), fotorama, false);
            this._checkForVideo(e, fotorama, -1);
            this._checkForVideo(e, fotorama, 0);
            this._checkForVideo(e, fotorama, 1);
        },

        /**
         * Check for video
         *
         * @param {Event} e
         * @param {jQuery} fotorama
         * @param {Number} number
         * @private
         */
        _checkForVideo: function (e, fotorama, number) {
            var frameNumber = parseInt(fotorama.activeFrame.i, 10),
                videoData = this.options.VideoData[frameNumber - 1 + number],
                $image = fotorama.data[frameNumber - 1 + number];

            if ($image) {
                $image = $image.$stageFrame;
            }

            if ($image && videoData && videoData.mediaType === this.VID) {
                $(fotorama.activeFrame.$stageFrame).removeAttr('href');
                this._prepareForVideoContainer($image, videoData, fotorama, number);
            }
        },

        /**
         * Prepare for video container
         *
         * @param {jQuery} $image
         * @param {Object} videoData
         * @param {Object} fotorama
         * @param {Number} number
         * @private
         */
        _prepareForVideoContainer: function ($image, videoData, fotorama, number) {
            if (!$image.hasClass('fotorama-video-container')) {
                $image.addClass('fotorama-video-container').addClass('video-unplayed');
            }

            this._createVideoContainer(videoData, $image);
            this._setVideoEvent($image, this.PV, fotorama, number);
        },

        /**
         * Create video container
         *
         * @param {Object} videoData
         * @param {jQuery} $image
         * @private
         */
        _createVideoContainer: function (videoData, $image) {
            var videoSettings;

            if ($image.find('.' + this.PV).length !== 0) {
                return;
            }

            videoSettings = this.options.VideoSettings[0];
            $image.append(
                '<div class="' +
                this.PV +
                '" data-related="' +
                videoSettings.showRelated +
                '" data-loop="' +
                videoSettings.videoAutoRestart +
                '" data-type="' +
                videoData.provider +
                '" data-code="' +
                videoData.id +
                '" data-width="100%" data-height="100%"></div>'
            );
        },

        /**
         *
         * @param {Object} $image
         * @param {Object} PV
         * @param {Object} fotorama
         * @param {Number} number
         * @private
         */
        _setVideoEvent: function ($image, PV, fotorama, number) {
            var self = this;

            $image.find('.magnify-lens').remove();
            $image.on('click', function () {
                if ($(this).hasClass('video-unplayed') && $(this).find('iframe').length === 0) {
                    $(this).removeClass('video-unplayed');
                    $(this).find('.' + PV).productVideoLoader();
                    self._showCloseVideo();
                }
            });
            this._handleBaseVideo(fotorama, number); //check for video is it base and handle it if it's base
        },

        /**
         * Handle base video
         * @param {Object} fotorama
         * @param {Number} srcNumber
         * @private
         */
        _handleBaseVideo: function (fotorama, srcNumber) {
            var waitForFroogaloop,
                videoData = this.options.VideoData,
                activeIndex = fotorama.activeIndex,
                number = parseInt(srcNumber, 10),
                activeIndexIsBase = videoData[activeIndex];

            if (!this.Base) {
                return;
            }

            if (activeIndexIsBase && number === 0 && $(window).width() > this.MobileMaxWidth) {
                if (this.options.VideoData[fotorama.activeIndex].provider === this.VI) {
                    waitForFroogaloop = setInterval($.proxy(function () {
                        if (window.Froogaloop) {
                            clearInterval(waitForFroogaloop);
                            $(this.element).data('fotorama').activeFrame.$stageFrame[0].click();
                            this.Base = false;
                        }
                    }, this), 50);
                } else { //if not a vimeo - play it immediately
                    $(this.element).data('fotorama').activeFrame.$stageFrame[0].click();
                    this.Base = false;
                }
            }
        },

        /**
         * Destroy video player
         * @param {jQuery} $wrapper
         * @param {jQuery} current
         * @param {bool} close
         * @private
         */
        _unloadVideoPlayer: function ($wrapper, current, close) {
            var self = this;

            $wrapper.find('.' + this.PV).each(function () {
                var $item = $(this).parent(),
                    cloneVideoDiv,
                    iframeElement = $(this).find('iframe'),
                    currentIndex,
                    itemIndex;

                if (iframeElement.length === 0) {
                    return;
                }

                currentIndex = current.activeFrame.$stageFrame.index();
                itemIndex = $item.index();

                if (currentIndex === itemIndex && !close) {
                    return;
                }

                if (currentIndex !== itemIndex && close) {
                    return;
                }

                iframeElement.remove();
                cloneVideoDiv = $(this).clone();
                $(this).remove();
                $item.append(cloneVideoDiv);
                $item.addClass('video-unplayed');
                self._hideCloseVideo();

            });
        }
    });

    $('.gallery-placeholder').on('fotorama:ready', function () {
        $(this).find('.fotorama').AddFotoramaVideoEvents({
            VideoData: $(this).data('fotorama-video-data'),
            VideoSettings: $(this).data('fotorama-video-settings')
        });
        //no reason to store video data and settings after - erase it
        $(this).removeAttr('data-fotorama-video-data');
        $(this).removeAttr('data-fotorama-video-settings');
    });
});
