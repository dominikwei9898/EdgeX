<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout grid grid-cols-1 lg:grid-cols-2 gap-8" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

	<!-- 左侧：客户信息区域 -->
	<div class="checkout-customer-details">
		<?php if ( $checkout->get_checkout_fields() ) : ?>

			<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

			<div id="customer_details">
				<!-- Billing Details -->
				<div class="checkout-billing mb-6">
					<?php do_action( 'woocommerce_checkout_billing' ); ?>
				</div>

				<!-- Shipping Details -->
				<div class="checkout-shipping mb-6">
					<?php do_action( 'woocommerce_checkout_shipping' ); ?>
				</div>
			</div>

			<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

		<?php endif; ?>
	</div>
	
	<!-- 右侧：订单摘要区域 -->
	<div class="checkout-review-order-wrap">
        <?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
        
        <div class="wc-block-components-checkout-order-summary__title">
            <h3 id="order_review_heading" class="text-xl font-bold mb-4"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>
        </div>
        
        <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

        <div class="wc-block-components-order-summary is-large">
            <div id="order_review" class="woocommerce-checkout-review-order p-6 bg-gray-50 rounded-lg">
                <?php do_action( 'woocommerce_checkout_order_review' ); ?>
            </div>
        </div>

        <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
    </div>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

