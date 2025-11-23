<?php
/**
 * EverShop Cart API
 * 
 * 提供购物车相关的 REST API 端点
 */

if (!defined('ABSPATH')) {
    exit;
}

class EverShop_Cart {
    
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
        // 获取购物车
        register_rest_route(self::$namespace, '/cart', [
            'methods' => 'GET',
            'callback' => [$this, 'get_cart'],
            'permission_callback' => '__return_true'
        ]);
        
        // 添加商品到购物车
        register_rest_route(self::$namespace, '/cart/add', [
            'methods' => 'POST',
            'callback' => [$this, 'add_to_cart'],
            'permission_callback' => '__return_true'
        ]);
        
        // 更新购物车商品数量
        register_rest_route(self::$namespace, '/cart/update', [
            'methods' => ['PUT', 'POST'],
            'callback' => [$this, 'update_cart_item'],
            'permission_callback' => '__return_true'
        ]);
        
        // 从购物车移除商品
        register_rest_route(self::$namespace, '/cart/remove', [
            'methods' => ['DELETE', 'POST'],
            'callback' => [$this, 'remove_from_cart'],
            'permission_callback' => '__return_true'
        ]);
        
        // 清空购物车
        register_rest_route(self::$namespace, '/cart/clear', [
            'methods' => ['DELETE', 'POST'],
            'callback' => [$this, 'clear_cart'],
            'permission_callback' => '__return_true'
        ]);
        
        // 应用优惠券
        register_rest_route(self::$namespace, '/cart/coupon', [
            'methods' => 'POST',
            'callback' => [$this, 'apply_coupon'],
            'permission_callback' => '__return_true'
        ]);
        
