/**
 * Custom Add to Cart & Quantity Selector JavaScript
 * 
 * EverShop Theme - 自定义购物车组件交互逻辑
 * 
 * @package EverShop_Theme
 * @version 1.0.0
 */

(function($) {
    'use strict';

    /**
     * 数量选择器类
     */
    class EverShopQuantitySelector {
        constructor(wrapper) {
            this.$wrapper = $(wrapper);
            this.$input = this.$wrapper.find('input.qty');
            this.$minusBtn = this.$wrapper.find('.quantity-minus');
            this.$plusBtn = this.$wrapper.find('.quantity-plus');
            
            this.min = parseInt(this.$input.attr('min')) || 1;
            this.max = parseInt(this.$input.attr('max')) || Infinity;
            this.step = parseInt(this.$input.attr('step')) || 1;
            
            this.init();
        }
        
        init() {
            // 减少数量
            this.$minusBtn.on('click', () => {
                this.decrease();
            });
            
            // 增加数量
            this.$plusBtn.on('click', () => {
                this.increase();
            });
            
            // 输入框变化时验证
            this.$input.on('change', () => {
                this.validate();
            });
            
            // 初始验证
            this.validate();
        }
        
        getCurrentValue() {
            return parseInt(this.$input.val()) || this.min;
        }
        
        setValue(value) {
            this.$input.val(value).trigger('change');
        }
        
        decrease() {
            const current = this.getCurrentValue();
            const newValue = Math.max(this.min, current - this.step);
            this.setValue(newValue);
        }
        
        increase() {
            const current = this.getCurrentValue();
            const newValue = Math.min(this.max, current + this.step);
            this.setValue(newValue);
        }
        
        validate() {
            let value = this.getCurrentValue();
            
            // 限制在最小值和最大值之间
            if (value < this.min) {
                value = this.min;
            } else if (value > this.max) {
                value = this.max;
            }
            
            this.$input.val(value);
            
            // 更新按钮状态
            this.$minusBtn.prop('disabled', value <= this.min);
            this.$plusBtn.prop('disabled', value >= this.max);
        }
    }

    /**
     * Add to Cart 按钮类
     */
    class EverShopAddToCartButton {
        constructor(button) {
            this.$button = $(button);
            this.$form = this.$button.closest('form.cart');
            this.isVariableProduct = this.$form.hasClass('variations_form');
            
            this.init();
        }
        
        init() {
            // 监听表单提交
            this.$form.on('submit', (e) => {
                if (this.isVariableProduct) {
                    // 变体商品：检查是否选择了所有必需属性
                    const variationId = this.$form.find('input.variation_id').val();
                    if (!variationId || variationId === '0') {
                        e.preventDefault();
                        this.showError('请选择所有必需的商品选项');
                        return false;
                    }
                }
                
                // 显示加载状态
                this.setLoading(true);
            });
            
            // 变体商品：监听变体变化
            if (this.isVariableProduct) {
                this.$form.on('found_variation', (e, variation) => {
                    this.onVariationFound(variation);
                });
                
                this.$form.on('reset_data', () => {
                    this.onVariationReset();
                });
            }
        }
        
        setLoading(isLoading) {
            if (isLoading) {
                this.$button.addClass('loading');
                this.$button.prop('disabled', true);
            } else {
                this.$button.removeClass('loading');
                this.$button.prop('disabled', false);
            }
        }
        
        setDisabled(isDisabled) {
            if (isDisabled) {
                this.$button.addClass('disabled');
                this.$button.prop('disabled', true);
            } else {
                this.$button.removeClass('disabled');
                this.$button.prop('disabled', false);
            }
        }
        
        onVariationFound(variation) {
            // 更新数量输入框的最大值
            const $qtyInput = this.$form.find('input.qty');
            const maxQty = variation.max_qty || '';
            $qtyInput.attr('max', maxQty);
            
            // 启用按钮
            this.setDisabled(false);
            
            // 更新按钮文字（如果变体有自定义文字）
            if (variation.is_in_stock) {
                this.$button.find('.button-text').text(
                    variation.availability_html ? 'Add to Cart' : 'Add to Cart'
                );
            } else {
                this.$button.find('.button-text').text('Out of Stock');
                this.setDisabled(true);
            }
        }
        
        onVariationReset() {
            // 禁用按钮
            this.setDisabled(true);
            
            // 重置按钮文字
            this.$button.find('.button-text').text('Select options');
        }
        
        showError(message) {
            // 可以集成你的通知系统
            alert(message);
        }
    }

    /**
     * 初始化所有组件
     */
    function initComponents() {
        // 初始化数量选择器
        $('.quantity-input-wrapper').each(function() {
            new EverShopQuantitySelector(this);
        });
        
        // 初始化 Add to Cart 按钮
        $('.evershop-add-to-cart-button').each(function() {
            new EverShopAddToCartButton(this);
        });
    }

    /**
     * AJAX Add to Cart（可选功能）
     */
    function setupAjaxAddToCart() {
        $(document).on('submit', 'form.cart:not(.variations_form)', function(e) {
            const $form = $(this);
            const $button = $form.find('.evershop-add-to-cart-button');
            
            // 如果已经在加载，阻止重复提交
            if ($button.hasClass('loading')) {
                e.preventDefault();
                return false;
            }
            
            // 这里可以添加 AJAX 提交逻辑
            // 如果你想使用默认的同步提交，就不需要阻止默认行为
        });
    }

    /**
     * 变体商品：与自定义变体选择器集成
     */
    function integrateWithVariantSelector() {
        // 监听自定义变体选择器的变化
        $(document).on('click', '.variant-option-button', function() {
            const $button = $(this);
            const $form = $('form.variations_form');
            
            if ($form.length === 0) return;
            
            // 获取变体数据
            const variationId = $button.data('variation-id');
            const attribute = $button.data('attribute');
            const value = $button.data('value');
            
            // 方法1：通过按钮自带的完整变体数据更新
            const variationData = $button.data('variation');
            if (variationData && variationData.variation_id) {
                // 直接更新 variation_id 隐藏字段
                $form.find('input.variation_id').val(variationData.variation_id);
                
                // 触发 found_variation 事件（通知其他脚本）
                $form.trigger('found_variation', [variationData]);
                
            }
            
            const $select = $form.find('select[name="' + attribute + '"]');
            if ($select.length > 0) {
                $select.val(value).trigger('change');
            }
        });
        
        // 监听变体重置
        $(document).on('reset_data', 'form.variations_form', function() {
            const $form = $(this);
            $form.find('input.variation_id').val('0');
        });
    }

    /**
     * 文档加载完成后初始化
     */
    $(document).ready(function() {
        initComponents();
        setupAjaxAddToCart();
        integrateWithVariantSelector();
    });

    /**
     * 支持动态内容
     */
    $(document).on('DOMContentLoaded updated_cart_totals updated_checkout', function() {
        initComponents();
    });

})(jQuery);

