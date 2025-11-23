<?php
/**
 * Custom Simple Product Add to Cart
 * 
 * EverShop Theme - 自定义单品商品 Add to Cart 组件
 * 覆盖 WooCommerce 默认模板
 * 
 * @package EverShop_Theme
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

global $product;

if (!$product->is_purchasable()) {
    return;
}

echo wc_get_stock_html($product);

if ($product->is_in_stock()) : ?>

    <?php do_action('woocommerce_before_add_to_cart_form'); ?>

    <form class="cart evershop-cart-form" 
          action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" 
          method="post" 
          enctype='multipart/form-data'>
        
        <?php do_action('woocommerce_before_add_to_cart_button'); ?>

        <div class="evershop-add-to-cart-container">
            <!-- 自定义数量选择器 -->
            <div class="evershop-quantity-selector">
                <?php
                do_action('woocommerce_before_add_to_cart_quantity');
                
                // 获取最小/最大数量
                $min_qty = $product->get_min_purchase_quantity();
                $max_qty = $product->get_max_purchase_quantity();
                $default_qty = isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $min_qty;
                ?>
                
                <label for="quantity_<?php echo esc_attr($product->get_id()); ?>" class="screen-reader-text">
                    <?php esc_html_e('Quantity', 'woocommerce'); ?>
                </label>
                
                <div class="quantity-input-wrapper">
                    <button type="button" 
                            class="quantity-btn quantity-minus" 
                            aria-label="<?php esc_attr_e('Decrease quantity', 'evershop-theme'); ?>">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 8H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                    
                    <input type="number"
                           id="quantity_<?php echo esc_attr($product->get_id()); ?>"
                           class="input-text qty text"
                           name="quantity"
                           value="<?php echo esc_attr($default_qty); ?>"
                           min="<?php echo esc_attr($min_qty); ?>"
                           max="<?php echo esc_attr($max_qty > 0 ? $max_qty : ''); ?>"
                           step="1"
                           inputmode="numeric"
                           autocomplete="off" />
                    
                    <button type="button" 
                            class="quantity-btn quantity-plus" 
                            aria-label="<?php esc_attr_e('Increase quantity', 'evershop-theme'); ?>">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 3V13M3 8H13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
                
                <?php do_action('woocommerce_after_add_to_cart_quantity'); ?>
            </div>

            <!-- 自定义 Add to Cart 按钮 -->
            <button type="submit" 
                    name="add-to-cart" 
                    value="<?php echo esc_attr($product->get_id()); ?>" 
                    class="evershop-add-to-cart-button single_add_to_cart_button button alt">
                <span class="button-text">
                    <?php echo esc_html($product->single_add_to_cart_text()); ?>
                </span>
                <span class="button-loader" style="display: none;">
                    <svg class="spinner" width="20" height="20" viewBox="0 0 50 50">
                        <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                    </svg>
                </span>
            </button>
        </div>

        <?php do_action('woocommerce_after_add_to_cart_button'); ?>
    </form>

    <?php do_action('woocommerce_after_add_to_cart_form'); ?>

<?php endif; ?>