        // 移除优惠券
        register_rest_route(self::$namespace, '/cart/coupon/remove', [
            'methods' => ['DELETE', 'POST'],
            'callback' => [$this, 'remove_coupon'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    /**
     * 获取购物车
     */
    public function get_cart($request) {
        if (!$this->initialize_cart()) {
            return new WP_Error('cart_error', 'Could not initialize cart', ['status' => 500]);
        }
        
        $cart = WC()->cart;
        
        $cart_data = [
            'items' => [],
            'items_count' => $cart->get_cart_contents_count(),
            'subtotal' => floatval($cart->get_subtotal()),
            'subtotal_tax' => floatval($cart->get_subtotal_tax()),
            'discount_total' => floatval($cart->get_discount_total()),
            'discount_tax' => floatval($cart->get_discount_tax()),
            'shipping_total' => floatval($cart->get_shipping_total()),
            'shipping_tax' => floatval($cart->get_shipping_tax()),
            'total' => floatval($cart->get_total('edit')),
            'total_tax' => floatval($cart->get_total_tax()),
            'coupons' => [],
            'needs_shipping' => $cart->needs_shipping()
        ];
        
        // 购物车商品
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $product_id = $cart_item['product_id'];
            $variation_id = $cart_item['variation_id'];
            
            $item_data = [
                'key' => $cart_item_key,
                'product_id' => $product_id,
                'variation_id' => $variation_id,
                'quantity' => $cart_item['quantity'],
                'line_subtotal' => floatval($cart_item['line_subtotal']),
                'line_total' => floatval($cart_item['line_total']),
                'line_tax' => floatval($cart_item['line_tax']),
                'product' => [
                    'id' => $product->get_id(),
                    'name' => $product->get_name(),
                    'slug' => $product->get_slug(),
                    'price' => floatval($product->get_price()),
                    'regular_price' => floatval($product->get_regular_price()),
                    'sale_price' => $product->get_sale_price() ? floatval($product->get_sale_price()) : null,
                    'on_sale' => $product->is_on_sale(),
                    'stock_status' => $product->get_stock_status(),
                    'stock_quantity' => $product->get_stock_quantity(),
                    'image' => wp_get_attachment_url($product->get_image_id()),
                    'permalink' => get_permalink($product_id)
                ]
            ];
            
            // 变体属性
            if ($variation_id) {
                $item_data['variation'] = $cart_item['variation'];
            }
            
            $cart_data['items'][] = $item_data;
        }
        
        // 优惠券
        foreach ($cart->get_applied_coupons() as $coupon_code) {
            $coupon = new WC_Coupon($coupon_code);
            $cart_data['coupons'][] = [
                'code' => $coupon_code,
                'discount_type' => $coupon->get_discount_type(),
                'amount' => floatval($coupon->get_amount()),
                'discount_total' => floatval($cart->get_coupon_discount_amount($coupon_code))
            ];
        }
        
        return rest_ensure_response($cart_data);
    }
    
    /**
     * 添加商品到购物车
     */
    public function add_to_cart($request) {
        if (!$this->initialize_cart()) {
            return new WP_Error('cart_error', 'Could not initialize cart', ['status' => 500]);
        }
        
        $product_id = $request->get_param('product_id');
        $quantity = $request->get_param('quantity') ?: 1;
        $variation_id = $request->get_param('variation_id') ?: 0;
        $variation = $request->get_param('variation') ?: [];
        
        if (empty($product_id)) {
            return new WP_Error('missing_product_id', 'Product ID is required', ['status' => 400]);
        }
        
        // 验证产品是否存在
        $product = wc_get_product($product_id);
        if (!$product) {
            return new WP_Error('product_not_found', 'Product not found', ['status' => 404]);
        }
        
        // 添加到购物车
        $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation);
        
        if (!$cart_item_key) {
            return new WP_Error('add_to_cart_failed', 'Failed to add product to cart', ['status' => 500]);
        }
        
        // 返回更新后的购物车
        return $this->get_cart($request);
    }
    
    /**
     * 更新购物车商品数量
     */
    public function update_cart_item($request) {
        if (!$this->initialize_cart()) {
            return new WP_Error('cart_error', 'Could not initialize cart', ['status' => 500]);
        }
        
        $cart_item_key = $request->get_param('cart_item_key');
        $quantity = $request->get_param('quantity');
        
        if (empty($cart_item_key)) {
            return new WP_Error('missing_cart_item_key', 'Cart item key is required', ['status' => 400]);
        }
        
        if (!isset(WC()->cart->get_cart()[$cart_item_key])) {
            return new WP_Error('cart_item_not_found', 'Cart item not found', ['status' => 404]);
        }
        
        // 如果数量为0，移除商品
        if ($quantity == 0) {
            WC()->cart->remove_cart_item($cart_item_key);
        } else {
            WC()->cart->set_quantity($cart_item_key, $quantity);
        }
        
        // 返回更新后的购物车
        return $this->get_cart($request);
    }
    
    /**
     * 从购物车移除商品
     */
    public function remove_from_cart($request) {
        if (!$this->initialize_cart()) {
            return new WP_Error('cart_error', 'Could not initialize cart', ['status' => 500]);
        }
        
        $cart_item_key = $request->get_param('cart_item_key');
        
        if (empty($cart_item_key)) {
            return new WP_Error('missing_cart_item_key', 'Cart item key is required', ['status' => 400]);
        }
        
        if (!isset(WC()->cart->get_cart()[$cart_item_key])) {
            return new WP_Error('cart_item_not_found', 'Cart item not found', ['status' => 404]);
        }
        
        WC()->cart->remove_cart_item($cart_item_key);
        
        // 返回更新后的购物车
        return $this->get_cart($request);
    }
    
    /**
     * 清空购物车
     */
    public function clear_cart($request) {
        if (!$this->initialize_cart()) {
            return new WP_Error('cart_error', 'Could not initialize cart', ['status' => 500]);
        }
        
        WC()->cart->empty_cart();
        
        return rest_ensure_response([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }
    
    /**
     * 应用优惠券
     */
    public function apply_coupon($request) {
        if (!$this->initialize_cart()) {
            return new WP_Error('cart_error', 'Could not initialize cart', ['status' => 500]);
        }
        
        $coupon_code = $request->get_param('coupon_code');
        
        if (empty($coupon_code)) {
            return new WP_Error('missing_coupon_code', 'Coupon code is required', ['status' => 400]);
        }
        
        // 验证优惠券
        $coupon = new WC_Coupon($coupon_code);
        
        if (!$coupon->is_valid()) {
            return new WP_Error('invalid_coupon', 'Invalid coupon code', ['status' => 400]);
        }
        
        // 应用优惠券
        $applied = WC()->cart->apply_coupon($coupon_code);
        
        if (!$applied) {
            return new WP_Error('coupon_apply_failed', 'Failed to apply coupon', ['status' => 500]);
        }
        
        // 返回更新后的购物车
        return $this->get_cart($request);
    }
    
    /**
     * 移除优惠券
     */
    public function remove_coupon($request) {
        if (!$this->initialize_cart()) {
            return new WP_Error('cart_error', 'Could not initialize cart', ['status' => 500]);
        }
        
        $coupon_code = $request->get_param('coupon_code');
        
        if (empty($coupon_code)) {
            return new WP_Error('missing_coupon_code', 'Coupon code is required', ['status' => 400]);
        }
        
        WC()->cart->remove_coupon($coupon_code);
        
        // 返回更新后的购物车
        return $this->get_cart($request);
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
