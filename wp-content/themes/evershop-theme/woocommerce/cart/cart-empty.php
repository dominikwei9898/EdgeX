<?php
/**
 * Empty cart page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-empty.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

$is_cancelled = isset($_GET['cancel_order']) && $_GET['cancel_order'] === 'true';
?>

<div class="empty-cart-wrapper" style="min-height: 60vh; display: flex; align-items: center; justify-content: center; padding: 2rem 0;">
    <div class="glass-panel empty-cart-content">
        
        <div class="icon-wrapper" style="margin-bottom: 2rem;">
            <?php if ($is_cancelled) : ?>
                <!-- Cancelled Icon -->
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            <?php else : ?>
                <!-- Empty Cart Icon -->
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            <?php endif; ?>
        </div>

        <?php if ($is_cancelled) : ?>
            <h2 class="empty-title"><?php esc_html_e( 'Order Cancelled', 'woocommerce' ); ?></h2>
            <p class="empty-message">
                <?php 
                if (isset($_GET['order_id'])) {
                    // Translators: %s: Order ID.
                    printf( esc_html__( 'Order #%s has been cancelled.', 'woocommerce' ), intval($_GET['order_id']) );
                } else {
                    esc_html_e( 'Your order has been cancelled.', 'woocommerce' );
                }
                ?>
                <br>
                <span style="display: block; margin-top: 0.5rem; color: #888;">
                    <?php esc_html_e( 'No items were charged. Your cart is now empty.', 'woocommerce' ); ?>
                </span>
            </p>
        <?php else : ?>
            <h2 class="empty-title"><?php esc_html_e( 'Your cart is currently empty', 'woocommerce' ); ?></h2>
            <p class="empty-message">
                <?php do_action( 'woocommerce_cart_is_empty' ); ?>
            </p>
        <?php endif; ?>

        <?php if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
            <p class="return-to-shop">
                <a class="button wc-backward cutler-primary-button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
                    <?php
                        /**
                         * Filter "Return To Shop" text.
                         *
                         * @since 4.6.0
                         * @param string $default_text Default text.
                         */
                        echo esc_html( apply_filters( 'woocommerce_return_to_shop_text', __( 'Return to shop', 'woocommerce' ) ) );
                    ?>
                </a>
            </p>
        <?php endif; ?>
        
    </div>
</div>

<style>
/* 复制并复用 form-login.php 的风格 */
.glass-panel {
    background: rgba(42, 42, 42, 0.9); 
    backdrop-filter: blur(20px); 
    border-radius: 24px; 
    padding: 4rem 3rem; 
    border: 1px solid rgba(255, 255, 255, 0.08); 
    box-shadow: 
        0 20px 60px rgba(0, 0, 0, 0.4),
        inset 0 0 0 1px rgba(255, 255, 255, 0.05);
    text-align: center;
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
}

.empty-title {
    color: #ffffff;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1rem;
    letter-spacing: -0.5px;
}

.empty-message {
    color: #ccc;
    font-size: 1.1rem;
    line-height: 1.6;
    margin-bottom: 2.5rem;
}

.cutler-primary-button {
    display: inline-block;
    background: linear-gradient(135deg, #ff6b35 0%, #e55a2b 100%) !important; 
    color: #ffffff !important; 
    border: none !important; 
    border-radius: 14px !important; 
    padding: 1rem 2.5rem !important; 
    font-weight: 700 !important; 
    font-size: 1.1rem !important; 
    text-transform: uppercase !important; 
    letter-spacing: 1.5px !important; 
    cursor: pointer !important; 
    text-decoration: none !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important; 
    box-shadow: 0 8px 20px rgba(255, 107, 53, 0.25) !important; 
}

.cutler-primary-button:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 12px 30px rgba(255, 107, 53, 0.4) !important;
    background: linear-gradient(135deg, #ff7645 0%, #f5501b 100%) !important;
    color: #fff !important;
}

@media (max-width: 767px) {
    .glass-panel {
        padding: 3rem 1.5rem;
    }
    .empty-title {
        font-size: 1.5rem;
    }
}
</style>

