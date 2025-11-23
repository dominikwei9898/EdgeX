/**
 * EverShop Variation Gallery Admin JavaScript
 * 处理后台变体图片上传、排序和删除
 */

(function($) {
    'use strict';

    var EverShopVariationGallery = {
        frame: null,
        currentVariationId: null,

        init: function() {
            this.bindEvents();
            this.initSortable();
        },

        bindEvents: function() {
            var self = this;

            // 添加图片按钮
            $(document).on('click', '.evershop-add-variation-gallery-image', function(e) {
                e.preventDefault();
                self.currentVariationId = $(this).data('variation-id');
                self.openMediaUploader($(this));
            });

            // 删除单个图片
            $(document).on('click', '.evershop-variation-gallery-images .delete', function(e) {
                e.preventDefault();
                $(this).closest('li').remove();
                self.updateGalleryIds($(this).closest('.evershop-variation-gallery-images'));
            });

            // 清空图库
            $(document).on('click', '.evershop-clear-variation-gallery', function(e) {
                e.preventDefault();
                
                if (confirm(evershopGallery.i18n.confirm_clear)) {
                    var variationId = $(this).data('variation-id');
                    var $container = $('.evershop-variation-gallery-images[data-variation-id="' + variationId + '"]');
                    
                    $container.empty();
                    self.updateGalleryIds($container);
                }
            });
        },

        openMediaUploader: function($button) {
            var self = this;

            // 如果 media frame 已存在，重新打开
            if (this.frame) {
                this.frame.open();
                return;
            }

            // 创建 media frame
            this.frame = wp.media({
                title: evershopGallery.i18n.select_images,
                button: {
                    text: evershopGallery.i18n.use_images
                },
                multiple: true
            });

            // 当选择图片后
            this.frame.on('select', function() {
                var selection = self.frame.state().get('selection');
                var variationId = self.currentVariationId;
                var $container = $('.evershop-variation-gallery-images[data-variation-id="' + variationId + '"]');

                selection.map(function(attachment) {
                    attachment = attachment.toJSON();
                    self.addImage(attachment, $container);
                });

                self.updateGalleryIds($container);
            });

            // 打开 media frame
            this.frame.open();
        },

        addImage: function(attachment, $container) {
            var imageHtml = '<li class="image" data-attachment-id="' + attachment.id + '">' +
                '<img src="' + (attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url) + '" />' +
                '<a href="#" class="delete" title="删除图片">×</a>' +
                '</li>';

            $container.append(imageHtml);
        },

        updateGalleryIds: function($container) {
            var imageIds = [];
            
            $container.find('.image').each(function() {
                imageIds.push($(this).data('attachment-id'));
            });

            var variationId = $container.data('variation-id');
            var $input = $('input[name="evershop_variation_gallery[' + variationId + ']"]');
            
            $input.val(imageIds.join(','));
            
            // 标记表单已修改，触发 WooCommerce 保存机制
            $input.trigger('change');
            
            // 触发 WooCommerce 变体修改事件
            $('#variable_product_options').trigger('woocommerce_variations_input_changed');
            
            // 显示 "Save changes" 按钮
            $('.woocommerce_variation').addClass('variation-needs-update');
            $('button.save-variation-changes, button.cancel-variation-changes').removeAttr('disabled');
        },

        initSortable: function() {
            var self = this;

            $('.evershop-variation-gallery-images').sortable({
                items: 'li.image',
                cursor: 'move',
                scrollSensitivity: 40,
                forcePlaceholderSize: true,
                forceHelperSize: false,
                helper: 'clone',
                opacity: 0.65,
                placeholder: 'evershop-gallery-sortable-placeholder',
                start: function(event, ui) {
                    ui.item.css('background-color', '#f6f6f6');
                },
                stop: function(event, ui) {
                    ui.item.removeAttr('style');
                },
                update: function(event, ui) {
                    self.updateGalleryIds($(this));
                }
            });
        }
    };

    // 初始化
    $(document).ready(function() {
        EverShopVariationGallery.init();
    });

})(jQuery);

