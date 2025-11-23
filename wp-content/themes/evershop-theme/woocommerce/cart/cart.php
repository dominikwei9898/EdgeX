<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.1.0
 */

defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------
// 1. Order Cancellation / Failure Notice
// ---------------------------------------------------------
if ( isset( $_GET['cancel_order'] ) && $_GET['cancel_order'] === 'true' ) {
    // Ensure we don't stack duplicate notices if page is refreshed
    if ( ! wc_has_notice( 'Payment for order', 'error' ) ) {
        $order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;
        
        $notice_message = sprintf( 
            __( 'Payment for order #%s was cancelled. Your items have been restored to your cart.', 'woocommerce' ), 
            $order_id 
        );
        
        wc_add_notice( $notice_message, 'error' );
    }
}

do_action( 'woocommerce_before_cart' ); ?>

<div class="shopify-cart-wrapper">
    
    <div class="cart-header">
        <h1 class="cart-title"><?php esc_html_e( 'Your cart', 'woocommerce' ); ?></h1>
        <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="continue-shopping-link">
            <?php esc_html_e( 'Continue shopping', 'woocommerce' ); ?>
        </a>
    </div>

    <!-- FORM START: Wraps entire grid layout -->
    <form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
        
        <div class="cart-grid-layout">
            
            <!-- LEFT COLUMN: Cart Items -->
            <div class="cart-items-column">
                <?php do_action( 'woocommerce_before_cart_table' ); ?>

                <div class="cart-table-modern">
                    <div class="cart-row-header">
                        <div class="col-product"><?php esc_html_e( 'PRODUCT', 'woocommerce' ); ?></div>
                        <div class="col-qty"><?php esc_html_e( 'QUANTITY', 'woocommerce' ); ?></div>
                        <div class="col-total"><?php esc_html_e( 'TOTAL', 'woocommerce' ); ?></div>
                    </div>

                    <?php do_action( 'woocommerce_before_cart_contents' ); ?>

                    <?php
                    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                        $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
                        $product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );

                        if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                            $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                            ?>
                            <div class="cart-item-row <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
                                
                                <!-- Product Info: Image + Details -->
                                <div class="item-info-cell">
                                    <!-- Image -->
                                    <div class="item-image">
                                        <?php
                                        $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                                        if ( ! $product_permalink ) {
                                            echo $thumbnail;
                                        } else {
                                            printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
                                        }
                                        ?>
                                    </div>
                                    
                                    <!-- Details -->
                                    <div class="item-details">
                                        <div class="item-name">
                                            <?php
                                            if ( ! $product_permalink ) {
                                                echo wp_kses_post( $product_name . '&nbsp;' );
                                            } else {
                                                echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
                                            }
                                            ?>
                                        </div>
                                        
                                        <div class="item-price-unit">
                                            <?php
                                                echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                                            ?>
                                        </div>

                                        <?php
                                        // Meta data
                                        echo wc_get_formatted_cart_item_data( $cart_item ); 

                                        // Backorder
                                        if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
                                            echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
                                        }
                                        ?>
                                    </div>
                                </div>

                                <!-- Quantity -->
                                <div class="item-qty-cell">
                                    <?php
                                    if ( $_product->is_sold_individually() ) {
                                        $min_quantity = 1;
                                        $max_quantity = 1;
                                    } else {
                                        $min_quantity = 0;
                                        $max_quantity = $_product->get_max_purchase_quantity();
                                    }

                                    $product_quantity = woocommerce_quantity_input(
                                        array(
                                            'input_name'   => "cart[{$cart_item_key}][qty]",
                                            'input_value'  => $cart_item['quantity'],
                                            'max_value'    => $max_quantity,
                                            'min_value'    => $min_quantity,
                                            'product_name' => $product_name,
                                        ),
                                        $_product,
                                        false
                                    );

                                    echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
                                    ?>
                                </div>

                                <!-- Total & Remove -->
                                <div class="item-total-cell">
                                    <div class="item-subtotal">
                                        <?php
                                            echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
                                        ?>
                                    </div>
                                    <div class="item-remove">
                                        <?php
                                            echo apply_filters( 
                                                'woocommerce_cart_item_remove_link',
                                                sprintf(
                                                    '<a href="%s" class="remove-link" aria-label="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
                                                    esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                                    esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
                                                    esc_attr( $product_id ),
                                                    esc_attr( $_product->get_sku() ),
                                                    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>'
                                                ),
                                                $cart_item_key
                                            );
                                        ?>
                                    </div>
                                </div>

                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
                
                <!-- Actions Bar (Coupon) -->
                <div class="cart-actions-bar">
                    <?php if ( wc_coupons_enabled() ) { ?>
                        <div class="shopify-coupon">
                            <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" /> 
                            <button type="submit" class="button coupon-btn" name="apply_coupon" value="<?php esc_attr_e( 'Apply', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply', 'woocommerce' ); ?></button>
                        </div>
                    <?php } ?>

                    <!-- Hidden Update Button -->
                    <button type="submit" class="button update-cart-btn" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>" style="display: none;"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>
                    
                    <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
                </div>

                <?php do_action( 'woocommerce_after_cart_contents' ); ?>
                <?php do_action( 'woocommerce_after_cart_table' ); ?>
            </div>

            <!-- RIGHT COLUMN: Summary -->
            <div class="cart-summary-column">
                <div class="summary-sticky-wrapper">
                    <?php do_action( 'woocommerce_cart_collaterals' ); ?>
                </div>
            </div>

        </div>
    </form>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>

<script>
jQuery(function($) {
    // Auto-update cart when quantity changes
    $(document.body).on('change', 'input.qty', function() {
        $("[name='update_cart']").trigger("click");
    });
});
</script>

