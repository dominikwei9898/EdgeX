<?php
/**
 * EverShop Gutenberg Blocks
 * 
 * 注册和管理 Gutenberg 编辑器区块
 */

if (!defined('ABSPATH')) {
    exit;
}

class EverShop_Blocks {
    
    public static function init() {
        $instance = new self();
        $instance->setup_hooks();
    }
    
    private function setup_hooks() {
        // 注册区块
        add_action('init', [$this, 'register_blocks']);
        
        // 加载区块编辑器资源
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets']);
        
        // 加载区块前端资源
        add_action('enqueue_block_assets', [$this, 'enqueue_block_assets']);
    }
    
    /**
     * 注册所有 Gutenberg 区块
     * 
     * 注意：所有内容模块已迁移到 EdgeX Content Builder：
     * - Key Benefits (关键优势)
     * - Image + Text (图文模块)
     * - Video Carousel (视频轮播)
     * - Testimonials (客户评价)
     * - Trust Badges (信任徽章)
     * - Custom HTML (自定义HTML)
     */
    public function register_blocks() {
        // 所有区块功能已整合到 EdgeX Content Builder
        // 保留此方法以避免引用错误
    }
    
    /**
     * 加载区块编辑器资源
     * 
     * 所有区块功能已迁移到 EdgeX Content Builder
     * 保留此方法以避免引用错误，但不再加载资源
     */
    public function enqueue_block_editor_assets() {
        // 区块功能已整合到 EdgeX Content Builder
        // 不再需要加载 Gutenberg 区块资源
    }
    
    /**
     * 加载区块前端资源
     * 
     * 所有区块功能已迁移到 EdgeX Content Builder
     * 保留此方法以避免引用错误，但不再加载资源
     */
    public function enqueue_block_assets() {
        // 区块功能已整合到 EdgeX Content Builder
        // 不再需要加载 Gutenberg 区块资源
    }
}

