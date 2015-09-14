/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global FORM_KEY*/
define([
    'jquery',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/backend/tree-suggest',
    'mage/backend/validation',
    'Magento_ProductVideo/js/get-video-information'
], function ($) {
    'use strict';
    $.widget('mage.createVideoPlayer', {
      options : {
        video_id : '',
        video_provider : '',
        container : '.video-player-container',
        video_class : 'product-video',
        reset : false,
        meta_data : {
          DOM : {
            title : '.video-information.title span',
            uploaded : '.video-information.uploaded span',
            uploader : '.video-information.uploader span',
            duration : '.video-information.duration span',
            all : '.video-information span',
            wrapper : '.video-information'
          },
          data : {
            title : '',
            uploaded : '',
            uploader : '',
            uploader_url : '',
            duration : ''
          }
        }
      },
      _init : function () {
        if (this.options.reset) {
          this.reset();
        } else {
          this.update();
        }
      },
      update : function () {
        this.reset();
        $(this.options.container).append('<div class="'+this.options.video_class+'" data-type="'+this.options.video_provider+'" data-code="'+this.options.video_id+'" data-width="100%" data-height="100%"></div>');
        $(this.options.meta_data.DOM.wrapper).show();
        $(this.options.meta_data.DOM.title).text(this.options.meta_data.data.title);
        $(this.options.meta_data.DOM.uploaded).text(this.options.meta_data.data.uploaded);
        $(this.options.meta_data.DOM.duration).text(this.options.meta_data.data.duration);
        if (this.options.video_provider === 'youtube') {
          $(this.options.meta_data.DOM.uploader).html('<a href="https://youtube.com/channel/'+this.options.meta_data.data.uploader_url+'">'+this.options.meta_data.data.uploader+'</a>');
        } else
        if (this.options.video_provider === 'vimeo') {
          $(this.options.meta_data.DOM.uploader).html('<a href="'+this.options.meta_data.data.uploader_url+'">'+this.options.meta_data.data.uploader+'</a>');
        }
        $('.'+this.options.video_class).productVideoLoader();
      },
      reset : function () {
        $(this.options.container).find('.'+this.options.video_class).remove();
        $(this.options.meta_data.DOM.wrapper).hide();
        $(this.options.meta_data.DOM.all).text('');
      }
    });

    $.widget('mage.updateInputFields', {
      options : {
        reset: false,
        DOM : {
          url_field : '',
          title_field : 'input[name="video_title"]',
          description_field : 'textarea[name="video_description"]',
          thumbnail_location : '.field-new_video_screenshot_preview .admin__field-control'
        },
        data : {
          url : '',
          title : '',
          description : '',
          thumbnail : ''
        }
      },
      _init : function () {
        if (this.options.reset) {
          this.reset();
        } else {
          this.update();
        }
      },
      update : function () {
        this.reset();
        $(this.options.DOM.title_field).val(this.options.data.title);
        $(this.options.DOM.description_field).val(this.options.data.description);
        $(this.options.DOM.thumbnail_location).append('<img src="'+this.options.data.thumbnail+'" />');
      },
      reset : function () {
        $(this.options.DOM.title_field).val('');
        $(this.options.DOM.description_field).val('');
        $(this.options.DOM.thumbnail_location).find('img').remove();
      }
    });

    /**
     */
    $.widget('mage.newVideoDialog', {

        _previewImage: null,

        clickedElement : '',

        _images: {},

        _imageTypes: ['.jpeg', '.pjpeg', '.jpeg', '.jpg', '.pjpeg', '.png', '.gif'],

        _imageWidgetSelector: '#media_gallery_content',

        _videoPreviewInputSelector: '#new_video_screenshot',

        _videoDisableinputSelector: '#new_video_disabled',

        _videoPreviewImagePointer: '#new_video_screenshot_preview',

        _videoFormSelector: '#new_video_form',

        _itemIdSelector: '#item_id',

        _videoUrlSelector: '[name="video_url"]',

        _videoUrlWidget: null,

        _videoInformationBtnSelector: '[name="new_video_get"]',

        _editVideoBtnSelector : '.image.item',

        _videoInformationGetBtn: null,

        _videoInformationGetUrlField: null,

        _videoInformationGetEditBtn: null,

        _isEditPage : false,

        _onlyVideoPlayer : false, //if onlyVideoPlayer - in modal we create on focus out only VideoPlayer and not filling input fields

        _bind: function() {
            var events = {
                'setImage': '_onSetImage'
            };
            this._on(events);

            this._videoUrlWidget = jQuery(this._videoUrlSelector).videoData();
            this._videoInformationGetBtn = jQuery(this._videoInformationBtnSelector);
            this._videoInformationGetUrlField = jQuery(this._videoUrlSelector);
            this._videoInformationGetEditBtn = jQuery(this._editVideoBtnSelector);

            this._videoInformationGetBtn.on('click', $.proxy(this._onGetVideoInformationClick, this));
            this._videoInformationGetUrlField.on('focusout', $.proxy(this._onGetVideoInformationFocusOut, this));
            this._videoUrlWidget.on("updated_video_information", $.proxy(this._onGetVideoInformationSuccess, this));
            this._videoUrlWidget.on("error_updated_information", $.proxy(this._onGetVideoInformationError, this));
        },

        /**
         * Fired when user click on button "Get video information"
          * @private
         */
        _onGetVideoInformationClick: function() {
            this._onlyVideoPlayer = false;
            this._isEditPage = false;
            this._videoInformationGetUrlField.videoData();
            this._videoUrlWidget.trigger('update_video_information');
        },
        /**
         * Fired when user do focus out from url field
         * @private
         */
        _onGetVideoInformationFocusOut : function () {
            if ($('.mage-new-video-dialog .modal-title').text() === 'Edit Video') {
              this._isEditPage = true;
            } else {
              this._isEditPage = false;
            }
            this._videoInformationGetUrlField.videoData();
            this._videoUrlWidget.trigger('update_video_information');
        },
        /**
         * Fired when user click Edit Video button
         * @private
         */
        _onGetVideoInformationEditClick : function () {
          this._onlyVideoPlayer = true;
          this._isEditPage = true;
          this._videoInformationGetUrlField.videoData();
          this._videoUrlWidget.trigger('update_video_information');
        },
        /**
         * Fired when successfully received information about the video.
         * @param e
         * @param data
         * @private
         */
        _onGetVideoInformationSuccess: function(e, data) {
          if (!this._isEditPage) {
            $('.mage-new-video-dialog').createVideoPlayer({
              video_id : data.videoId,
              video_provider : data.videoProvider,
              reset: false,
              meta_data : {
                DOM : {
                  title : '.video-information.title span',
                  uploaded : '.video-information.uploaded span',
                  uploader : '.video-information.uploader span',
                  duration : '.video-information.duration span',
                  all : '.video-information span',
                  wrapper : '.video-information'
                },
                data : {
                  title : data.title,
                  uploaded : data.uploaded,
                  uploader : data.channel,
                  duration : data.duration,
                  uploader_url : data.channelId
                }
              }
            }).updateInputFields({
              reset: false,
              data : {
                title : data.title,
                description : data.description,
                thumbnail : data.thumbnail
              }
            });
          }
          if (this._onlyVideoPlayer) {
            $('.mage-new-video-dialog').createVideoPlayer({
              video_id : data.videoId,
              video_provider : data.videoProvider,
              reset: false,
              meta_data : {
                DOM : {
                  title : '.video-information.title span',
                  uploaded : '.video-information.uploaded span',
                  uploader : '.video-information.uploader span',
                  duration : '.video-information.duration span',
                  all : '.video-information span',
                  wrapper : '.video-information'
                },
                data : {
                  title : data.title,
                  uploaded : data.uploaded,
                  uploader : data.channel,
                  duration : data.duration,
                  uploader_url : data.channelId
                }
              }
            });
          }
        },

        /**
         * Fired when receiving information about the video ended with error
         * @param e
         * @param data
         * @private
         */
        _onGetVideoInformationError: function(e, data) {
        },

        /**
         * Remove ".tmp"
         *
         * @param name
         * @returns {*}
         * @private
         */
        __prepareFilename: function(name) {
            if(!name) {
                return name;
            }
            var tmppost = '.tmp';
            if(name.endsWith(tmppost)) {
                name = name.slice(0, name.length - tmppost.length);
            }
            return name;
        },

        /**
         *  set image data
         * @param file
         * @param imageData
         * @private
         */
        _setImage: function(file, imageData) {
            file = this.__prepareFilename(file);
            this._images[file] = imageData;
            jQuery(this._imageWidgetSelector).trigger('addItem', imageData);
            this.element.trigger('setImage', imageData);
            this._addVideoClass(imageData.url);
        },

        /**
         * Get image data
         *
         * @param file
         * @returns {*}
         * @private
         */
        _getImage: function(file) {
            file = this.__prepareFilename(file);
            return this._images[file];
        },

        /**
         * Replace image (update)
         * @param oldFile
         * @param newFile
         * @param imageData
         * @private
         */
        _replaceImage: function(oldFile, newFile, imageData) {
            var tmpOldFile = oldFile;
            var tmpNewFile = newFile;
            oldFile = this.__prepareFilename(oldFile);
            newFile = this.__prepareFilename(newFile);
            if(newFile == oldFile) {
                this._images[newFile] = imageData;
                this.saveImageRoles(imageData);
                return;
            }
            this._removeImage(oldFile);
            this._setImage(newFile, imageData);
            if(oldFile && imageData.old_file) {
                var oldImageId = this.findElementId(tmpOldFile);
                var newImageId = this.findElementId(tmpNewFile);
                var fc = jQuery(this._itemIdSelector).val();

                var suff = 'product[media_gallery][images]' + fc;

                var searchsuff = 'input[name="' + suff + '[value_id]"]';
                var key = jQuery(searchsuff).val();
                if(!key) {
                    return;
                }
                var old_val_id_elem = document.createElement('input');
                jQuery('form[data-form="edit-product"]').append(old_val_id_elem);
                jQuery(old_val_id_elem).attr({
                    type: 'hidden',
                    name: 'product[media_gallery][images][' + newImageId + '][save_data_from]'
                }).val(key);
            }
        },

        /**
         * Remove image data
         * @param file
         * @private
         */
        _removeImage: function(file) {
            var imageData = this._getImage(file);
            if(!imageData) {
                return;
            }
            jQuery(this._imageWidgetSelector).trigger('removeItem', imageData);
            this.element.trigger('removeImage', imageData);
            delete this._images[file];
        },

        /**
         * Fired when preview change
         *
         * @param event
         * @param imageData
         * @private
         */
        _onSetImage: function(event, imageData) {
            this.saveImageRoles(imageData);
        },

        /**
         *
         * Wrap _uploadFile
         *
         * @param file
         * @param oldFile
         * @param callback
         * @private
         */
        _uploadImage: function(file, oldFile, callback) {
            var self        = this;
            var url         = this.options.saveVideoUrl;
            var data = {
                files: file,
                url: url
            };
            this._uploadFile('send', data, $.proxy(function(result, textStatus, jqXHR) {
                var data = JSON.parse(result);
                if(data.errorcode) {
                    alert(data.error);
                    return;
                }
                $.each($(this._videoFormSelector).serializeArray(), function(i, field) {
                    data[field.name] = field.value;
                });
                data['disabled'] = $(this._videoDisableinputSelector).prop('checked') ? 1 : 0;
                data['media_type'] = 'external-video';
                data.old_file = oldFile;
                oldFile  ?
                    self._replaceImage(oldFile, data.file, data):
                    self._setImage(data.file, data);
                callback.call(0, data);
            }, this));

        },

        /**
         * File uploader
         * @returns {*}
         * @private
         */
        _uploadFile: function(method, data, callback) {
            var fu = jQuery(this._videoPreviewInputSelector);
            var tmp_input   = document.createElement('input');
            jQuery(tmp_input).attr({
                'name': fu.attr('name'),
                'value': fu.val(),
                'type': 'file',
                'data-ui-ud': fu.attr('data-ui-ud')
            }).css('display', 'none');
            fu.parent().append(tmp_input);
            var fileUploader = jQuery(tmp_input).fileupload();
            fileUploader.fileupload(method, data).success(function(result, textStatus, jqXHR) {
                tmp_input.remove();
                callback.call(null, result, textStatus, jqXHR);
            });
        },

        /**
         * Update style
         * @param url
         * @private
         */
        _addVideoClass: function(url) {
            var class_video = 'video-item';
            jQuery('img[src="' + url + '"]').addClass(class_video);
        },

        /**
         * Build widget
         * @private
         */
        _create: function () {
            var imgs = jQuery(this._imageWidgetSelector).data('images') || [];
            for(var i = 0; i < imgs.length; i++) {
                var tmp = imgs[i];
                this._images[tmp.file] = tmp;
                if(tmp.media_type == 'external-video') {
                    tmp.subclass = 'video-item';
                    this._addVideoClass(tmp.url);
                }
            }
            this._bind();
            this.createVideoItemIcons();
            var widget = this;
            var uploader = jQuery(this._videoPreviewInputSelector);
            uploader.on('change', this._onImageInputChange.bind(this));
            uploader.attr('accept', this._imageTypes.join(','));

            this.toggleButtons();
            this.element.modal({
                type: 'slide',
                modalClass: 'mage-new-video-dialog form-inline',
                title: $.mage.__('Create Video'),
                buttons: [{
                    text: $.mage.__('Save'),
                    class: 'action-primary video-create-button',
                    click: $.proxy(widget._onCreate, widget)
                },
                {
                    text: $.mage.__('Save'),
                    class: 'action-primary video-edit',
                    click: $.proxy(widget._onUpdate, widget)
                },
                {
                    text: $.mage.__('Delete'),
                    class: 'action-primary video-delete-button',
                    click: $.proxy(widget._onDelete, widget)
                },
                {
                    text: $.mage.__('Cancel'),
                    class: 'video-cancel-button',
                    click: $.proxy(widget._onCancel, widget)
                }],
                opened: function(e) {
                    $('#video_url').focus();
                    var roles = $('.video_image_role');
                    roles.prop('disabled', false);
                    var file = jQuery('#file_name').val();
                    widget._onGetVideoInformationEditClick();
                    var modalTitleElement = $('.modal-title');
                    if(!file) {
                        roles.prop('checked', $('.image.item').length < 1);
                        modalTitleElement.text($.mage.__('Create Video'));
                        return;
                    }
                    modalTitleElement.text($.mage.__('Edit Video'));
                    var imageData = widget._getImage(file);
                    widget._onPreview(null, imageData.url, false);
                },
                closed: function(e) {
                    widget._onClose();
                    widget.createVideoItemIcons();
                }
            });
        },

        /**
         * Check form
         * @returns {*}
         */
        isValid: function() {
            var videoForm = $(this._videoFormSelector);
            videoForm.mage('validation', {
                errorPlacement: function (error, element) {
                    error.insertAfter(element);
                }
            }).on('highlight.validate', function (e) {
                $(this).validation('option');
            });
            videoForm.validation();
            return videoForm.valid();
        },

        createVideoItemIcons : function () {
          $('#image-container, #media_gallery_content').find('.video-item').parent().addClass('video-item');
        },

        /**
         * Fired when click on create video
         * @private
         */
        _onCreate: function() {
            var nvs = jQuery(this._videoPreviewInputSelector);
            var file = nvs.get(0);
            if(file && file.files && file.files.length) {
                file =  file.files[0];
            } else {
                file = null;
            }
            var reqClass = 'required-entry _required';
            if (!file) {
                nvs.addClass(reqClass);
            }

            if(!this.isValid()) {
                return;
            }

            this._uploadImage(file, null, $.proxy(function(code, data) {
                this.close();
            }, this));
            nvs.removeClass(reqClass);
        },

        /**
         * Fired when click on update video
         * @private
         */
        _onUpdate: function() {

            if(!this.isValid()) {
                return;
            }
            var inputFile       = jQuery(this._videoPreviewInputSelector);
            var itemId          = jQuery(this._itemIdSelector).val();
            itemId              = itemId.slice(1, itemId.length - 1);
            var mediaFields     = $('input[name*="' + itemId + '"]');
            var _inputSelector  = '[name*="[' + itemId + ']"';
            $.each(mediaFields, function(i, el) {
                var elName      = el.name;
                var start       = elName.indexOf(itemId) + itemId.length + 2;
                var fieldName   = elName.substring(start, el.name.length - 1);
                var _field      = $('#' + fieldName);

                if (_field.length > 0) {
                    var _tmp = _inputSelector.slice(0, _inputSelector.length - 1) + '[' + fieldName + ']"]';
                    $(_tmp).val(_field.val());
                }
            });
            var flagChecked     = $(this._videoDisableinputSelector).prop('checked') ? 1 : 0;
            $('input' + _inputSelector + '[disabled]"]').val(flagChecked);
            $(_inputSelector).siblings('.image-fade').css('visibility', flagChecked ? 'visible': 'hidden');

            var imageData = this._getImage($('#file_name').val());
            var fileName = inputFile.get(0).files;
            if(!fileName || !fileName.length) {
                fileName = null;
            }
            inputFile.replaceWith(inputFile);

            var callback = $.proxy(function() {
                this.close();
            }, this);

            if (fileName) {
                this._uploadImage(fileName, imageData.file, callback);
            } else {
                callback(0, imageData);
                this._replaceImage(imageData.file, imageData.file, imageData);
            }
        },

        /**
         * Fired when clicked on cancel
         * @private
         */
        _onCancel: function() {
            this.close();
        },

        /**
         * Fired when clicked on delete
         * @private
         */
        _onDelete: function() {
            var filename = this.element.find('#file_name[data-ui-id="new-video-fieldset-element-hidden"]').val();
            this._removeImage(filename);
            this.close();
        },

        _readPreviewLocal: function(file, callback) {
            if(!window.FileReader) {
                return;
            }
            var fr = new FileReader;
            fr.onloadend = function() {
                callback(fr.result);
            };
            fr.readAsDataURL(file);
        },

        /**
         *  Image file input handler
         * @private
         */
        _onImageInputChange: function() {
            var jFile = jQuery(this._videoPreviewInputSelector);
            var file = jFile[0];
            var val = jFile.val();
            var prev = this._getPreviewImage();

            if(!val) {
                return;
            }
            var ext = '.' + val.split('.').pop();
            if(
                ext.length < 2 ||
                this._imageTypes.indexOf(ext) == -1 ||
                !file.files  ||
                !file.files.length

            ) {
                prev.remove();
                this._previewImage = null;
                jFile.val('');
                return;
            } // end if
            file = file.files[0];
            this._onPreview(null, file, true);
        },

        /**
         * Change Preview
         * @param error
         * @param src
         * @param local
         * @private
         */
        _onPreview: function(error, src, local) {
            var img = this._getPreviewImage();
            if(error) {
                return;
            }

            var renderImage = function(src) {
                img.attr({'src': src}).show();
            };

            if(!local) {
                renderImage(src);
            } else {
                this._readPreviewLocal(src, renderImage);
            }
        },

        /**
         *
         * Return preview image imstance
         * @returns {null}
         * @private
         */
        _getPreviewImage: function() {
            if(!this._previewImage) {
                this._previewImage = jQuery(document.createElement('img')).css({
                    'width' : '145px',
                    'display': 'none',
                    'src': ''
                });
                jQuery(this._previewImage).insertAfter(this._videoPreviewImagePointer);
            }
            return this._previewImage;
        },

        /**
         * Close slideout dialog
         */
        close: function() {
            this.element.trigger('closeModal');
        },

        /**
         * Close dialog wrap
         * @private
         */
        _onClose: function() {
            if(this._previewImage) {
                this._previewImage.remove();
                this._previewImage = null;
            }
            var newVideoForm = this.element.find(this._videoFormSelector);
            jQuery(newVideoForm).find('input[type="hidden"][name!="form_key"]').val('');
            $('input[name*="' + $(this._itemIdSelector).val() + '"]').parent().removeClass('active');
            try {
                newVideoForm.validation('clearError');
            } catch(e) {}
            newVideoForm.trigger('reset');
        },

        /**
         * Find element by fileName
         */
        findElementId: function (file) {
            var elem = jQuery('.image.item').find('input[value="' + file + '"]');
            if(!elem) {
                return null;
            }
            return jQuery(elem).attr('name').replace('product[media_gallery][images][', '').replace('][file]', '');
        },

        /**
         * @param imageData
         */
        saveImageRoles: function(imageData) {
            var data = imageData.file;
            if(!data) {
                throw new Error('You need use _getImae');
            }
            var self = this;
            if (data.length > 0) {
                var containers = $('.video-placeholder').siblings('input');
                $.each(containers, function (i, el) {
                    var start = el.name.indexOf('[') + 1;
                    var end = el.name.indexOf(']');
                    var imageType = el.name.substring(start, end);
                    var imageCheckbox = $(self._videoFormSelector + ' input[value="' + imageType + '"]');
                    self._changeRole(imageType, imageCheckbox.attr('checked'), imageData);
                });
            }
        },

        _changeRole: function(imageType, isEnabled, imageData) {
            var needCheked = true;
            if(!isEnabled) {
                needCheked = jQuery('input[name="product[' + imageType + ']"]').val() == imageData.file;
            }
            if(!needCheked) {
                return;
            }

            jQuery(this._imageWidgetSelector).trigger('setImageType', {
                type:  imageType,
                imageData: isEnabled ? imageData: null
            });
        },

        toggleButtons: function() {
            var self = this;
            $('.video-placeholder').click(function() {
                $('.video-create-button').show();
                $('.video-delete-button').hide();
                $('.video-edit').hide();
                $('.mage-new-video-dialog').createVideoPlayer({reset : true}).createVideoPlayer('reset').updateInputFields({reset : true}).updateInputFields('reset');
            });
            $(document).on('click', '.item.image', function() {
                $('.video-create-button').hide();
                $('.video-delete-button').show();
                $('.video-edit').show();
                $('.mage-new-video-dialog').createVideoPlayer({reset : true}).createVideoPlayer('reset');
            });
            $(document).on('click', '.item.image', function() {
                var formFields = $(self._videoFormSelector).find('.edited-data');
                var container = $(this);

                $.each(formFields, function (i, field) {
                    $(field).val(container.find('input[name*="' + field.name + '"]').val());
                });

                var flagChecked = (container.find('input[name*="disabled"]').val() == 1) ? true : false;
                $(self._videoDisableinputSelector).prop('checked', flagChecked);

                var file = $('#file_name').val(container.find('input[name*="file"]').val());

                $.each($('.video_image_role'), function(){
                    $(this).prop('checked', false).prop('disabled', false);
                });

                $.each($('.video-placeholder').siblings('input:hidden'), function() {
                    if ($(this).val() == file.val()) {
                        var start = this.name.indexOf('[') + 1;
                        var end = this.name.length - 1;
                        var imageRole = this.name.substring(start, end);
                        $(self._videoFormSelector + ' input[value="' + imageRole + '"]').prop('checked', true);
                    }
                });

            });
        }
    });
    $('#group-fields-image-management > legend > span').text('Images and Videos');
    
    return $.mage.newVideoDialog;
});
