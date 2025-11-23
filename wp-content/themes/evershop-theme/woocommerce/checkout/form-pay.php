<?php
/**
 * Pay for order form - 自定义模板
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-pay.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.2.0
 */

defined( 'ABSPATH' ) || exit;

$totals = $order->get_order_item_totals(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
?>

<div class="cutler-order-pay-wrapper" style="min-height: 100vh; background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 50%, #0f0f0f 100%); padding: 2rem 0; position: relative;">
    <!-- 背景装饰 -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 100 100\"><defs><pattern id=\"grain\" width=\"100\" height=\"100\" patternUnits=\"userSpaceOnUse\"><circle cx=\"50\" cy=\"50\" r=\"0.5\" fill=\"%23ffffff\" opacity=\"0.02\"/></pattern></defs><rect width=\"100\" height=\"100\" fill=\"url(%23grain)\"/></svg>'); pointer-events: none;"></div>
    
    <div class="cutler-order-pay-container" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem; position: relative; z-index: 1;">
        
        <!-- 简化的订单信息头部 -->
        <div class="order-info-compact" style="display: flex; justify-content: space-between; align-items: center; background: rgba(42, 42, 42, 0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 16px; padding: 1.5rem 2rem; margin-bottom: 3rem; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);">
            <div style="display: flex; gap: 3rem; flex-wrap: wrap;">
                <div>
                    <p style="color: #888; font-size: 0.9rem; margin: 0; text-transform: uppercase; letter-spacing: 1px;">ORDER NUMBER:</p>
                    <p style="color: #ffffff; font-size: 1.2rem; font-weight: 700; margin: 0.25rem 0 0 0;"><?php echo $order->get_order_number(); ?></p>
                </div>
                <div>
                    <p style="color: #888; font-size: 0.9rem; margin: 0; text-transform: uppercase; letter-spacing: 1px;">DATE:</p>
                    <p style="color: #ffffff; font-size: 1.2rem; font-weight: 700; margin: 0.25rem 0 0 0;"><?php echo $order->get_date_created()->format('j F Y'); ?></p>
                </div>
                <div>
                    <p style="color: #888; font-size: 0.9rem; margin: 0; text-transform: uppercase; letter-spacing: 1px;">TOTAL:</p>
                    <p style="color: #ff6b35; font-size: 1.4rem; font-weight: 700; margin: 0.25rem 0 0 0;"><?php echo wc_price( $order->get_total() ); ?></p>
                </div>
                <div>
                    <p style="color: #888; font-size: 0.9rem; margin: 0; text-transform: uppercase; letter-spacing: 1px;">PAYMENT METHOD:</p>
                    <p style="color: #ffffff; font-size: 1.2rem; font-weight: 700; margin: 0.25rem 0 0 0;">Credit Card</p>
                </div>
            </div>
        </div>

        <div class="order-pay-content" style="display: grid; grid-template-columns: 1fr; gap: 2rem; max-width: 600px; margin: 0 auto;">
            
            <!-- 中央支付区域 -->
            <div class="payment-section-modern" style="background: rgba(42, 42, 42, 0.9); backdrop-filter: blur(20px); border-radius: 20px; padding: 3rem; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4); position: relative; overflow: hidden;">
                <!-- 装饰性背景元素 -->
                <div style="position: absolute; top: -50%; right: -20%; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255, 107, 53, 0.1) 0%, transparent 70%); border-radius: 50%;"></div>
                <div style="position: absolute; bottom: -30%; left: -10%; width: 150px; height: 150px; background: radial-gradient(circle, rgba(255, 107, 53, 0.05) 0%, transparent 70%); border-radius: 50%;"></div>
                
                <div style="position: relative; z-index: 2;">
                <form id="order_review" method="post">
                    <?php do_action( 'woocommerce_pay_order_before_payment' ); ?>

                    <!-- 现代化支付方式区域 -->
                    <div class="payment-section-modern" style="text-align: center;">
                        <div style="margin-bottom: 2.5rem;">
                            <h2 style="color: #ffffff; font-size: 1.8rem; font-weight: 600; margin-bottom: 0.5rem; letter-spacing: -0.5px;">Complete Payment</h2>
                            <p style="color: #888; font-size: 1rem; margin: 0;">Enter your payment details below</p>
                        </div>
                        
                        <div id="payment" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; padding: 2.5rem; margin-bottom: 2rem;">
                            <?php if ( $order->needs_payment() ) : ?>
                                <ul class="wc_payment_methods payment_methods methods" style="list-style: none; padding: 0; margin: 0;">
                                    <?php
                                    if ( ! empty( $available_gateways ) ) {
                                        foreach ( $available_gateways as $gateway ) {
                                            wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
                                        }
                                    } else {
                                        echo '<li style="color: #ffffff; padding: 2rem; text-align: center; background: rgba(239, 68, 68, 0.1); border-radius: 12px; border: 1px solid rgba(239, 68, 68, 0.2);">';
                                        echo '<div style="font-size: 1.1rem; margin-bottom: 0.5rem;">No Payment Methods Available</div>';
                                        echo '<div style="color: #ccc; font-size: 0.9rem;">Please contact support for assistance</div>';
                                        echo '</li>';
                                    }
                                    ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 现代化提交区域 -->
                    <div class="form-row" style="margin-top: 2rem;">
                        <input type="hidden" name="woocommerce_pay" value="1" />

                        <?php wc_get_template( 'checkout/terms.php' ); ?>

                        <?php do_action( 'woocommerce_pay_order_before_submit' ); ?>

                        <div style="text-align: center; margin-top: 2rem;">
                            <?php echo apply_filters( 'woocommerce_pay_order_button_html', 
                                '<button type="submit" class="button alt cutler-pay-button-modern" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '" style="
                                    background: linear-gradient(135deg, #ff6b35 0%, #e55a2b 100%); 
                                    color: #ffffff; 
                                    border: none; 
                                    border-radius: 16px; 
                                    padding: 1.5rem 4rem; 
                                    font-weight: 700; 
                                    font-size: 1.2rem;
                                    text-transform: uppercase; 
                                    letter-spacing: 2px; 
                                    cursor: pointer; 
                                    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
                                    width: 100%; 
                                    max-width: 450px; 
                                    box-shadow: 0 8px 32px rgba(255, 107, 53, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1) inset; 
                                    position: relative; 
                                    overflow: hidden;
                                    backdrop-filter: blur(10px);
                                    transform: translateZ(0);
                                ">' . esc_html( $order_button_text ) . '</button>' 
                            ); ?>
                        </div>

                        <!-- 安全提示 -->
                        <div style="display: flex; align-items: center; justify-content: center; margin-top: 1.5rem; color: #888; font-size: 0.9rem;">
                            <svg width="16" height="16" style="margin-right: 8px; opacity: 0.7;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2z" clip-rule="evenodd"/>
                            </svg>
                            Your payment information is protected by SSL encryption
                        </div>

                        <?php do_action( 'woocommerce_pay_order_after_submit' ); ?>

                        <?php wp_nonce_field( 'woocommerce-pay', 'woocommerce-pay-nonce' ); ?>
                    </div>
                </form>
                </div> <!-- 关闭 position: relative; z-index: 2 div -->
            </div> <!-- 关闭 payment-section-modern div -->
        </div>
    </div>
</div>

<style>
/* 响应式设计 */
@media (max-width: 768px) {
    .order-info-compact {
        flex-direction: column !important;
        gap: 1rem !important;
        text-align: center !important;
    }
    
    .order-info-compact > div {
        flex-direction: row !important;
        justify-content: space-between !important;
        gap: 1rem !important;
    }
    
    .payment-section-modern {
        padding: 2rem 1.5rem !important;
    }
    
    .cutler-pay-button-modern {
        padding: 1.25rem 2rem !important;
        font-size: 1.1rem !important;
        letter-spacing: 1px !important;
    }
}

@media (min-width: 1024px) {
    .cutler-order-pay-container {
        padding: 0 2rem !important;
    }
}

/* 现代化支付按钮效果 */
.cutler-pay-button-modern:hover {
    transform: translateY(-3px) scale(1.02) !important;
    box-shadow: 0 12px 40px rgba(255, 107, 53, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.2) inset !important;
    background: linear-gradient(135deg, #ff7645 0%, #f5501b 100%) !important;
}

.cutler-pay-button-modern:active {
    transform: translateY(-1px) scale(1.01) !important;
    box-shadow: 0 8px 32px rgba(255, 107, 53, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1) inset !important;
}

.cutler-pay-button-modern:focus {
    outline: none !important;
    box-shadow: 0 8px 32px rgba(255, 107, 53, 0.3), 0 0 0 3px rgba(255, 107, 53, 0.2) !important;
}

/* 现代化支付方式样式 */
.payment_methods li {
    background: rgba(255, 255, 255, 0.03) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 12px !important;
    margin-bottom: 1rem !important;
    padding: 1.5rem !important;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    position: relative !important;
    overflow: hidden !important;
}

.payment_methods li::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 107, 53, 0.05) 0%, transparent 50%);
    opacity: 0;
    transition: opacity 0.4s ease;
    pointer-events: none;
}

