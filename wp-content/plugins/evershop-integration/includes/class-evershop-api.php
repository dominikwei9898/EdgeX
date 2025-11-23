<?php
/**
 * EverShop Base API
 * 
 * 提供基础 API 功能和工具方法
 */

if (!defined('ABSPATH')) {
    exit;
}

class EverShop_API {
    
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
        // 健康检查
        register_rest_route(self::$namespace, '/health', [
            'methods' => 'GET',
            'callback' => [$this, 'health_check'],
            'permission_callback' => '__return_true'
        ]);
        
        // API 信息
        register_rest_route(self::$namespace, '/info', [
            'methods' => 'GET',
            'callback' => [$this, 'get_api_info'],
            'permission_callback' => '__return_true'
        ]);
        
        // 站点设置
        register_rest_route(self::$namespace, '/settings', [
            'methods' => 'GET',
            'callback' => [$this, 'get_settings'],
            'permission_callback' => '__return_true'
        ]);
        
        // 菜单
        register_rest_route(self::$namespace, '/menus/(?P<location>[a-zA-Z0-9_-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_menu'],
            'permission_callback' => '__return_true'
        ]);
        
        // 搜索
        register_rest_route(self::$namespace, '/search', [
            'methods' => 'GET',
            'callback' => [$this, 'search'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    /**
     * 健康检查
     */
    public function health_check($request) {
        global $wpdb;
        
        // 检查数据库连接
        $db_check = $wpdb->query('SELECT 1');
        
        // 检查 WooCommerce
        $wc_active = class_exists('WooCommerce');
        
        // 检查 WordPress 版本
        $wp_version = get_bloginfo('version');
        
        $status = [
            'status' => 'ok',
            'timestamp' => current_time('mysql'),
            'wordpress' => [
                'version' => $wp_version,
                'multisite' => is_multisite()
            ],
            'woocommerce' => [
                'active' => $wc_active,
                'version' => $wc_active ? WC()->version : null
            ],
            'database' => [
                'connected' => $db_check !== false
            ],
            'php' => [
                'version' => PHP_VERSION
            ],
            'plugin' => [
                'version' => EVERSHOP_INTEGRATION_VERSION
            ]
        ];
        
        return rest_ensure_response($status);
    }
    
    /**
     * 获取 API 信息
     */
    public function get_api_info($request) {
        $info = [
            'name' => 'EverShop Integration API',
            'version' => EVERSHOP_INTEGRATION_VERSION,
            'namespace' => self::$namespace,
            'endpoints' => [
                'health' => rest_url(self::$namespace . '/health'),
                'products' => rest_url(self::$namespace . '/products'),
                'cart' => rest_url(self::$namespace . '/cart'),
                'checkout' => rest_url(self::$namespace . '/checkout'),
                'auth' => rest_url(self::$namespace . '/auth')
            ],
            'documentation' => admin_url('admin.php?page=evershop-api-docs')
        ];
        
        return rest_ensure_response($info);
    }
    
    /**
     * 获取站点设置
     */
    public function get_settings($request) {
        $settings = [
            'site_name' => get_bloginfo('name'),
            'site_description' => get_bloginfo('description'),
            'site_url' => get_bloginfo('url'),
            'admin_email' => get_bloginfo('admin_email'),
            'timezone' => get_option('timezone_string'),
            'date_format' => get_option('date_format'),
            'time_format' => get_option('time_format'),
            'language' => get_bloginfo('language'),
            'currency' => get_woocommerce_currency(),
            'currency_symbol' => get_woocommerce_currency_symbol(),
            'currency_position' => get_option('woocommerce_currency_pos'),
            'thousand_separator' => wc_get_price_thousand_separator(),
            'decimal_separator' => wc_get_price_decimal_separator(),
            'decimals' => wc_get_price_decimals()
        ];
        
        return rest_ensure_response($settings);
    }
    
    /**
     * 获取菜单
     */
    public function get_menu($request) {
        $location = $request->get_param('location');
        
        // 获取指定位置的菜单
        $locations = get_nav_menu_locations();
        
        if (!isset($locations[$location])) {
            return new WP_Error('menu_not_found', 'Menu not found', ['status' => 404]);
        }
        
        $menu_id = $locations[$location];
        $menu_items = wp_get_nav_menu_items($menu_id);
        
        if (!$menu_items) {
            return rest_ensure_response([]);
        }
        
        // 格式化菜单项
        $formatted_items = [];
        $menu_tree = [];
        
        foreach ($menu_items as $item) {
            $formatted_item = [
                'id' => $item->ID,
                'title' => $item->title,
                'url' => $item->url,
                'target' => $item->target,
                'attr_title' => $item->attr_title,
                'classes' => implode(' ', $item->classes),
                'parent' => $item->menu_item_parent,
                'object_id' => $item->object_id,
                'object' => $item->object,
                'type' => $item->type,
                'children' => []
            ];
            
            $formatted_items[$item->ID] = $formatted_item;
        }
        
        // 构建树形结构
        foreach ($formatted_items as $id => $item) {
            if ($item['parent'] == 0) {
                $menu_tree[$id] = $item;
            } else {
                if (isset($formatted_items[$item['parent']])) {
                    $formatted_items[$item['parent']]['children'][] = $item;
                    $menu_tree = $this->update_menu_tree($menu_tree, $formatted_items[$item['parent']]);
                }
            }
        }
        
        return rest_ensure_response(array_values($menu_tree));
    }
    
    /**
     * 更新菜单树（辅助方法）
     */
    private function update_menu_tree($tree, $updated_item) {
        foreach ($tree as $key => $item) {
            if ($item['id'] == $updated_item['id']) {
                $tree[$key] = $updated_item;
                return $tree;
            }
            if (!empty($item['children'])) {
                $tree[$key]['children'] = $this->update_menu_tree($item['children'], $updated_item);
            }
        }
        return $tree;
    }
    
    /**
     * 搜索
     */
    public function search($request) {
        $query = $request->get_param('q');
        $type = $request->get_param('type') ?: 'product';
        $per_page = $request->get_param('per_page') ?: 10;
        
        if (empty($query)) {
            return new WP_Error('missing_query', 'Search query is required', ['status' => 400]);
        }
        
        $args = [
            'post_type' => $type,
            'post_status' => 'publish',
            's' => sanitize_text_field($query),
            'posts_per_page' => intval($per_page)
        ];
        
        $search_query = new WP_Query($args);
        $results = [];
        
        if ($search_query->have_posts()) {
            while ($search_query->have_posts()) {
                $search_query->the_post();
                $post_id = get_the_ID();
                
                if ($type === 'product') {
                    $product = wc_get_product($post_id);
                    $results[] = [
                        'id' => $post_id,
                        'title' => get_the_title(),
                        'slug' => get_post_field('post_name', $post_id),
                        'permalink' => get_permalink($post_id),
                        'excerpt' => get_the_excerpt(),
                        'image' => get_the_post_thumbnail_url($post_id, 'medium'),
                        'price' => $product ? $product->get_price() : null,
                        'price_html' => $product ? $product->get_price_html() : null
                    ];
                } else {
                    $results[] = [
                        'id' => $post_id,
                        'title' => get_the_title(),
                        'slug' => get_post_field('post_name', $post_id),
                        'permalink' => get_permalink($post_id),
                        'excerpt' => get_the_excerpt(),
                        'image' => get_the_post_thumbnail_url($post_id, 'medium')
                    ];
                }
            }
            wp_reset_postdata();
        }
        
        $response = rest_ensure_response($results);
        $response->header('X-WP-Total', $search_query->found_posts);
        
        return $response;
    }
    
    /**
     * 格式化错误响应
     */
    public static function error_response($code, $message, $status = 400, $data = []) {
        return new WP_Error($code, $message, array_merge(['status' => $status], $data));
    }
    
    /**
     * 格式化成功响应
     */
    public static function success_response($data, $message = null) {
        $response = [
            'success' => true,
            'data' => $data
        ];
        
        if ($message) {
            $response['message'] = $message;
        }
        
        return rest_ensure_response($response);
    }
}
