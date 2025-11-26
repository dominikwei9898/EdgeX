/**
 * EverShop Style Variant Selector for WordPress/WooCommerce
 * 
 * 实现变体切换功能：
 * - 切换产品图片（支持多图库）
 * - 更新价格
 * - 更新 SKU
 * - 更新库存状态
 * 
 * 核心设计：
 * - currentImages: 当前显示的图片数组（唯一数据源）
 * - currentImageIndex: 当前主图索引
 * - 主图和缩略图响应式绑定到 currentImages
 */

(function($) {
    'use strict';

    var EvershopVariantSelector = {
        $productGallery: null,
        $priceContainer: null,
        $skuContainer: null,
        
        // 数据状态
        originalImages: [],      // 原始产品图片
        currentImages: [],       // 当前显示的图片（响应式数据源）
        currentImageIndex: 0,    // 当前主图索引
        
        init: function() {
            console.log('🚀 EverShop Variant Selector 初始化...');
            
            this.$productGallery = $('.woocommerce-product-gallery');
            this.$priceContainer = $('.product-price');
            this.$skuContainer = $('.sku-wrapper .sku');
            
            console.log('图片库:', this.$productGallery ? this.$productGallery.length : 0);
            console.log('价格容器:', this.$priceContainer.length);
            console.log('SKU容器:', this.$skuContainer.length);
                
            // 1. 禁用 FlexSlider 的干扰
            this.disableFlexSlider();
            
            // 2. 保存原始图片
            this.saveOriginalImages();
            
            // 3. 设置当前图片为原始图片
            this.currentImages = this.originalImages.slice(); // 复制数组
            
            // 4. 绑定变体选择事件
            this.bindVariantEvents();
            
            // 5. 绑定图片切换事件
            this.bindGalleryEvents();

            // 6. 绑定滑动事件
            this.bindSwipeEvents();
            
            // 7. 添加无障碍属性
            this.addAriaAttributes();

            // 7. 自动选择默认变体
            this.selectDefaultVariation();
            
            console.log('✅ 初始化完成');
        },
        
        /**
         * 禁用 FlexSlider 的干扰
         */
        disableFlexSlider: function() {
            console.log('🔧 禁用 FlexSlider 干扰...');
            
            // 移除 FlexSlider 实例（如果存在）
            var $gallery = this.$productGallery;
            if ($gallery.data('flexslider')) {
                try {
                    $gallery.data('flexslider').pause();
                    console.log('✅ FlexSlider 已暂停');
                } catch (e) {
                    console.warn('⚠️ FlexSlider 暂停失败:', e);
                }
            }
            
            // 阻止 FlexSlider 的后续初始化
            $(document).off('click touchstart', '.flex-control-nav li');
        },
        
        /**
         * 保存原始产品图片
         */
        saveOriginalImages: function() {
            var self = this;
            
            if (!this.$productGallery.length) {
                console.warn('⚠️ 找不到产品图片库');
                return;
            }
            
            this.$productGallery.find('.woocommerce-product-gallery__image').each(function() {
                var $img = $(this).find('img');
                var $link = $(this).find('a');
                
                self.originalImages.push({
                    src: $img.attr('src') || '',
                    full_src: $link.attr('href') || $img.attr('src') || '',
                    srcset: $img.attr('srcset') || '',
                    sizes: $img.attr('sizes') || '',
                    alt: $img.attr('alt') || '',
                    title: $img.attr('title') || '',
                    width: $img.attr('data-large_image_width') || $img.width() || 1024,
                    height: $img.attr('data-large_image_height') || $img.height() || 1024
                });
            });
            
            console.log('💾 已保存', self.originalImages.length, '张原始图片');
        },
        
        /**
         * 绑定变体选择事件
         */
        bindVariantEvents: function() {
            var self = this;
                
            // 变体选项点击事件
            $(document).on('click', '.variant-option-button:not(:disabled)', function(e) {
                e.preventDefault();
                self.handleVariationChange($(this));
        });
                
            // 键盘支持
            $(document).on('keypress', '.variant-option-button:not(:disabled)', function(e) {
                if (e.which === 13 || e.which === 32) {
                e.preventDefault();
                $(this).trigger('click');
            }
            });
        },
        
        /**
         * 绑定图片库事件（缩略图点击）
         */
        bindGalleryEvents: function() {
            var self = this;
            
            // 使用事件委托，监听所有缩略图点击
            // 兼容移动端 click，增加 touchend 处理
            $(document).on('click touchend', '.flex-control-nav.flex-control-thumbs li, .woocommerce-product-gallery ol li', function(e) {
                // 防止 click 和 touchend 重复触发
                if (e.type === 'touchend') {
                    $(this).data('is-touch', true);
                } else if (e.type === 'click' && $(this).data('is-touch')) {
                    $(this).data('is-touch', false);
                    return;
                }

                e.preventDefault();
                e.stopPropagation(); // 阻止事件冒泡，防止触发 FlexSlider
                
                var index = $(this).index();
                console.log('🖱️ 缩略图被点击/触摸，索引:', index);
                
                // 切换到对应索引的图片
                self.switchToImage(index);
                
                // 延迟一小段时间后再次重置样式，防止 FlexSlider 后续干扰
                setTimeout(function() {
                    self.resetGalleryStyles();
                }, 50);
            });
            
            // 🔧 监控样式变化，防止 FlexSlider 重新添加动画
            this.watchGalleryStyles();
            
            console.log('✅ 图片库事件绑定完成');
        },

        /**
         * 绑定滑动事件 (移动端支持)
         */
        bindSwipeEvents: function() {
            var self = this;
            var touchStartX = 0;
            var touchStartY = 0;
            
            // 监听主图容器
            var $wrapper = $('.woocommerce-product-gallery__wrapper');
            
            // 确保 wrapper 存在
            if (!$wrapper.length) return;

            $wrapper.on('touchstart', function(e) {
                var touch = e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
                touchStartX = touch.pageX;
                touchStartY = touch.pageY;
            });
            
            $wrapper.on('touchend', function(e) {
                var touch = e.originalEvent.changedTouches[0];
                var touchEndX = touch.pageX;
                var touchEndY = touch.pageY;
                
                self.handleSwipeGesture(touchStartX, touchStartY, touchEndX, touchEndY);
            });
        },

        /**
         * 处理滑动逻辑
         */
        handleSwipeGesture: function(startX, startY, endX, endY) {
            var xDiff = startX - endX;
            var yDiff = startY - endY;
            var minSwipeDistance = 50;

            // 检测水平滑动 (水平距离大于垂直距离，且超过阈值)
            if (Math.abs(xDiff) > Math.abs(yDiff) && Math.abs(xDiff) > minSwipeDistance) {
                if (xDiff > 0) {
                    // 向左滑动 -> 下一张
                    console.log('👈 向左滑动 -> 下一张');
                    this.nextImage();
                } else {
                    // 向右滑动 -> 上一张
                    console.log('👉 向右滑动 -> 上一张');
                    this.prevImage();
                }
            }
        },

        /**
         * 下一张图片
         */
        nextImage: function() {
            var nextIndex = this.currentImageIndex + 1;
            if (nextIndex >= this.currentImages.length) {
                nextIndex = 0; // 循环播放
            }
            this.switchToImage(nextIndex);
        },

        /**
         * 上一张图片
         */
        prevImage: function() {
            var prevIndex = this.currentImageIndex - 1;
            if (prevIndex < 0) {
                prevIndex = this.currentImages.length - 1; // 循环播放
            }
            this.switchToImage(prevIndex);
        },
        
        /**
         * 监控图片库样式变化，防止 FlexSlider 干扰
         */
        watchGalleryStyles: function() {
            var self = this;
            
            // 使用 MutationObserver 监控样式变化
            var $wrapper = $('.woocommerce-product-gallery__wrapper');
            var $viewport = $('.flex-viewport');
            
            if ($wrapper.length && window.MutationObserver) {
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                            var $target = $(mutation.target);
                            var style = $target.attr('style') || '';
                            
                            // 检查是否有不需要的 transform 或 transition
                            if (style.indexOf('translate') !== -1 && style.indexOf('translateX(0)') === -1) {
                                console.log('🔧 检测到样式变化，重新重置...');
                                self.resetGalleryStyles();
                            }
                        }
                    });
                });
                
                // 监控 wrapper 和 viewport
                if ($wrapper[0]) {
                    observer.observe($wrapper[0], { attributes: true, attributeFilter: ['style'] });
                }
                if ($viewport[0]) {
                    observer.observe($viewport[0], { attributes: true, attributeFilter: ['style'] });
                }
                
                console.log('✅ 样式监控已启动');
            }
        },
        
        /**
         * 切换到指定索引的图片
         * @param {number} index - 图片索引
         */
        switchToImage: function(index) {
            if (index < 0 || index >= this.currentImages.length) {
                console.warn('⚠️ 图片索引超出范围:', index);
                return;
            }
            
            console.log('🔄 切换到图片索引:', index);
            
            // 更新当前索引
            this.currentImageIndex = index;
            
            // 🔧 在切换前重置样式，防止 FlexSlider 干扰
            this.resetGalleryStyles();
            
            // 更新主图
            this.renderMainImage(this.currentImages[index]);
            
            // 更新缩略图选中状态
            this.updateThumbnailActiveState(index);
        },
        
        /**
         * 处理变体切换
         */
        handleVariationChange: function($button) {
            var variationData = $button.data('variation');
            
            if (!variationData) {
                console.warn('No variation data found');
                return;
            }
            
            console.log('🎯 变体切换:', variationData);
            
            // 1. 更新选中状态
            this.updateSelectedState($button);
            
            // 2. 更新图片数据源
            this.updateImageDataSource(variationData);
            
            // 3. 重新渲染图片库
            this.renderGallery();
            
            // 4. 更新价格和SKU
            this.updateProductPrice(variationData);
            this.updateProductSKU(variationData);
            
            // 5. 触发自定义事件
            $(document).trigger('evershop_variation_changed', [variationData]);
            
            // 6. 关键：同步到隐藏的 WooCommerce 默认表单
            this.syncToStandardForm($button);
        },

        /**
         * 同步选择到 WooCommerce 标准表单 (隐藏的)
         * @param {jQuery} $button 被点击的按钮
         */
        syncToStandardForm: function($button) {
            var attributeName = $button.data('attribute'); // 例如 attribute_pa_flavor
            var value = $button.data('value'); // 例如 Fruit Punch
            var variationId = $button.data('variation-id');
            var variationData = $button.data('variation'); // 获取完整的变体数据
            
            console.log('🔄 同步到标准表单:', attributeName, '=', value, 'ID:', variationId);
            
            // 1. 更新下拉框值
            // 注意：WooCommerce 的 select name 通常是 attribute_pa_flavor
            var $select = $('select[name="' + attributeName + '"]');
            if ($select.length) {
                $select.val(value).trigger('change');
            }
            
            // 2. 强制更新 variation_id 隐藏域
            var $variationInput = $('input.variation_id');
            if ($variationInput.length && variationId) {
                $variationInput.val(variationId).trigger('change');
                console.log('✅ variation_id 已更新为:', variationId);
            }
            
            // 3. 触发 found_variation 事件（重要！用于启用 Add to Cart 按钮）
            var $form = $('form.variations_form');
            if ($form.length && variationData) {
                // 延迟触发，确保所有更新完成
                setTimeout(function() {
                    $form.trigger('found_variation', [variationData]);
                    console.log('✅ found_variation 事件已触发');
                }, 50);
            }
        },
        
        /**
         * 更新图片数据源
         */
        updateImageDataSource: function(variation) {
            // 优先使用变体图片库
            if (variation.variation_gallery_images && variation.variation_gallery_images.length > 0) {
                console.log('📸 使用变体图片库:', variation.variation_gallery_images.length, '张图片');
                this.currentImages = variation.variation_gallery_images;
            } 
            // 否则使用变体的特色图片
            else if (variation.image && variation.image.src) {
                console.log('📸 使用变体特色图片');
                this.currentImages = [{
                    src: variation.image.src,
                    full_src: variation.image.full_src || variation.image.src,
                    srcset: variation.image.srcset || '',
                    sizes: variation.image.sizes || '',
                    alt: variation.image.alt || '',
                    title: variation.image.title || ''
                }];
            }
            // 如果没有变体图片，恢复原始图片
            else {
                console.log('📸 恢复原始图片');
                this.currentImages = this.originalImages.slice();
            }
            
            // 重置当前索引
            this.currentImageIndex = 0;
            
            console.log('✅ 当前图片数据源更新完成，共', this.currentImages.length, '张图片');
        },
        
        /**
         * 渲染整个图片库（主图 + 缩略图）
         */
        renderGallery: function() {
            console.log('🎨 重新渲染图片库...');
            
            if (this.currentImages.length === 0) {
                console.warn('⚠️ 没有图片可显示');
                return;
            }
            
            // 1. 渲染主图
            this.renderMainImage(this.currentImages[this.currentImageIndex]);
            
            // 2. 渲染缩略图列表
            this.renderThumbnails();
            
            console.log('✅ 图片库渲染完成');
        },
        
        /**
         * 渲染主图
         */
        renderMainImage: function(image) {
            console.log('🖼️ 渲染主图:', image.src ? image.src.substring(image.src.lastIndexOf('/') + 1) : '');
            
            var self = this;
            var $wrapper = $('.woocommerce-product-gallery__wrapper');
            if (!$wrapper.length) {
                console.error('❌ 找不到主图容器');
                return;
            }
            
            // 🔧 强制重置所有可能干扰的样式
            this.resetGalleryStyles();
            
            var $mainImage = $wrapper.find('.woocommerce-product-gallery__image').first();
            
            if (!$mainImage.length) {
                console.warn('⚠️ 创建新的主图容器');
                $mainImage = $('<div class="woocommerce-product-gallery__image"></div>');
                $wrapper.prepend($mainImage);
            }
            
            // 构建主图HTML
            var imgSrc = image.src || '';
            var fullSrc = image.full_src || imgSrc;
            var imgWidth = image.width || 1024;
            var imgHeight = image.height || 1024;
            
            var mainImageHTML = '<a href="' + fullSrc + '">' +
                '<img ' +
                'src="' + imgSrc + '" ' +
                'class="wp-post-image" ' +
                'alt="' + (image.alt || '') + '" ' +
                'title="' + (image.title || '') + '" ' +
                'data-large_image="' + fullSrc + '" ' +
                'data-large_image_width="' + imgWidth + '" ' +
                'data-large_image_height="' + imgHeight + '" ';
            
            if (image.srcset) {
                mainImageHTML += 'srcset="' + image.srcset + '" ';
            }
            if (image.sizes) {
                mainImageHTML += 'sizes="' + image.sizes + '" ';
            }
            
            mainImageHTML += '/></a>';
            
            // 更新DOM
            $mainImage.html(mainImageHTML);
            
            // 监听图片加载
            $mainImage.find('img').on('load', function() {
                console.log('✅ 主图加载成功');
                
                // 🎯 图片加载后，动态调整容器高度
                self.adjustGalleryHeight($(this));
            }).on('error', function() {
                console.error('❌ 主图加载失败:', imgSrc);
            });
        },
        
        /**
         * 重置图片库样式，移除 FlexSlider 的干扰
         */
        resetGalleryStyles: function() {
            var $wrapper = $('.woocommerce-product-gallery__wrapper');
            var $viewport = $('.flex-viewport');
            
            // 重置 wrapper 的 transform 和 transition
            $wrapper.css({
                'transform': 'translateX(0)',
                'transition': 'none',
                'width': 'auto'
            });
            
            // 移除 wrapper 上的 inline style 中的 FlexSlider 添加的属性
            if ($wrapper.length) {
                var style = $wrapper.attr('style') || '';
                // 移除可能的宽度设置（FlexSlider 会设置很大的宽度）
                style = style.replace(/width\s*:\s*[^;]+;?/gi, '');
                $wrapper.attr('style', style + ' transform: translateX(0) !important; transition: none !important;');
            }
            
            // 重置 viewport 的 transition
            if ($viewport.length) {
                $viewport.css({
                    'transition': 'none',
                    'overflow': 'hidden'
                });
            }
        },
        
        /**
         * 动态调整图片库高度
         * @param {jQuery} $img - 已加载的图片元素
         */
        adjustGalleryHeight: function($img) {
            if (!$img || !$img.length) return;
            
            var imgHeight = $img.height();
            var imgNaturalHeight = $img[0].naturalHeight;
            var actualHeight = imgHeight > 0 ? imgHeight : imgNaturalHeight;
            
            console.log('📐 调整容器高度:', actualHeight + 'px');
            
            // 设置 flex-viewport 的高度
            var $viewport = $('.flex-viewport');
            if ($viewport.length && actualHeight > 0) {
                $viewport.css({
                    'height': actualHeight + 'px',
                    'transition': 'none',
                    'overflow': 'hidden'
                });
            }
            
            // 确保 wrapper 的高度也正确
            var $wrapper = $('.woocommerce-product-gallery__wrapper');
            if ($wrapper.length) {
                $wrapper.css({
                    'height': 'auto',
                    'min-height': actualHeight + 'px'
                });
            }
            
            // 确保图片容器不会被缩略图遮挡
            var $gallery = $('.woocommerce-product-gallery');
            if ($gallery.length) {
                $gallery.css({
                    'margin-bottom': '20px' // 给缩略图留出空间
                });
            }
        },
        
        /**
         * 渲染缩略图列表
         */
        renderThumbnails: function() {
            console.log('🎞️ 渲染缩略图列表...');
            
            var $thumbsContainer = $('.flex-control-nav.flex-control-thumbs');
            
            if (!$thumbsContainer.length) {
                $thumbsContainer = $('.woocommerce-product-gallery ol');
            }
            
            if (!$thumbsContainer.length) {
                console.warn('⚠️ 找不到缩略图容器');
                return;
            }
            
            // 清空现有缩略图
            $thumbsContainer.empty();
            
            // 渲染每个缩略图
            var self = this;
            this.currentImages.forEach(function(image, index) {
                var thumbSrc = image.gallery_thumbnail_src || image.thumb_src || image.src;
                
                var $thumb = $('<li data-image-index="' + index + '">' +
                    '<img src="' + thumbSrc + '" ' +
                    'alt="' + (image.alt || '') + '" ' +
                    'draggable="false" />' +
                    '</li>');
                
                // 标记当前选中的缩略图
                if (index === self.currentImageIndex) {
                    $thumb.addClass('flex-active-slide');
                }
                
                $thumbsContainer.append($thumb);
            });
            
            console.log('✅ 已渲染', this.currentImages.length, '个缩略图');
        },
        
        /**
         * 更新缩略图选中状态
         */
        updateThumbnailActiveState: function(index) {
            var $thumbsContainer = $('.flex-control-nav.flex-control-thumbs');
            
            if (!$thumbsContainer.length) {
                $thumbsContainer = $('.woocommerce-product-gallery ol');
            }
            
            if (!$thumbsContainer.length) return;
            
            // 移除所有选中状态
            $thumbsContainer.find('li').removeClass('flex-active-slide');
            
            // 添加当前选中状态
            $thumbsContainer.find('li').eq(index).addClass('flex-active-slide');
        },
        
        /**
         * 选择默认变体
         */
        selectDefaultVariation: function() {
            var self = this;
            console.log('🎯 检查默认变体...');
            
            var $selectedButtons = $('.variant-option-item.selected .variant-option-button');
            
            if ($selectedButtons.length === 0) {
                console.log('⚠️ 没有默认选中的变体');
                return;
            }
            
            console.log('✅ 找到', $selectedButtons.length, '个默认选中的属性');
                
            var $firstSelectedButton = $selectedButtons.first();
            
            if ($firstSelectedButton.length > 0) {
                console.log('🔄 自动触发默认变体切换...');
                setTimeout(function() {
                    self.handleVariationChange($firstSelectedButton);
                }, 200);
            }
        },
        
        /**
         * 更新变体选中状态
         */
        updateSelectedState: function($button) {
            // 移除同组其他选项的选中状态
            $button.closest('.variant-option-list')
                   .find('.variant-option-item').removeClass('selected');
            
            // 添加当前选项的选中状态
            $button.closest('.variant-option-item').addClass('selected');

            // 更新 ARIA 属性
            $button.closest('.variant-option-list')
                   .find('.variant-option-item').attr('aria-selected', 'false');
            $button.closest('.variant-option-item').attr('aria-selected', 'true');
        },
        
        /**
         * 更新产品价格
         */
        updateProductPrice: function(variation) {
            if (!this.$priceContainer.length) {
                return;
            }
            
            // 获取货币符号 (需要通过 wp_localize_script 传递)
            // 如果 evershopVariantData 未定义，回退到默认 '£' (或 '$'，根据具体需求)
            var currencySymbol = (typeof evershopVariantData !== 'undefined' && evershopVariantData.currency_symbol) 
                ? evershopVariantData.currency_symbol 
                : '£';
            var priceHtml = '';
            
            if (variation.display_price !== variation.display_regular_price) {
                priceHtml = '<del><span class="woocommerce-Price-amount amount">' +
                    '<bdi><span class="woocommerce-Price-currencySymbol">' + currencySymbol + '</span>' + 
                    variation.display_regular_price + '</bdi></span></del> ';
            }
            
            priceHtml += '<ins><span class="woocommerce-Price-amount amount">' +
                '<bdi><span class="woocommerce-Price-currencySymbol">' + currencySymbol + '</span>' + 
                variation.display_price + '</bdi></span></ins>';
            
            this.$priceContainer.find('.price').html(priceHtml);
        },
        
        /**
         * 更新产品SKU
         */
        updateProductSKU: function(variation) {
            if (!this.$skuContainer.length || !variation.sku) {
                return;
            }
            
            this.$skuContainer.text(variation.sku);
        },
        
        /**
         * 添加无障碍属性
         */
        addAriaAttributes: function() {
            $('.variant-option-list li.selected').attr('aria-selected', 'true');
            $('.variant-option-list li:not(.selected)').attr('aria-selected', 'false');
            $('.variant-option-list li.un-available button').attr('aria-disabled', 'true');
        }
    };

    // 初始化
    $(document).ready(function() {
        EvershopVariantSelector.init();
    });

})(jQuery);
