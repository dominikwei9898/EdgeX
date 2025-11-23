<?php
/**
 * Mini Cart Template
 * 
 * 基于 EverShop DefaultMiniCartDropdown.tsx
 * 侧边栏弹出式购物车
 * 
 * @package Gym_Nutrition_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// 确保 WooCommerce 已激活
if (!class_exists('WooCommerce')) {
    return;
}
?>

<!-- Mini Cart Overlay & Dropdown -->
<div id="minicart-wrapper" class="minicart-wrapper" style="display: none;">
    <!-- 背景遮罩 -->
    <div class="minicart-overlay" id="minicart-overlay"></div>
    
    <!-- 侧边栏购物车 -->
    <div class="minicart-dropdown" id="minicart-dropdown" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Shopping Cart', 'gym-nutrition-theme'); ?>">
        
        <!-- 头部 -->
        <div class="minicart-header">
            <h3 class="minicart-title"><?php esc_html_e('Your Cart', 'gym-nutrition-theme'); ?></h3>
            <button type="button" 
                    class="minicart-close" 
                    id="minicart-close-btn"
                    aria-label="<?php esc_attr_e('Close cart', 'gym-nutrition-theme'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="close-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <!-- 购物车内容容器 -->
        <div class="minicart-content" id="minicart-content">
            <!-- 空购物车状态 -->
            <div class="minicart-empty" id="minicart-empty" style="display: none;">
                <div class="empty-cart-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                </div>
                <p class="empty-cart-text"><?php esc_html_e('Your cart is empty', 'gym-nutrition-theme'); ?></p>
                <button type="button" class="continue-shopping-btn" id="continue-shopping-btn">
                    <?php esc_html_e('Continue Shopping', 'gym-nutrition-theme'); ?>
                </button>
            </div>
            
            <!-- 加载状态 -->
            <div class="minicart-loading" id="minicart-loading" style="display: none;">
                <div class="loading-spinner"></div>
                <p><?php esc_html_e('Loading...', 'gym-nutrition-theme'); ?></p>
            </div>
            
            <!-- 商品列表 -->
            <div class="minicart-items-container" id="minicart-items-container" style="display: none;">
                <div class="minicart-items-scroll">
                    <ul class="minicart-items-list" id="minicart-items-list">
                        <!-- 通过 JavaScript 动态填充 -->
                    </ul>
                </div>
                
                <!-- 底部结算区域 -->
                <div class="minicart-summary">
                    <div class="minicart-subtotal">
                        <span class="subtotal-label"><?php esc_html_e('Subtotal', 'gym-nutrition-theme'); ?>:</span>
                        <span class="subtotal-value" id="minicart-subtotal"><?php echo get_woocommerce_currency_symbol(); ?>0.00</span>
                    </div>
                    <button type="button" 
                            class="minicart-checkout-btn" 
                            id="minicart-checkout-btn">
                        <?php esc_html_e('Checkout', 'gym-nutrition-theme'); ?> (<span id="minicart-total-qty">0</span>)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

