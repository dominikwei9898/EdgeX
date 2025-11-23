<?php
/**
 * EverShop Variant System
 * 
 * 提供变体 REST API 支持
 */

if (!defined('ABSPATH')) {
    exit;
}

class EverShop_Variant_System {
    
    public static function init() {
        $instance = new self();
        $instance->setup_hooks();
    }
    
    private function setup_hooks() {
        // REST API端点
        add_action('rest_api_init', [$this, 'register_variant_api_routes']);
    }
    
    /**
     * 注册REST API路由
     */
    public function register_variant_api_routes() {
        register_rest_route('evershop/v1', '/product/(?P<id>[\d]+)/variants', [
            'methods' => 'GET',
            'callback' => [$this, 'get_product_variants'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    /**
     * 获取产品的所有变体
     */
    public function get_product_variants($request) {
        $product_id = $request['id'];
        $product = wc_get_product($product_id);
        
        if (!$product || !$product->is_type('variable')) {
            return new WP_Error('invalid_product', 'Product not found or not variable', ['status' => 404]);
        }
        
        $variations = $product->get_available_variations();
        $variants = [];
        
        foreach ($variations as $variation_data) {
            $variation_id = $variation_data['variation_id'];
            $variation = wc_get_product($variation_id);
            
            $variants[] = [
                'id' => $variation_id,
                'sku' => $variation->get_sku(),
                'price' => $variation->get_price(),
                'regular_price' => $variation->get_regular_price(),
                'sale_price' => $variation->get_sale_price(),
                'stock_status' => $variation->get_stock_status(),
                'attributes' => $variation->get_attributes(),
                'image' => $variation->get_image_id() ? wp_get_attachment_url($variation->get_image_id()) : '',
                'permalink' => get_permalink($variation->get_parent_id()) . '?' . http_build_query($variation->get_attributes())
            ];
        }
        
        return [
            'product_id' => $product_id,
            'variants' => $variants
        ];
    }
}

