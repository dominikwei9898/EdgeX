/**
 * Mini Cart JavaScript
 * 
 * 基于 EverShop MiniCart.tsx 功能
 * 使用原生 JavaScript + WooCommerce AJAX API
 * 
 * @package Gym_Nutrition_Theme
 */

(function() {
    'use strict';

    // ============================================
    // 状态管理
    // ============================================
    const MiniCart = {
        wrapper: null,
        dropdown: null,
        overlay: null,
        closeBtn: null,
        icon: null,
        isOpen: false,
        cartData: null,
        localQuantities: {}, // 本地数量存储（模拟 EverShop 的 localStorage 机制）
        isUpdating: false, // 标志位：是否正在进行内部更新
        currencySymbol: evershopMiniCart.currency_symbol || '£',    

        // ============================================
        // 初始化
        // ============================================
        init: function() {
            // 获取 DOM 元素
            this.wrapper = document.getElementById('minicart-wrapper');
            this.dropdown = document.getElementById('minicart-dropdown');
            this.overlay = document.getElementById('minicart-overlay');
            this.closeBtn = document.getElementById('minicart-close-btn');
            this.icon = document.getElementById('minicart-trigger');
            this.continueShoppingBtn = document.getElementById('continue-shopping-btn');
            this.checkoutBtn = document.getElementById('minicart-checkout-btn');

            if (!this.wrapper || !this.icon) {
                console.warn('Mini cart elements not found');
                return;
            }

            // 绑定事件
            this.bindEvents();

            // 监听 WooCommerce 购物车更新事件
            jQuery(document.body).on('wc_fragments_refreshed wc_fragments_loaded added_to_cart removed_from_cart', () => {
                if (this.isOpen && !this.isUpdating) {
                    this.loadCart();
                }
            });
        },

        // ============================================
        // 事件绑定
        // ============================================
        bindEvents: function() {
            // 点击购物车图标打开
            this.icon.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggle();
            });

            // 关闭按钮
            if (this.closeBtn) {
                this.closeBtn.addEventListener('click', () => {
                    this.close();
                });
            }

            // 遮罩层点击关闭
            if (this.overlay) {
                this.overlay.addEventListener('click', () => {
                    this.close();
                });
            }

            // ESC 键关闭
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.close();
                }
            });

            // Continue Shopping 按钮
            if (this.continueShoppingBtn) {
                this.continueShoppingBtn.addEventListener('click', () => {
                    this.close();
                });
            }

            // Checkout 按钮
            if (this.checkoutBtn) {
                this.checkoutBtn.addEventListener('click', () => {
                    window.location.href = evershopMiniCart.checkout_url || '/checkout';
                });
            }
        },

        // ============================================
        // 打开/关闭
        // ============================================
        toggle: function() {
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        },

        open: function() {
            this.isOpen = true;
            this.wrapper.style.display = 'block';
            
            // 触发重排以启动动画
            setTimeout(() => {
                this.wrapper.classList.add('active');
                document.body.classList.add('minicart-open');
            }, 10);

            // 加载购物车数据
            this.loadCart();

            // 聚焦关闭按钮
            setTimeout(() => {
                if (this.closeBtn) {
                    this.closeBtn.focus();
                }
            }, 100);
        },

        close: function() {
            this.isOpen = false;
            this.wrapper.classList.remove('active');
            document.body.classList.remove('minicart-open');

            // 动画结束后隐藏
            setTimeout(() => {
                if (!this.isOpen) {
                    this.wrapper.style.display = 'none';
                }
            }, 300);
        },

        // ============================================
        // 加载购物车数据
        // ============================================
        loadCart: function() {
            this.showLoading();

            // 使用 WooCommerce AJAX API 获取购物车
            jQuery.ajax({
                url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'),
                type: 'POST',
                success: (response) => {
                    if (response && response.fragments) {
                        // 从 fragments 中提取购物车数据
                        this.parseCartData(response.fragments);
                        this.render();
                    } else {
                        this.showError();
                    }
                },
                error: () => {
                    this.showError();
                }
            });
        },

        // ============================================
        // 解析购物车数据
        // ============================================
        parseCartData: function(fragments) {
            // 获取购物车对象
            const cart = typeof wc_cart_fragments_params !== 'undefined' ? 
                         wc_cart_fragments_params : null;

            // 通过 AJAX 获取购物车详细信息
            jQuery.ajax({
                url: evershopMiniCart.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_minicart_data',
                    nonce: evershopMiniCart.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.cartData = response.data;
                        this.render();
                    } else {
                        this.showError();
                    }
                },
                error: () => {
                    this.showError();
                }
            });
        },

        // ============================================
        // 渲染购物车
        // ============================================
        render: function() {
            const emptyEl = document.getElementById('minicart-empty');
            const loadingEl = document.getElementById('minicart-loading');
            const containerEl = document.getElementById('minicart-items-container');

            // 隐藏所有状态
            emptyEl.style.display = 'none';
            loadingEl.style.display = 'none';
            containerEl.style.display = 'none';

            if (!this.cartData || !this.cartData.items || this.cartData.items.length === 0) {
                // 显示空购物车
                emptyEl.style.display = 'flex';
            } else {
                // 显示商品列表
                containerEl.style.display = 'flex';
                this.renderItems();
                this.updateSummary();
            }
        },

        // ============================================
        // 渲染商品列表
        // ============================================
        renderItems: function() {
            const listEl = document.getElementById('minicart-items-list');
            if (!listEl) return;

            listEl.innerHTML = '';

            this.cartData.items.forEach((item) => {
                const itemEl = this.createItemElement(item);
                listEl.appendChild(itemEl);
            });
        },

        // ============================================
        // 创建商品元素
        // ============================================
        createItemElement: function(item) {
            const li = document.createElement('li');
            li.className = 'minicart-item';
            li.dataset.cartItemKey = item.key;

            // 获取本地数量或服务器数量
            const displayQty = this.localQuantities[item.key] || item.quantity;
            const lineTotal = (parseFloat(item.price) * displayQty).toFixed(2);

            li.innerHTML = `
                <div class="minicart-item-image-wrapper">
                    <img src="${item.image || wc_add_to_cart_params.i18n_view_cart}" 
                         alt="${this.escapeHtml(item.name)}" 
                         class="minicart-item-image">
                    <span class="minicart-item-qty-badge">${displayQty}</span>
                </div>
                <div class="minicart-item-info">
                    <h3 class="minicart-item-name">${this.escapeHtml(item.name)}</h3>
                    ${item.variation ? `<div class="minicart-item-variants">${item.variation}</div>` : ''}
                    <p class="minicart-item-unit-price">${this.currencySymbol}${parseFloat(item.price).toFixed(2)} each</p>
                    <div class="minicart-item-qty-controls">
                        <button class="qty-btn qty-decrease" data-key="${item.key}" ${displayQty <= 1 ? 'disabled' : ''}>
                            −
                        </button>
                        <span class="qty-value">${displayQty}</span>
                        <button class="qty-btn qty-increase" data-key="${item.key}">
                            +
                        </button>
                    </div>
                </div>
                <div class="minicart-item-actions">
                    <div class="minicart-item-total-price">${this.currencySymbol}${lineTotal}</div>
                    <button class="minicart-item-remove" data-key="${item.key}" aria-label="Remove item">
                        <svg class="remove-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </div>
            `;

            // 绑定事件
            this.bindItemEvents(li, item);

            return li;
        },

        // ============================================
        // 绑定商品事件
        // ============================================
        bindItemEvents: function(itemEl, item) {
            // 数量增加
            const increaseBtn = itemEl.querySelector('.qty-increase');
            if (increaseBtn) {
                increaseBtn.addEventListener('click', () => {
                    this.updateQuantity(item.key, 'increase');
                });
            }

            // 数量减少
            const decreaseBtn = itemEl.querySelector('.qty-decrease');
            if (decreaseBtn) {
                decreaseBtn.addEventListener('click', () => {
                    this.updateQuantity(item.key, 'decrease');
                });
            }

            // 删除商品
            const removeBtn = itemEl.querySelector('.minicart-item-remove');
            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    this.removeItem(item.key);
                });
            }
        },

        // ============================================
        // 更新数量（前端优先，类似 EverShop）
        // ============================================
        updateQuantity: function(key, action) {
            // 查找商品
            const item = this.cartData.items.find(i => i.key === key);
            if (!item) return;

            // 计算新数量
            const currentQty = this.localQuantities[key] || item.quantity;
            let newQty = action === 'increase' ? currentQty + 1 : currentQty - 1;

            if (newQty < 1) newQty = 1;

            // 保存到本地状态
            this.localQuantities[key] = newQty;

            // 立即更新 UI
            this.renderItems();
            this.updateSummary();

            // 后台同步到服务器（防抖）
            clearTimeout(this._syncTimeout);
            this._syncTimeout = setTimeout(() => {
                this.syncQuantityToServer(key, newQty);
            }, 800);
        },

        // ============================================
        // 同步数量到服务器
        // ============================================
        syncQuantityToServer: function(key, quantity) {
            jQuery.ajax({
                url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'update_cart_item_qty'),
                type: 'POST',
                data: {
                    cart_item_key: key,
                    quantity: quantity
                },
                success: (response) => {
                    if (response && response.fragments) {
                        // 清除本地数量（已同步）
                        delete this.localQuantities[key];
                        
                        // 更新 fragments
                        jQuery.each(response.fragments, function(key, value) {
                            jQuery(key).replaceWith(value);
                        });

                        // 重新加载购物车
                        this.loadCart();
                    }
                },
                error: () => {
                    console.error('Failed to update quantity');
                    // 回滚本地数量
                    delete this.localQuantities[key];
                    this.renderItems();
                }
            });
        },

        // ============================================
        // 删除商品
        // ============================================
        removeItem: function(key) {
            // 标记正在更新，防止外部事件触发重载
            this.isUpdating = true;

            const itemEl = document.querySelector(`[data-cart-item-key="${key}"]`);
            
            // 乐观更新：先从 UI 中移除
            if (itemEl) {
                itemEl.remove();
            }

            // 更新本地数据模型
            if (this.cartData && this.cartData.items) {
                this.cartData.items = this.cartData.items.filter(item => item.key !== key);
            }
            
            // 清除本地数量记录
            delete this.localQuantities[key];

            // 立即更新汇总信息
            this.updateSummary();

            // 检查是否为空
            if (!this.cartData.items || this.cartData.items.length === 0) {
                const emptyEl = document.getElementById('minicart-empty');
                const containerEl = document.getElementById('minicart-items-container');
                if (emptyEl && containerEl) {
                    containerEl.style.display = 'none';
                    emptyEl.style.display = 'flex';
                }
            }

            // 发送删除请求到服务器
            jQuery.ajax({
                url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'remove_from_cart'),
                type: 'POST',
                data: {
                    cart_item_key: key
                },
                success: (response) => {
                    // 延时重置标志位，确保 removed_from_cart 事件处理完毕
                    setTimeout(() => {
                        this.isUpdating = false;
                    }, 500);

                    if (response && response.fragments) {
                        // 更新 fragments (如头部购物车图标数量)
                        jQuery.each(response.fragments, function(key, value) {
                            jQuery(key).replaceWith(value);
                        });
                        
                        // 这里不再调用 loadCart()，因为 UI 已经通过乐观更新完成了
                    }
                },
                error: () => {
                    console.error('Failed to remove item');
                    this.isUpdating = false;
                    // 失败时重新加载以恢复状态
                    this.loadCart();
                }
            });
        },

        // ============================================
        // 更新底部汇总
        // ============================================
        updateSummary: function() {
            if (!this.cartData) return;

            let totalQty = 0;
            let subtotal = 0;

            this.cartData.items.forEach((item) => {
                const qty = this.localQuantities[item.key] || item.quantity;
                totalQty += qty;
                subtotal += parseFloat(item.price) * qty;
            });

            // 更新数量
            const qtyEl = document.getElementById('minicart-total-qty');
            if (qtyEl) {
                qtyEl.textContent = totalQty;
            }

            // 更新小计
            const subtotalEl = document.getElementById('minicart-subtotal');
            if (subtotalEl) {
                subtotalEl.textContent = `${this.currencySymbol}${subtotal.toFixed(2)}`;
            }

            // 更新头部购物车图标数量
            this.updateIconBadge(totalQty);
        },

        // ============================================
        // 更新图标徽章
        // ============================================
        updateIconBadge: function(count) {
            const badge = this.icon.querySelector('.cart-count-badge');
            if (badge) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = count > 0 ? 'flex' : 'none';
            }
        },

        // ============================================
        // 显示加载状态
        // ============================================
        showLoading: function() {
            const emptyEl = document.getElementById('minicart-empty');
            const loadingEl = document.getElementById('minicart-loading');
            const containerEl = document.getElementById('minicart-items-container');

            emptyEl.style.display = 'none';
            loadingEl.style.display = 'flex';
            containerEl.style.display = 'none';
        },

        // ============================================
        // 显示错误
        // ============================================
        showError: function() {
            const emptyEl = document.getElementById('minicart-empty');
            const loadingEl = document.getElementById('minicart-loading');
            const containerEl = document.getElementById('minicart-items-container');

            emptyEl.style.display = 'none';
            loadingEl.style.display = 'none';
            containerEl.style.display = 'none';

            // 显示错误消息
            alert('Failed to load cart. Please refresh the page.');
        },

        // ============================================
        // 工具函数：HTML 转义
        // ============================================
        escapeHtml: function(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    };

    // ============================================
    // DOM Ready
    // ============================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            MiniCart.init();
        });
    } else {
        MiniCart.init();
    }

})();

