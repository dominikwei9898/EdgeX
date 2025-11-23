<?php
/**
 * View order
 *
 * Shows the details of a particular order on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/view-order.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

$notes = $order->get_customer_order_notes();
?>
    
    <!-- 订单状态和基本信息 -->
    <div class="order-status-header" style="
        background: rgba(255, 255, 255, 0.02); 
        border: 1px solid rgba(255, 255, 255, 0.08); 
        border-radius: 16px; 
        padding: 1.5rem; 
        margin-bottom: 2rem; 
        backdrop-filter: blur(5px);
    ">
        <div class="order-header-content" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
            <div class="order-info-left" style="flex: 1; min-width: 200px;">
                <p style="color: #888; font-size: 0.8rem; margin: 0 0 0.25rem 0; text-transform: uppercase; letter-spacing: 0.5px;">
                    <?php echo esc_html__( 'Order #', 'woocommerce' ) . esc_html( $order->get_order_number() ); ?>
                </p>
                <h2 style="color: #ffffff; font-size: 1.3rem; font-weight: 600; margin: 0; line-height: 1.3;">
                    <?php
                    $status_name = wc_get_order_status_name( $order->get_status() );
                    /* translators: 1: order status */
                    printf( esc_html__( 'Order status: %s', 'woocommerce' ), '<span style="color: #ff6b35;">' . esc_html( $status_name ) . '</span>' );
                    ?>
                </h2>
            </div>
            <div class="order-info-right" style="text-align: right; flex-shrink: 0; min-width: 150px;">
                <p style="color: #888; font-size: 0.8rem; margin: 0 0 0.25rem 0; text-transform: uppercase; letter-spacing: 0.5px;"><?php esc_html_e( 'Date placed', 'woocommerce' ); ?></p>
                <p style="color: #ffffff; font-size: 0.95rem; font-weight: 600; margin: 0; line-height: 1.3;">
                    <?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- 订单详情 -->
    <div class="order-details-section" style="margin-bottom: 2rem;">
        <h3 style="color: #ffffff; font-size: 1.3rem; font-weight: 600; margin-bottom: 1.5rem;">
            <?php esc_html_e( 'Order details', 'woocommerce' ); ?>
        </h3>

        <?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

        <div class="order-table-wrapper" style="
            background: rgba(255, 255, 255, 0.02); 
            border: 1px solid rgba(255, 255, 255, 0.08); 
            border-radius: 16px; 
            overflow-x: auto; 
            overflow-y: visible;
            margin-bottom: 1.5rem;
        ">
                <table class="woocommerce-table woocommerce-table--order-details shop_table order_details" style="
                width: 100%; 
                border-collapse: collapse;
                table-layout: fixed;
                min-width: 500px;
            ">
                <thead style="background: rgba(255, 255, 255, 0.05);">
                    <tr>
                        <th class="woocommerce-table__product-name product-name" style="
                            color: #ffffff; 
                            font-weight: 600; 
                            text-transform: uppercase; 
                            letter-spacing: 0.5px; 
                            font-size: 0.85rem; 
                            padding: 16px; 
                            border: none;
                            width: 75%;
                        "><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
                        <th class="woocommerce-table__product-table product-total" style="
                            color: #ffffff; 
                            font-weight: 600; 
                            text-transform: uppercase; 
                            letter-spacing: 0.5px; 
                            font-size: 0.85rem; 
                            padding: 16px; 
                            border: none; 
                            text-align: right;
                            width: 25%;
                        "><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    do_action( 'woocommerce_order_details_before_order_table_items', $order );

                    foreach ( $order->get_items() as $item_id => $item ) {
                        $product = $item->get_product();
                        ?>
                        <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order ) ); ?>" style="border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
                            <td class="woocommerce-table__product-name product-name" style="
                                color: #ffffff; 
                                padding: 16px; 
                                border: none;
                                word-wrap: break-word;
                                overflow-wrap: break-word;
                                width: 75%;
                                max-width: 0;
                            ">
                                <?php
                                $is_visible        = $product && $product->is_visible();
                                $product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );

                                echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s" style="color: #ff6b35; text-decoration: none;">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name(), $item, $is_visible ) );

                                $qty          = $item->get_quantity();
                                $refunded_qty = $order->get_qty_refunded_for_item( $item_id );

                                if ( $refunded_qty ) {
                                    $qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
                                } else {
                                    $qty_display = esc_html( $qty );
                                }

                                echo ' <strong class="product-quantity" style="color: #888; font-size: 0.9rem;">×&nbsp;' . apply_filters( 'woocommerce_order_item_quantity_html', $qty_display, $item ) . '</strong>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

                                do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

                                wc_display_item_meta( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

                                do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
                                ?>
                            </td>
                            <td class="woocommerce-table__product-total product-total" style="
                                color: #ff6b35; 
                                font-weight: 700; 
                                font-size: 1rem; 
                                padding: 16px; 
                                border: none; 
                                text-align: right;
                                white-space: nowrap;
                                width: 25%;
                                vertical-align: top;
                            ">
                                <?php echo $order->get_formatted_line_subtotal( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </td>
                        </tr>
                        <?php
                    }

                    do_action( 'woocommerce_order_details_after_order_table_items', $order );
                    ?>
                </tbody>
                <tfoot style="background: rgba(255, 255, 255, 0.03);">
                    <?php
                    foreach ( $order->get_order_item_totals() as $key => $total ) {
                        ?>
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
                            <th scope="row" style="
                                color: #ffffff; 
                                font-weight: 600; 
                                padding: 12px 16px; 
                                border: none; 
                                text-transform: uppercase; 
                                letter-spacing: 0.5px; 
                                font-size: 0.85rem;
                                width: 75%;
                            "><?php echo esc_html( $total['label'] ); ?></th>
                            <td style="
                                color: <?php echo $key === 'order_total' ? '#ff6b35' : '#ffffff'; ?>; 
                                font-weight: <?php echo $key === 'order_total' ? '700' : '500'; ?>; 
                                font-size: <?php echo $key === 'order_total' ? '1.2rem' : '1rem'; ?>; 
                                padding: 12px 16px; 
                                border: none; 
                                text-align: right;
                                white-space: nowrap;
                                width: 25%;
                            "><?php echo wp_kses_post( $total['value'] ); ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <?php if ( $order->get_customer_note() ) : ?>
                        <tr>
                            <th style="
                                color: #ffffff; 
                                font-weight: 600; 
                                padding: 12px 16px; 
                                border: none; 
                                text-transform: uppercase; 
                                letter-spacing: 0.5px; 
                                font-size: 0.85rem;
                                width: 75%;
                            "><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
                            <td style="
                                color: #888; 
                                padding: 12px 16px; 
                                border: none; 
                                text-align: right; 
                                font-style: italic;
                                word-wrap: break-word;
                                width: 25%;
                            "><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
                        </tr>
                    <?php endif; ?>
                </tfoot>
            </table>
        </div>

        <?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
    </div>

    <!-- 订单操作 -->
    <?php if ( $order->needs_payment() ) : ?>
    <div class="order-actions-section" style="margin-bottom: 2rem;">
        <h3 style="color: #ffffff; font-size: 1.3rem; font-weight: 600; margin-bottom: 1rem;">
            <?php esc_html_e( 'Actions', 'woocommerce' ); ?>
        </h3>
        
        <div style="
            background: rgba(255, 255, 255, 0.02); 
            border: 1px solid rgba(255, 255, 255, 0.08); 
            border-radius: 16px; 
            padding: 1.5rem; 
            text-align: center;
        ">
            <div style="margin-bottom: 1.5rem;">
                <p style="color: #888; margin: 0;">
                    <?php esc_html_e( 'Payment method:', 'woocommerce' ); ?>
                    <span style="color: #ffffff; font-weight: 600;">Credit Card</span>
                </p>
            </div>
            
            <div class="order-pay-buttons" style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay" style="
                    background: linear-gradient(135deg, #ff6b35 0%, #e55a2b 100%); 
                    color: #ffffff; 
                    border: none; 
                    border-radius: 12px; 
                    padding: 12px 24px; 
                    font-weight: 600; 
                    text-transform: uppercase; 
                    letter-spacing: 1px; 
                    cursor: pointer; 
                    transition: all 0.3s ease; 
                    text-decoration: none; 
                    display: inline-block;
                    font-size: 0.9rem;
                ">
                    <?php esc_html_e( 'Pay', 'woocommerce' ); ?>
                </a>
                
                <?php if ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" style="
                    background: transparent; 
                    color: #888; 
                    border: 2px solid #444; 
                    border-radius: 12px; 
                    padding: 10px 22px; 
                    font-weight: 600; 
                    text-transform: uppercase; 
                    letter-spacing: 1px; 
                    cursor: pointer; 
                    transition: all 0.3s ease; 
                    text-decoration: none; 
                    display: inline-block;
                    font-size: 0.9rem;
                ">
                    <?php esc_html_e( 'My orders', 'woocommerce' ); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 地址信息 -->
    <div class="order-addresses-section">
        <h3 style="color: #ffffff; font-size: 1.3rem; font-weight: 600; margin-bottom: 1rem;">
            <?php esc_html_e( 'Billing address', 'woocommerce' ); ?>
        </h3>
        
        <div style="
            background: rgba(255, 255, 255, 0.02); 
            border: 1px solid rgba(255, 255, 255, 0.08); 
            border-radius: 16px; 
            padding: 1.5rem;
        ">
            <div style="color: #888; line-height: 1.6;">
                <?php echo wp_kses_post( $order->get_formatted_billing_address( esc_html__( 'N/A', 'woocommerce' ) ) ); ?>
                
                <?php if ( $order->get_billing_phone() ) : ?>
                    <p style="margin-top: 1rem; color: #ffffff;">
                        <strong><?php esc_html_e( 'Phone:', 'woocommerce' ); ?></strong> <?php echo esc_html( $order->get_billing_phone() ); ?>
                    </p>
                <?php endif; ?>
                
                <?php if ( $order->get_billing_email() ) : ?>
                    <p style="margin-top: 0.5rem; color: #ffffff;">
                        <strong><?php esc_html_e( 'Email:', 'woocommerce' ); ?></strong> <?php echo esc_html( $order->get_billing_email() ); ?>
                    </p>
                <?php endif; ?>
            </div>

            <?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && $order->get_formatted_shipping_address() ) : ?>
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                    <h4 style="color: #ffffff; font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem;">
                        <?php esc_html_e( 'Shipping address', 'woocommerce' ); ?>
                    </h4>
                    <div style="color: #888; line-height: 1.6;">
                        <?php echo wp_kses_post( $order->get_formatted_shipping_address( esc_html__( 'N/A', 'woocommerce' ) ) ); ?>
                        
                        <?php if ( $order->get_shipping_phone() ) : ?>
                            <p style="margin-top: 1rem; color: #ffffff;">
                                <strong><?php esc_html_e( 'Phone:', 'woocommerce' ); ?></strong> <?php echo esc_html( $order->get_shipping_phone() ); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* 订单页面响应式优化 */
@media (max-width: 767px) {
    .order-status-header {
        padding: 1.25rem !important;
    }
    
    .order-header-content {
        flex-direction: column !important;
        gap: 0.75rem !important;
    }
    
    .order-info-left {
        min-width: auto !important;
        flex: none !important;
        width: 100% !important;
        text-align: center !important;
    }
    
    .order-info-right {
        min-width: auto !important;
        flex: none !important;
        width: 100% !important;
        text-align: center !important;
    }
    
    .order-info-left h2 {
        font-size: 1.2rem !important;
    }
    
    .woocommerce-table thead {
        display: none !important;
    }
    
    .woocommerce-table tbody tr {
        display: block !important;
        background: rgba(255, 255, 255, 0.02) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 12px !important;
        margin-bottom: 1rem !important;
        padding: 1rem !important;
    }
    
    .woocommerce-table tbody td {
        display: block !important;
        padding: 8px 0 !important;
        border: none !important;
        text-align: left !important;
    }
    
    .woocommerce-table tbody .product-name::before {
        content: "Product: " !important;
        font-weight: 600 !important;
        color: #888 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        font-size: 0.8rem !important;
    }
    
    .woocommerce-table tbody .product-total::before {
        content: "Total: " !important;
        font-weight: 600 !important;
        color: #888 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        font-size: 0.8rem !important;
    }
    
    .order-pay-buttons {
        flex-direction: column !important;
    }
}

/* 按钮悬停效果 */
.order-pay-buttons .button:hover {
    background: linear-gradient(135deg, #ff7645 0%, #f5501b 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 32px rgba(255, 107, 53, 0.3) !important;
}

.order-pay-buttons a:last-child:hover {
    border-color: #888 !important;
    color: #ffffff !important;
    transform: translateY(-2px) !important;
}

/* 表格优化 */
.order-table-wrapper {
    overflow-x: auto !important;
}

.woocommerce-table {
    min-width: 100% !important;
}
</style>

<?php
/**
 * Action hook fired after the order details.
 *
 * @since 4.4.0
 * @param WC_Order $order Order data.
 */
do_action( 'woocommerce_after_single_order_summary', $order, $notes );

if ( $notes ) : ?>
    <section class="order-notes-section" style="margin-top: 2rem;">
        <h3 style="color: #ffffff; font-size: 1.3rem; font-weight: 600; margin-bottom: 1rem;">
            <?php esc_html_e( 'Order updates', 'woocommerce' ); ?>
        </h3>
        <div style="
            background: rgba(255, 255, 255, 0.02); 
            border: 1px solid rgba(255, 255, 255, 0.08); 
            border-radius: 16px; 
            padding: 1.5rem;
        ">
            <ol class="woocommerce-OrderUpdates commentlist notes" style="list-style: none; margin: 0; padding: 0;">
                <?php foreach ( $notes as $note ) : ?>
                    <li class="woocommerce-OrderUpdate comment note" style="
                        padding: 1rem 0; 
                        border-bottom: 1px solid rgba(255, 255, 255, 0.08); 
                        margin-bottom: 1rem;
                    ">
                        <div class="woocommerce-OrderUpdate-inner comment_container">
                            <div class="woocommerce-OrderUpdate-text comment-text">
                                <p class="woocommerce-OrderUpdate-meta meta" style="
                                    color: #888; 
                                    font-size: 0.85rem; 
                                    margin-bottom: 0.5rem;
                                ">
                                    <time class="woocommerce-OrderUpdate-date" datetime="<?php echo esc_attr( $note->comment_date ); ?>">
                                        <?php echo esc_html( wc_format_datetime( new WC_DateTime( $note->comment_date ), get_option( 'date_format' ) ) ); ?>
                                    </time>
                                </p>
                                <div class="woocommerce-OrderUpdate-description description" style="color: #ffffff; line-height: 1.6;">
                                    <?php echo wpautop( wptexturize( wp_kses_post( $note->comment_content ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>
<?php endif;