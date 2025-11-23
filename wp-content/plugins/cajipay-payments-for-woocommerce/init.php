<?php
/*
 * Plugin Name: cajipay Payments for WooCommerce
 * Plugin URI: https://www.cajipay.com
 * Description:超集支付 通过提供各种支付渠道，使您的在线和离线业务更加轻松，提供了安全便捷的在线支付解决方案。多渠道，多账号，多平台，一键导入，快速对接。
 * Version: 1.0.1
 * Author: cajipay
 * Author URI:http://www.cajipay.com/
 * Text Domain: cajipay Payments for WooCommerce
 * WC tested up to: 9.9.9
 */
if (! defined ( 'ABSPATH' ))
	exit (); // Exit if accessed directly

if (! defined ( 'CAJI_PAY' )) {define ( 'CAJI_PAY', 'CAJI_PAY' );} else {return;}
define('CAJI_PAY_GATEWAY_VERSION','1.0.0');
define('CAJI_PAY_GATEWAY_ID','cajipaywcpaymentgateway' /*'xh-wechat'*/);
define('CAJI_PAY_GATEWAY_DIR',rtrim(plugin_dir_path(__FILE__),'/'));
define('CAJI_PAY_GATEWAY_URL',rtrim(plugin_dir_url(__FILE__),'/'));
load_plugin_textdomain( 'cajipay', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/'  );
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'cajipay_wc_payment_gateway_plugin_edit_link' );
add_action( 'init', 'cajipay_wc_payment_gateway_init' );

if(!function_exists('cajipay_wc_payment_gateway_init')){
    function cajipay_wc_payment_gateway_init() {
        if( !class_exists('WC_Payment_Gateway') )  return;
        require_once CAJI_PAY_GATEWAY_DIR .'/class-cajipay-wc-payment-gateway.php';
        $api = new CajipayWCPaymentGateway();

        $api->check_cajipay_response();

        add_filter('woocommerce_payment_gateways',array($api,'woocommerce_cajipay_add_gateway' ),10,1);
        add_action( 'woocommerce_api_cajipay_payment_get_order', array($api, "get_order_status" ) );
        add_action( 'woocommerce_receipt_'.$api->id, array($api, 'receipt_page'));
        add_action( 'woocommerce_update_options_payment_gateways_' . $api->id, array ($api,'process_admin_options') ); // WC >= 2.0
        add_action( 'woocommerce_update_options_payment_gateways', array ($api,'process_admin_options') );
    }
}

function cajipay_wc_payment_gateway_plugin_edit_link( $links ){
    return array_merge(
        array(
            'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section='.CAJI_PAY_GATEWAY_ID) . '">'.__( 'Settings', 'cajipay' ).'</a>'
        ),
        $links
    );
}
?>
