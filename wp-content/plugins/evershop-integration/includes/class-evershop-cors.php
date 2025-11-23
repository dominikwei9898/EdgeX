<?php
/**
 * EverShop CORS Handler
 * 
 * 处理跨域资源共享（CORS），允许 React 前端访问 WordPress REST API
 */

if (!defined('ABSPATH')) {
    exit;
}

class EverShop_CORS {
    
    public static function init() {
        $instance = new self();
        $instance->setup_hooks();
    }
    
    private function setup_hooks() {
        // 添加 CORS 头部
        add_action('rest_api_init', [$this, 'add_cors_headers']);
        
        // 处理 OPTIONS 预检请求
        add_action('init', [$this, 'handle_preflight']);
    }
    
    /**
     * 添加 CORS 头部
     */
    public function add_cors_headers() {
        // 获取允许的来源
        $allowed_origins = $this->get_allowed_origins();
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        
        // 检查来源是否在允许列表中
        if (in_array($origin, $allowed_origins) || in_array('*', $allowed_origins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce, X-Requested-With');
        header('Access-Control-Expose-Headers: X-WP-Total, X-WP-TotalPages');
        header('Access-Control-Max-Age: 86400');
    }
    
    /**
     * 处理 OPTIONS 预检请求
     */
    public function handle_preflight() {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $this->add_cors_headers();
            status_header(200);
            exit;
        }
    }
    
    /**
     * 获取允许的来源列表
     */
    private function get_allowed_origins() {
        $origins = get_option('evershop_cors_origins', 'http://localhost:3000');
        
        if (empty($origins)) {
            return ['http://localhost:3000'];
        }
        
        // 如果是逗号分隔的字符串，转换为数组
        if (is_string($origins)) {
            $origins = array_map('trim', explode(',', $origins));
        }
        
        // 过滤空值
        $origins = array_filter($origins);
        
        // 如果为空，返回默认值
        if (empty($origins)) {
            return ['http://localhost:3000'];
        }
        
        return $origins;
    }
}
