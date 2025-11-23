<?php
/**
 * EverShop Checkout API
 * 
 * 提供结账相关的 REST API 端点
 */

if (!defined('ABSPATH')) {
    exit;
}

class EverShop_Checkout {
    
    private static $namespace = 'evershop/v1';
    
    public static function init() {
        $instance = new self();
        $instance->setup_hooks();
    }
    
    private function setup_hooks() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * 注册 REST API 路由
     */
    public function register_routes() {
        // 获取支付方式
        register_rest_route(self::$namespace, '/checkout/payment-methods', [
            'methods' => 'GET',
            'callback' => [$this, 'get_payment_methods'],
            'permission_callback' => '__return_true'
        ]);
        
        // 获取配送方式
        register_rest_route(self::$namespace, '/checkout/shipping-methods', [
            'methods' => 'POST',
            'callback' => [$this, 'get_shipping_methods'],
            'permission_callback' => '__return_true'
        ]);
        
        // 计算运费
        register_rest_route(self::$namespace, '/checkout/calculate-shipping', [
            'methods' => 'POST',
            'callback' => [$this, 'calculate_shipping'],
            'permission_callback' => '__return_true'
        ]);
        
        // 创建订单
        register_rest_route(self::$namespace, '/checkout/create-order', [
            'methods' => 'POST',
            'callback' => [$this, 'create_order'],
            'permission_callback' => [$this, 'check_user_permission']
        ]);
        
        // 获取订单详情
        register_rest_route(self::$namespace, '/orders/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_order'],
            'permission_callback' => [$this, 'check_user_permission']
        ]);
        
        // 获取用户订单列表
        register_rest_route(self::$namespace, '/orders', [
            'methods' => 'GET',
            'callback' => [$this, 'get_orders'],
            'permission_callback' => [$this, 'check_user_permission']
        ]);
    }
    
    /**
     * 检查用户权限
     */
    public function check_user_permission($request) {
        return is_user_logged_in();
    }
    
    /**
     * 获取支付方式
     */
    public function get_payment_methods($request) {
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        $payment_methods = [];
        
        foreach ($available_gateways as $gateway) {
            $payment_methods[] = [
                'id' => $gateway->id,
                'title' => $gateway->get_title(),
                'description' => $gateway->get_description(),
                'icon' => $gateway->get_icon(),
                'enabled' => $gateway->is_available()
            ];
        }
        
        return rest_ensure_response($payment_methods);
    }
    
    /**
     * 获取配送方式
     */
    public function get_shipping_methods($request) {
        $country = $request->get_param('country');
        $state = $request->get_param('state');
        $postcode = $request->get_param('postcode');
        $city = $request->get_param('city');
        
        if (!$this->initialize_cart()) {
            return new WP_Error('cart_error', 'Could not initialize cart', ['status' => 500]);
        }
        
        // 设置配送地址
        if ($country) {
            WC()->customer->set_shipping_country($country);
        }
        if ($state) {
            WC()->customer->set_shipping_state($state);
        }
        if ($postcode) {
            WC()->customer->set_shipping_postcode($postcode);
        }
        if ($city) {
            WC()->customer->set_shipping_city($city);
        }
        
        // 计算配送
        WC()->cart->calculate_shipping();
        WC()->cart->calculate_totals();
        
        // 获取可用配送方式
        $packages = WC()->shipping()->get_packages();
        $shipping_methods = [];
        
        foreach ($packages as $package_key => $package) {
            if (isset($package['rates']) && !empty($package['rates'])) {
                foreach ($package['rates'] as $rate) {
                    $shipping_methods[] = [
                        'id' => $rate->get_id(),
                        'label' => $rate->get_label(),
                        'cost' => floatval($rate->get_cost()),
                        'tax' => floatval($rate->get_shipping_tax()),
                        'method_id' => $rate->get_method_id()
                    ];
                }
            }
        }
        
        return rest_ensure_response($shipping_methods);
    }
    
    /**
     * 计算运费
     */
    public function calculate_shipping($request) {
        return $this->get_shipping_methods($request);
    }
    
    /**
     * 创建订单
     */
    public function create_order($request) {
        if (!$this->initialize_cart()) {
            return new WP_Error('cart_error', 'Could not initialize cart', ['status' => 500]);
        }
        
        // 验证购物车不为空
        if (WC()->cart->is_empty()) {
            return new WP_Error('empty_cart', 'Cart is empty', ['status' => 400]);
        }
        
        $billing = $request->get_param('billing');
        $shipping = $request->get_param('shipping');
        $payment_method = $request->get_param('payment_method');
        $customer_note = $request->get_param('customer_note');
        $ship_to_different_address = $request->get_param('ship_to_different_address');
        
        // 验证必填字段
        if (empty($billing)) {
            return new WP_Error('missing_billing', 'Billing information is required', ['status' => 400]);
        }
        
        if (empty($payment_method)) {
            return new WP_Error('missing_payment_method', 'Payment method is required', ['status' => 400]);
        }
        
        try {
            // 创建订单
            $order = wc_create_order([
                'customer_id' => get_current_user_id()
            ]);
            
            if (is_wp_error($order)) {
                return new WP_Error('order_creation_failed', $order->get_error_message(), ['status' => 500]);
            }
            
            // 添加购物车商品到订单
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $product = $cart_item['data'];
                $order->add_product($product, $cart_item['quantity'], [
                    'subtotal' => $cart_item['line_subtotal'],
                    'total' => $cart_item['line_total']
                ]);
            }
            
            // 设置账单地址
            $order->set_address($billing, 'billing');
            
            // 设置配送地址
            if ($ship_to_different_address && !empty($shipping)) {
                $order->set_address($shipping, 'shipping');
            } else {
                $order->set_address($billing, 'shipping');
            }
            
            // 设置支付方式
            $order->set_payment_method($payment_method);
            
            // 设置客户备注
            if ($customer_note) {
                $order->set_customer_note($customer_note);
            }
            
            // 计算税费和总额
            $order->calculate_totals();
            
            // 更新订单状态
            $order->update_status('pending', 'Order created via REST API');
            
            // 清空购物车
            WC()->cart->empty_cart();
            
            // 返回订单信息
            return rest_ensure_response([
                'success' => true,
                'order_id' => $order->get_id(),
                'order_key' => $order->get_order_key(),
                'order_number' => $order->get_order_number(),
                'status' => $order->get_status(),
                'total' => floatval($order->get_total()),
                'payment_url' => $order->get_checkout_payment_url(),
                'order' => $this->format_order($order)
            ]);
            
        } catch (Exception $e) {
            return new WP_Error('order_creation_failed', $e->getMessage(), ['status' => 500]);
        }
    }
    
    /**
     * 获取订单详情
     */
    public function get_order($request) {
        $order_id = $request->get_param('id');
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return new WP_Error('order_not_found', 'Order not found', ['status' => 404]);
        }
        
        // 验证订单所有权
        $user_id = get_current_user_id();
        if ($order->get_customer_id() != $user_id && !current_user_can('manage_woocommerce')) {
            return new WP_Error('unauthorized', 'Unauthorized access', ['status' => 403]);
        }
        
        return rest_ensure_response($this->format_order($order));
    }
    
    /**
     * 获取用户订单列表
     */
    public function get_orders($request) {
        $user_id = get_current_user_id();
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 10;
        
        $args = [
            'customer' => $user_id,
            'limit' => $per_page,
            'page' => $page,
            'orderby' => 'date',
            'order' => 'DESC'
        ];
        
        $orders = wc_get_orders($args);
        $formatted_orders = [];
        
        foreach ($orders as $order) {
            $formatted_orders[] = $this->format_order($order);
        }
        
        return rest_ensure_response($formatted_orders);
    }
    
    /**
     * 格式化订单数据
     */
    private function format_order($order) {
        $data = [
            'id' => $order->get_id(),
            'order_key' => $order->get_order_key(),
            'order_number' => $order->get_order_number(),
            'status' => $order->get_status(),
            'currency' => $order->get_currency(),
            'date_created' => $order->get_date_created()->date('Y-m-d H:i:s'),
            'date_modified' => $order->get_date_modified() ? $order->get_date_modified()->date('Y-m-d H:i:s') : null,
            'customer_id' => $order->get_customer_id(),
            'customer_note' => $order->get_customer_note(),
            'billing' => [
                'first_name' => $order->get_billing_first_name(),
                'last_name' => $order->get_billing_last_name(),
                'company' => $order->get_billing_company(),
                'address_1' => $order->get_billing_address_1(),
                'address_2' => $order->get_billing_address_2(),
                'city' => $order->get_billing_city(),
                'state' => $order->get_billing_state(),
                'postcode' => $order->get_billing_postcode(),
                'country' => $order->get_billing_country(),
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone()
            ],
            'shipping' => [
                'first_name' => $order->get_shipping_first_name(),
                'last_name' => $order->get_shipping_last_name(),
                'company' => $order->get_shipping_company(),
                'address_1' => $order->get_shipping_address_1(),
                'address_2' => $order->get_shipping_address_2(),
                'city' => $order->get_shipping_city(),
                'state' => $order->get_shipping_state(),
                'postcode' => $order->get_shipping_postcode(),
                'country' => $order->get_shipping_country()
            ],
            'payment_method' => $order->get_payment_method(),
            'payment_method_title' => $order->get_payment_method_title(),
            'transaction_id' => $order->get_transaction_id(),
            'subtotal' => floatval($order->get_subtotal()),
            'discount_total' => floatval($order->get_discount_total()),
            'discount_tax' => floatval($order->get_discount_tax()),
            'shipping_total' => floatval($order->get_shipping_total()),
            'shipping_tax' => floatval($order->get_shipping_tax()),
            'cart_tax' => floatval($order->get_cart_tax()),
            'total' => floatval($order->get_total()),
            'total_tax' => floatval($order->get_total_tax()),
            'items' => []
        ];
        
        // 订单商品
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            
            $data['items'][] = [
                'id' => $item_id,
                'name' => $item->get_name(),
                'product_id' => $item->get_product_id(),
                'variation_id' => $item->get_variation_id(),
                'quantity' => $item->get_quantity(),
                'subtotal' => floatval($item->get_subtotal()),
                'total' => floatval($item->get_total()),
                'tax' => floatval($item->get_total_tax()),
                'sku' => $product ? $product->get_sku() : '',
                'price' => floatval($item->get_total() / $item->get_quantity()),
                'image' => $product ? wp_get_attachment_url($product->get_image_id()) : null
            ];
        }
        
        return $data;
    }
    
    /**
     * 初始化购物车
     */
    private function initialize_cart() {
        if (is_null(WC()->cart)) {
            wc_load_cart();
        }
        
        if (is_null(WC()->session)) {
            WC()->session = new WC_Session_Handler();
            WC()->session->init();
        }
        
        if (is_null(WC()->customer)) {
            WC()->customer = new WC_Customer(get_current_user_id(), true);
        }
        
        return !is_null(WC()->cart);
    }
}
