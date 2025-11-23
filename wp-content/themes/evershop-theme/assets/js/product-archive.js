/**
 * Product Archive - Add to Cart Handler
 * 
 * 处理产品分类页面的添加到购物车功能
 * 添加产品后自动打开 mini cart
 * 
 * @package Evershop_Theme
 */

(function() {
    'use strict';

    const ProductArchive = {
        /**
         * 初始化
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * 绑定事件
         */
        bindEvents: function() {
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('product-card-add-to-cart')) {
                    e.preventDefault();
                    this.handleAddToCart(e.target);
                }
            });
        },

        /**
         * 处理添加到购物车
         */
        handleAddToCart: function(button) {
            const productId = button.getAttribute('data-product-id');
            const productName = button.getAttribute('data-product-name');

            if (!productId) {
                console.error('Product ID not found');
                return;
            }

            // 禁用按钮，显示加载状态
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Adding...';
            button.classList.add('loading');

            // 使用 WooCommerce AJAX API 添加到购物车
            jQuery.ajax({
                url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
                type: 'POST',
                data: {
                    product_id: productId,
                    quantity: 1
                },
                success: (response) => {
                    if (response.error) {
                        // 显示错误
                        this.showError(button, response.error_message || 'Failed to add to cart');
                        return;
                    }

                    // 触发 WooCommerce 购物车更新事件
                    jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, jQuery(button)]);

                    // 移除 WooCommerce 自动添加的 "View basket" 链接
                    this.removeViewBasketLink(button);

                    // 更新按钮状态
                    button.textContent = 'Added!';
                    button.classList.remove('loading');
                    button.classList.add('added');

                    // 短暂延迟后恢复按钮状态
                    setTimeout(() => {
                        button.disabled = false;
                        button.textContent = originalText;
                        button.classList.remove('added');
                    }, 2000);

                    // 打开 mini cart
                    this.openMiniCart();
                },
                error: (xhr, status, error) => {
                    console.error('Add to cart error:', error);
                    this.showError(button, 'Failed to add to cart. Please try again.');
                }
            });
        },

        /**
         * 显示错误信息
         */
        showError: function(button, message) {
            button.disabled = false;
            button.textContent = 'Try Again';
            button.classList.remove('loading');
            button.classList.add('error');

            // 显示错误提示（可以使用 alert 或自定义提示）
            alert(message);

            // 恢复按钮状态
            setTimeout(() => {
                button.textContent = 'Add to basket';
                button.classList.remove('error');
            }, 3000);
        },

        /**
         * 移除 WooCommerce 自动添加的 "View basket" 链接
         */
        removeViewBasketLink: function(button) {
            // 延迟执行，确保链接已经被添加
            setTimeout(() => {
                // 查找按钮后面的 "View basket" 链接
                const productCard = button.closest('li.product');
                if (productCard) {
                    const viewBasketLink = productCard.querySelector('.added_to_cart');
                    if (viewBasketLink) {
                        viewBasketLink.remove();
                    }
                }
            }, 100);
        },

        /**
         * 打开 Mini Cart
         */
        openMiniCart: function() {
            // 等待一小段时间确保购物车数据已更新
            setTimeout(() => {
                // 触发 mini cart 的打开事件
                const miniCartIcon = document.getElementById('minicart-trigger');
                if (miniCartIcon) {
                    miniCartIcon.click();
                } else {
                    console.warn('Mini cart trigger not found');
                }
            }, 300);
        }
    };

    // DOM 加载完成后初始化
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            ProductArchive.init();
        });
    } else {
        ProductArchive.init();
    }

})();