<style>
/* -------------------------------------------------------
   SHOPIFY-STYLE CART CSS
   ------------------------------------------------------- */

.shopify-cart-wrapper {
    max-width: 1400px;
    margin: 0 auto;
    padding: 3rem 2rem;
    color: #fff;
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 2rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding-bottom: 1.5rem;
}

.cart-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #fff;
    margin: 0;
}

.continue-shopping-link {
    color: #ff6b35;
    text-decoration: none;
    font-size: 1rem;
    border-bottom: 1px solid transparent;
    transition: all 0.3s;
}
.continue-shopping-link:hover {
    border-bottom-color: #ff6b35;
}

.cart-grid-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 4rem;
    align-items: start;
}

/* LEFT COLUMN ADJUSTMENTS */
.cart-items-column {
    /* Ensure left column has space */
    padding-right: 0; 
}

/* HEADER ROW */
.cart-row-header {
    display: grid;
    grid-template-columns: 5fr 2fr 2fr;
    padding: 0 1rem 1rem 1rem; /* Indented padding to match content */
    border-bottom: 1px solid rgba(255,255,255,0.1);
    color: #888;
    font-size: 0.8rem;
    letter-spacing: 1px;
    text-transform: uppercase;
}

/* ITEM ROW */
.cart-item-row {
    display: grid;
    grid-template-columns: 5fr 2fr 2fr;
    padding: 2rem 1rem; /* Add horizontal padding to align with header/coupon */
    border-bottom: 1px solid rgba(255,255,255,0.1);
    align-items: center;
}

/* PRODUCT IMAGE SIZE - Increased */
.item-image img {
    height: auto !important;
    border-radius: 8px;
    background: rgba(255,255,255,0.05);
    display: block;
}

.item-info-cell {
    display: flex;
    gap: 2rem; 
    align-items: center;
}

.item-name a {
    color: #fff;
    font-size: 1.2rem;
    font-weight: 600;
    text-decoration: none;
    display: block;
    margin-bottom: 0.5rem;
}
.item-price-unit {
    color: #888;
    font-size: 1rem;
}

/* QTY INPUT */
.item-qty-cell input {
    background: transparent !important;
    border: 1px solid rgba(255,255,255,0.2) !important;
    color: #fff !important;
    border-radius: 4px !important;
    padding: 0.8rem !important;
    width: 70px !important;
    text-align: center;
    font-size: 1rem;
}

/* TOTAL CELL */
.item-total-cell {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.item-subtotal {
    color: #fff;
    font-size: 1.2rem;
    font-weight: 600;
}

.remove-link {
    color: #666;
    transition: color 0.3s;
    cursor: pointer;
}
.remove-link:hover { color: #ff6b35; }

/* COUPON SECTION */
.cart-actions-bar {
    margin-top: 2rem;
    padding: 0 1rem; /* Match row padding */
}

.shopify-coupon {
    display: flex;
    gap: 10px;
}
.shopify-coupon input {
    background: transparent !important;
    border: 1px solid rgba(255,255,255,0.2) !important;
    color: #fff !important;
    border-radius: 4px !important;
    padding: 1rem 1.5rem !important;
    width: 250px !important;
    font-size: 1rem;
}
.shopify-coupon button {
    background: #333 !important;
    color: #fff !important;
    border: none !important;
    border-radius: 4px !important;
    padding: 0 2rem !important;
    font-weight: 600;
    cursor: pointer;
}

/* SUMMARY COLUMN (Right) */
.summary-sticky-wrapper {
    position: sticky;
    top: 100px;
    background: rgba(255,255,255,0.05);
    padding: 2.5rem;
    border-radius: 12px;
}

.cart_totals h2 { display: none; }
.cart_totals table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; }
.cart_totals th, .cart_totals td {
    padding: 1rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    text-align: right;
    color: #ccc;
}
.cart_totals th { text-align: left; color: #888; font-weight: 500; }
.cart_totals .order-total th, .cart_totals .order-total td {
    border-bottom: none; color: #fff; font-size: 1.4rem; font-weight: 700; padding-top: 1.5rem;
}
.cart_totals .order-total td strong { color: #ff6b35; }

.wc-proceed-to-checkout a.checkout-button {
    display: block; width: 100%;
    background: linear-gradient(135deg, #ff6b35 0%, #e55a2b 100%) !important;
    color: #fff !important; padding: 1.4rem !important;
    border-radius: 8px !important; text-align: center;
    font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
    font-size: 1.1rem; text-decoration: none; margin-top: 1rem;
}

/* Error Notice Styling */
.woocommerce-error {
    background: rgba(220, 53, 69, 0.1) !important;
    color: #ff6b6b !important;
    border-left: 4px solid #ff6b6b !important;
    padding: 1rem 1.5rem !important;
    margin-bottom: 2rem !important;
    border-radius: 4px;
    list-style: none outside !important;
}
.woocommerce-error li {
    margin: 0 !important; 
    padding: 0 !important;
    display: flex;
    align-items: center;
    gap: 10px;
}
.woocommerce-error:before, .woocommerce-info:before, .woocommerce-message:before {
    display: none !important;
}

/* MOBILE RESPONSIVE */
@media (max-width: 992px) {
    .cart-grid-layout { grid-template-columns: 1fr; gap: 3rem; }
    .cart-row-header { display: none; }
    .cart-item-row { display: grid; grid-template-columns: 140px 1fr; grid-template-areas: "img details" "img qty" "img total"; gap: 1.5rem; padding: 1.5rem 0; }
    .item-image { grid-area: img; }
    .item-details { grid-area: details; }
    .item-qty-cell { grid-area: qty; margin-left: 0; }
    .item-total-cell { grid-area: total; }
    .shopify-coupon input { width: 100% !important; flex: 1; }
}
</style>