.payment_methods li:hover {
    background: rgba(255, 255, 255, 0.08) !important;
    border-color: rgba(255, 107, 53, 0.4) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2) !important;
}

.payment_methods li:hover::before {
    opacity: 1;
}

.payment_methods li label {
    color: #ffffff !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    font-size: 1.1rem !important;
    position: relative !important;
    z-index: 1 !important;
}

.payment_methods li input[type="radio"] {
    accent-color: #ff6b35 !important;
    transform: scale(1.2) !important;
    margin-right: 12px !important;
}

.payment_methods li.wc_payment_method input[type="radio"]:checked + label {
    color: #ff6b35 !important;
}

/* Cajipay iframe 容器样式优化 */
.payment_method_cajipay {
    position: relative !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* paypal-diy-button 容器样式 */
.paypal-diy-button {
    position: relative !important;
    background: rgba(255, 255, 255, 0.02) !important;
    border-radius: 20px !important;
    padding: 0 !important;
    margin: 1.5rem 0 !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
    backdrop-filter: blur(15px) !important;
    overflow: hidden !important;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.paypal-diy-button:hover {
    border-color: rgba(255, 107, 53, 0.3) !important;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 107, 53, 0.2) !important;
    transform: translateY(-2px) !important;
}

/* iframe 样式 */
.payment_method_cajipay iframe,
.paypal-diy-button iframe,
iframe[name="qstripe1frame"] {
    border-radius: 20px !important;
    border: none !important;
    background: transparent !important;
    width: 100% !important;
    min-height: 400px !important;
    max-height: 500px !important;
    overflow: hidden !important;
    transition: all 0.3s ease !important;
}

/* iframe 容器装饰效果 */
.paypal-diy-button::before {
    content: '';
    position: absolute;
    top: -3px;
    left: -3px;
    right: -3px;
    bottom: -3px;
    background: linear-gradient(135deg, 
        rgba(255, 107, 53, 0.3) 0%, 
        transparent 25%, 
        transparent 75%, 
        rgba(255, 107, 53, 0.2) 100%);
    border-radius: 23px;
    z-index: -1;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.paypal-diy-button:hover::before {
    opacity: 1;
}

/* 在iframe加载时的占位样式 */
.paypal-diy-button::after {
    content: 'Loading secure payment form...';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #888;
    font-size: 1rem;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 0;
}

.paypal-diy-button:not(:has(iframe))::after,
.paypal-diy-button:has(iframe[src=""])::after {
    opacity: 1;
}

/* 支付表单字段样式 - 用于其他非iframe支付方式 */
.payment_method_stripe .form-row input[type="text"],
.payment_method_stripe .form-row input[type="email"],
.payment_method_stripe .form-row select,
.wc-credit-card-form-card-number input,
.wc-credit-card-form-card-expiry input,
.wc-credit-card-form-card-cvc input {
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 12px !important;
    color: #ffffff !important;
    padding: 16px 20px !important;
    font-size: 1rem !important;
    transition: all 0.3s ease !important;
    backdrop-filter: blur(10px) !important;
}

.payment_method_stripe .form-row input:focus,
.wc-credit-card-form input:focus {
    border-color: #ff6b35 !important;
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.2) !important;
    background: rgba(255, 255, 255, 0.08) !important;
}

/* 表单标签和描述文字样式 */
.payment_method_cajipay .form-row label,
.wc-credit-card-form label {
    color: #ffffff !important;
    font-weight: 600 !important;
    margin-bottom: 8px !important;
    font-size: 0.95rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
}

.payment_method_cajipay .description,
.payment_box p {
    color: #aaa !important;
    font-size: 0.9rem !important;
    line-height: 1.5 !important;
    margin-top: 12px !important;
}

/* 加载状态优化 */
.processing .cutler-pay-button-modern {
    background: rgba(255, 107, 53, 0.6) !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
}

.processing .cutler-pay-button-modern::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 24px;
    height: 24px;
    margin: -12px 0 0 -12px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* 通知样式优化 */
.woocommerce-error,
.woocommerce-message,
.woocommerce-info {
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 12px !important;
    color: #ffffff !important;
    padding: 16px 20px !important;
    margin-bottom: 20px !important;
    backdrop-filter: blur(10px) !important;
}

.woocommerce-error {
    border-left: 4px solid #ef4444 !important;
    background: rgba(239, 68, 68, 0.1) !important;
}

.woocommerce-message {
    border-left: 4px solid #10b981 !important;
    background: rgba(16, 185, 129, 0.1) !important;
}

/* 辅助功能和视觉增强 */
.cutler-order-pay-wrapper *:focus {
    outline: 2px solid rgba(255, 107, 53, 0.5) !important;
    outline-offset: 2px !important;
}

.cutler-order-pay-wrapper ::selection {
    background: rgba(255, 107, 53, 0.3) !important;
    color: #ffffff !important;
}
</style>
