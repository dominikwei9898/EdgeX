<?php
/**
 * EdgeX Content Builder
 * 
 * 灵活的产品页面内容模块构建器
 * 不依赖 ACF，使用原生 WordPress Meta Boxes
 * 
 * @package EdgeX_Content_Builder
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class EverShop_Content_Builder {
    
    /**
     * 单例实例
     */
    private static $instance = null;
    
    /**
     * 可用的内容块类型
     */
    private $block_types = array();
    
    /**
     * 获取单例实例
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 初始化（静态调用入口）
     */
    public static function init() {
        return self::get_instance();
    }
    
    /**
     * 构造函数
     */
    private function __construct() {
        $this->register_block_types();
        
        // 注册后台管理界面
        add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
        add_action('save_post_product', array($this, 'save_content_blocks'), 10, 2);
        
        // 注册前端资源
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // 注册前端渲染钩子
        add_action('woocommerce_after_single_product_summary', array($this, 'render_frontend_content_blocks'), 15);
        add_action('edgex_after_product_summary_content_blocks', array($this, 'render_frontend_content_blocks'));
    }
    
    /**
     * 注册可用的内容块类型
     */
    private function register_block_types() {
        $this->block_types = array(
            'image_module' => array(
                'label' => '图文模块',
                'icon' => '🖼️',
                'description' => '支持单图/多图+文字，灵活布局',
                'fields' => array(
                    'title' => array('type' => 'text', 'label' => '模块名称（仅后台显示）', 'placeholder' => 'Banner', 'description' => '此标题仅用于后台识别模块，不会显示在前端'),
                    'image' => array('type' => 'image', 'label' => '图片 (桌面端)'),
                    'mobile_image' => array('type' => 'image', 'label' => '图片 (移动端，可选)', 'placeholder' => '留空则使用桌面端图片'),
                    'image_position' => array(
                        'type' => 'select', 
                        'label' => '图片位置', 
                        'options' => array(
                            'left' => '左侧（图文左右布局）',
                            'right' => '右侧（图文左右布局）',
                            'full' => '全宽（图片占满容器）'
                        ), 
                        'default' => 'left'
                    ),
                    'product_images' => array(
                        'type' => 'repeater',
                        'label' => '产品图片',
                        'button_label' => '+ 添加更多图片',
                        'description' => '添加多张图片将显示为横向画廊',
                        'fields' => array(
                            'image' => array('type' => 'image', 'label' => '产品图片 (桌面端)'),
                            'mobile_image' => array('type' => 'image', 'label' => '产品图片 (移动端，可选)', 'placeholder' => '留空则使用桌面端图片'),
                            'alt_text' => array('type' => 'text', 'label' => '图片描述', 'placeholder' => 'Product Image'),
                        )
                    ),
                    'button_text' => array('type' => 'text', 'label' => '按钮文字', 'placeholder' => 'Supplement Facts'),
                    'button_action' => array(
                        'type' => 'select',
                        'label' => '点击图片/按钮动作',
                        'options' => array(
                            'none' => '无动作',
                            'link' => '跳转链接',
                            'scroll' => '滚动到指定位置'
                        ),
                        'default' => 'none'
                    ),
                    'button_link' => array('type' => 'url', 'label' => '跳转链接', 'show_if' => array('button_action' => array('link'))),
                    'scroll_target' => array('type' => 'text', 'label' => '滚动目标 CSS 选择器', 'placeholder' => '#section-id', 'show_if' => array('button_action' => array('scroll'))),
                    'background_color' => array('type' => 'color', 'label' => '背景颜色', 'default' => '#ffffff'),
                    'text_color' => array('type' => 'color', 'label' => '文字颜色', 'default' => '#000000'),
                    'button_bg_color' => array('type' => 'color', 'label' => '按钮背景色', 'default' => '#000000'),
                    'button_text_color' => array('type' => 'color', 'label' => '按钮文字色', 'default' => '#ffffff'),
                )
            ),
            
            'key_benefits' => array(
                'label' => '关键优势',
                'icon' => '⭐',
                'description' => '展示产品的核心卖点（2列布局）',
                'fields' => array(
                    'title' => array('type' => 'text', 'label' => '模块标题', 'placeholder' => 'KEY BENEFITS'),
                    'background_color' => array('type' => 'color', 'label' => '背景颜色', 'default' => '#f5f5f5'),
                    'top_background_image' => array('type' => 'image', 'label' => '顶部背景装饰图（右上角）'),
                    'bottom_background_image' => array('type' => 'image', 'label' => '底部背景装饰图（左下角）'),
                    'benefits' => array(
                        'type' => 'repeater',
                        'label' => '优势列表',
                        'button_label' => '+ 添加优势',
                        'fields' => array(
                            'icon' => array('type' => 'image', 'label' => '图标图片'),
                            'title' => array('type' => 'text', 'label' => '标题', 'placeholder' => 'IMPROVED METABOLISM'),
                            'description' => array('type' => 'textarea', 'label' => '描述'),
                        )
                    )
                )
            ),
            
            'video_carousel' => array(
                'label' => '视频轮播',
                'icon' => '🎬',
                'description' => '展示多个产品视频',
                'fields' => array(
                    'title' => array('type' => 'text', 'label' => '模块标题', 'placeholder' => 'BURN, RECOVER, REPEAT WITH LIQUID L CARNITINE'),
                    'background_color' => array('type' => 'color', 'label' => '背景颜色', 'default' => '#000000'),
                    'videos' => array(
                        'type' => 'repeater',
                        'label' => '视频列表',
                        'button_label' => '+ 添加视频',
                        'fields' => array(
                            'video_url' => array('type' => 'url', 'label' => '视频链接', 'placeholder' => 'https://youtube.com/watch?v=xxx'),
                        )
                    )
                )
            ),
            
            'testimonials' => array(
                'label' => '客户评价',
                'icon' => '💬',
                'description' => '展示客户真实评价（轮播展示）',
                'fields' => array(
                    'title' => array('type' => 'text', 'label' => '模块标题', 'placeholder' => 'REAL REVIEWS FROM REAL PEOPLE'),
                    'testimonials' => array(
                        'type' => 'repeater',
                        'label' => '评价列表',
                        'button_label' => '+ 添加评价',
                        'fields' => array(
                            'avatar' => array('type' => 'image', 'label' => '头像（选填）'),
                            'name' => array('type' => 'text', 'label' => '客户姓名', 'placeholder' => 'John Doe'),
                            'title' => array('type' => 'text', 'label' => '评价标题', 'placeholder' => 'Game-Changer'),
                            'content' => array('type' => 'textarea', 'label' => '评价内容'),
                            'rating' => array('type' => 'number', 'label' => '评分（1-5）', 'min' => 1, 'max' => 5, 'default' => 5),
                        )
                    )
                )
            ),
            
            'custom_html' => array(
                'label' => '自定义 HTML',
                'icon' => '💻',
                'description' => '插入自定义 HTML/CSS 代码',
                'fields' => array(
                    'html_content' => array('type' => 'textarea', 'label' => 'HTML 代码', 'rows' => 10),
                    'css_content' => array('type' => 'textarea', 'label' => 'CSS 样式（选填）', 'rows' => 5),
                    'background_color' => array('type' => 'color', 'label' => '背景颜色', 'default' => '#ffffff'),
                )
            ),
        );
        
        // 允许其他插件/主题扩展内容块类型
        $this->block_types = apply_filters('edgex_content_builder_block_types', $this->block_types);
    }
    
    /**
     * 注册 Meta Boxes
     */
    public function register_meta_boxes() {
        add_meta_box(
            'edgex_content_blocks',
            '📦 EdgeX 内容模块构建器',
            array($this, 'render_meta_box'),
            'product',
            'normal',
            'high'
        );
    }
    
    /**
     * 渲染 Meta Box
     */
    public function render_meta_box($post) {
        wp_nonce_field('edgex_content_blocks_save', 'edgex_content_blocks_nonce');
        
        $content_blocks = get_post_meta($post->ID, '_edgex_content_blocks', true);
        if (!is_array($content_blocks)) {
            $content_blocks = array();
        }
        
        ?>
        <div id="edgex-content-builder" class="edgex-content-builder">
            
            <!-- 提示信息 -->
            <div class="edgex-builder-intro">
                <p><strong>💡 使用说明：</strong>通过拖拽调整模块顺序，点击模块可编辑内容。</p>
            </div>
            
            <!-- 已添加的内容块列表 -->
            <div class="edgex-blocks-list" id="edgex-blocks-list">
                <?php if (!empty($content_blocks)) : ?>
                    <?php foreach ($content_blocks as $index => $block) : ?>
                        <?php $this->render_block_item($index, $block); ?>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="edgex-empty-state">
                        <p>📭 还没有添加任何内容模块</p>
                        <p>点击下方按钮添加您的第一个模块</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- 添加新模块按钮 -->
            <div class="edgex-add-block-section">
                <button type="button" class="button edgex-add-block-btn">
                    ➕ 添加内容模块
                </button>
                
                <!-- 模块类型选择器（默认隐藏） -->
                <div class="edgex-block-type-picker" style="display: none;">
                    <h4>选择模块类型</h4>
                    <div class="edgex-block-types-grid">
                        <?php foreach ($this->block_types as $type => $config) : ?>
                            <div class="edgex-block-type-card" data-block-type="<?php echo esc_attr($type); ?>">
                                <div class="block-type-icon"><?php echo esc_html($config['icon']); ?></div>
                                <div class="block-type-label"><?php echo esc_html($config['label']); ?></div>
                                <div class="block-type-desc"><?php echo esc_html($config['description']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- 隐藏字段用于存储数据 -->
            <input type="hidden" name="edgex_content_blocks_data" id="edgex_content_blocks_data" 
                   value="<?php echo esc_attr(wp_json_encode($content_blocks)); ?>">
        </div>
        
        <!-- 模块编辑器模板 -->
        <?php $this->render_editor_templates(); ?>
        
        <style>
        .edgex-content-builder {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }
        
        .edgex-builder-intro {
            background: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #2271b1;
            border-radius: 4px;
        }
        
        .edgex-builder-intro p {
            margin: 5px 0;
            font-size: 13px;
        }
        
        .edgex-blocks-list {
            min-height: 100px;
            margin-bottom: 20px;
        }
        
        .edgex-empty-state {
            background: #fff;
            padding: 40px;
            text-align: center;
            border: 2px dashed #ddd;
            border-radius: 8px;
            color: #666;
        }
        
        .edgex-block-item {
            background: #fff;
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 6px;
            border: 2px solid #ddd;
            cursor: move;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s;
        }
        
        .edgex-block-item:hover {
            border-color: #2271b1;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .edgex-block-item.dragging {
            opacity: 0.5;
        }
        
        .block-item-left {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
        }
        
        .block-drag-handle {
            cursor: grab;
            font-size: 20px;
            color: #999;
        }
        
        .block-drag-handle:active {
            cursor: grabbing;
        }
        
        .block-item-info {
            flex: 1;
        }
        
        .block-item-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 4px;
        }
        
        .block-item-type {
            font-size: 12px;
            color: #666;
        }
        
        .block-item-actions {
            display: flex;
            gap: 8px;
        }
        
        .block-item-actions button {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .edgex-add-block-section {
            text-align: center;
        }
        
        .edgex-add-block-btn {
            padding: 12px 24px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
        }
        
        .edgex-block-type-picker {
            margin-top: 20px;
            background: #fff;
            padding: 20px;
            border-radius: 6px;
        }
        
        .edgex-block-types-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 15px;
        }
        
        .edgex-block-type-card {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 6px;
            border: 2px solid transparent;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
        }
        
        .edgex-block-type-card:hover {
            border-color: #2271b1;
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .block-type-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .block-type-label {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 6px;
        }
        
        .block-type-desc {
            font-size: 12px;
            color: #666;
            line-height: 1.4;
        }
        
        /* 模态框样式 */
        .edgex-block-editor-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 100000;
            background: rgba(0,0,0,0.7);
        }
        
        .edgex-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
        }
        
        .edgex-modal-header {
            background: #2271b1;
            color: #fff;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
        
        .edgex-modal-header h3 {
            margin: 0;
            color: #fff;
        }
        
        .edgex-modal-close {
            background: none;
            border: none;
            color: #fff;
            font-size: 28px;
            cursor: pointer;
            line-height: 1;
        }
        
        .edgex-modal-body {
            padding: 30px;
            overflow-y: auto;
            flex: 1 1 auto;
            min-height: 0;
        }
        
        .edgex-modal-footer {
            padding: 20px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-shrink: 0;
            background: #fff;
        }
        
        .edgex-modal-footer .button {
            min-height: 36px;
        }
        
        .edgex-modal-save {
            background: #2271b1 !important;
            border-color: #2271b1 !important;
            color: #fff !important;
        }
        
        .edgex-modal-save:hover {
            background: #135e96 !important;
            border-color: #135e96 !important;
        }
        
        .edgex-field-group {
            margin-bottom: 20px;
        }
        
        .edgex-field-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .edgex-field-input,
        .edgex-field-textarea,
        .edgex-field-select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .edgex-field-textarea {
            min-height: 100px;
            font-family: monospace;
        }
        
        .edgex-repeater-items {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            background: #f9f9f9;
        }
        
        .edgex-repeater-item {
            background: #fff;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            position: relative;
        }
        
        .edgex-repeater-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .edgex-repeater-remove {
            background: #dc3232;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .edgex-repeater-add {
            margin-top: 10px;
        }
        
        /* 条件显示字段过渡效果 */
        .edgex-field-group[data-show-if] {
            overflow: hidden;
            transition: opacity 0.2s ease;
        }
        
        .edgex-field-group[data-show-if]:not([style*="display: none"]) {
            opacity: 1;
        }
        
        /* 字段描述样式 */
        .edgex-field-description {
            margin: 8px 0 0 0;
            font-size: 12px;
            color: #666;
            font-style: italic;
            line-height: 1.4;
        }
        </style>
        <?php
    }
    
    /**
     * 渲染单个内容块项目
     */
    private function render_block_item($index, $block) {
        $block_type = isset($block['type']) ? $block['type'] : 'unknown';
        $block_config = isset($this->block_types[$block_type]) ? $this->block_types[$block_type] : null;
        
        if (!$block_config) {
            return;
        }
        
        $block_label = $block_config['label'];
        $block_icon = $block_config['icon'];
        $block_title = isset($block['data']['title']) ? $block['data']['title'] : '未命名';
        
        ?>
        <div class="edgex-block-item" data-block-index="<?php echo esc_attr($index); ?>" data-block-type="<?php echo esc_attr($block_type); ?>">
            <div class="block-item-left">
                <span class="block-drag-handle">☰</span>
                <div class="block-item-info">
                    <div class="block-item-title">
                        <?php echo esc_html($block_icon); ?> 
                        <?php echo esc_html($block_title ?: $block_label); ?>
                    </div>
                    <div class="block-item-type"><?php echo esc_html($block_label); ?></div>
                </div>
            </div>
            <div class="block-item-actions">
                <button type="button" class="button edgex-edit-block">✏️ 编辑</button>
                <button type="button" class="button edgex-duplicate-block">📋 复制</button>
                <button type="button" class="button edgex-remove-block">🗑️ 删除</button>
            </div>
        </div>
        <?php
    }
    
    /**
     * 渲染编辑器模板
     */
    private function render_editor_templates() {
        ?>
        <!-- 模态框模板 -->
        <div id="edgex-block-editor-modal" class="edgex-block-editor-modal">
            <div class="edgex-modal-content">
                <div class="edgex-modal-header">
                    <h3 id="edgex-modal-title">编辑内容模块</h3>
                    <button type="button" class="edgex-modal-close">&times;</button>
                </div>
                <div class="edgex-modal-body" id="edgex-modal-body">
                    <!-- 动态生成的表单字段 -->
                </div>
                <div class="edgex-modal-footer">
                    <button type="button" class="button edgex-modal-cancel">取消</button>
                    <button type="button" class="button button-primary edgex-modal-save">保存</button>
                </div>
            </div>
        </div>
        
        <!-- Block Types Config (JSON) -->
        <script type="application/json" id="edgex-block-types-config">
            <?php echo wp_json_encode($this->block_types); ?>
        </script>
        <?php
    }
    
    /**
     * 加载后台资源
     */
    public function enqueue_admin_assets($hook) {
        if ('post.php' !== $hook && 'post-new.php' !== $hook) {
            return;
        }
        
        global $post;
        if (!$post || $post->post_type !== 'product') {
            return;
        }
        
        // 加载 WordPress 媒体库
        wp_enqueue_media();
        
        // 加载自定义 JS
        wp_enqueue_script(
            'edgex-content-builder',
            EVERSHOP_CONTENT_BUILDER_URL . 'assets/js/content-builder.js',
            array('jquery', 'jquery-ui-sortable'),
            EVERSHOP_CONTENT_BUILDER_VERSION,
            true
        );
        
        wp_localize_script('edgex-content-builder', 'edgexBuilderData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('edgex_builder_nonce'),
        ));
    }
    
    /**
     * 保存内容块数据
     */
    public function save_content_blocks($post_id, $post) {
        // 安全检查
        if (!isset($_POST['edgex_content_blocks_nonce']) || 
            !wp_verify_nonce($_POST['edgex_content_blocks_nonce'], 'edgex_content_blocks_save')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // 保存数据
        if (isset($_POST['edgex_content_blocks_data'])) {
            $content_blocks = json_decode(stripslashes($_POST['edgex_content_blocks_data']), true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($content_blocks)) {
                update_post_meta($post_id, '_edgex_content_blocks', $content_blocks);
            }
        }
    }
    
    /**
     * 前端渲染钩子（自动获取当前产品ID）
     */
    public function render_frontend_content_blocks() {
        global $product;
        
        if (!$product) {
            return;
        }
        
        $product_id = $product->get_id();
        self::render_content_blocks($product_id);
    }
    
    /**
     * 获取产品的内容块（供前端调用）
     */
    public static function get_product_content_blocks($product_id) {
        $blocks = get_post_meta($product_id, '_edgex_content_blocks', true);
        return is_array($blocks) ? $blocks : array();
    }
    
    /**
     * 渲染内容块（供前端调用）
     */
    public static function render_content_blocks($product_id) {
        $blocks = self::get_product_content_blocks($product_id);
        
        if (empty($blocks)) {
            // Debug: 输出调试信息（开发环境）
            if (defined('WP_DEBUG') && WP_DEBUG) {
                echo '<!-- EdgeX Content Builder: No blocks found for product ' . $product_id . ' -->';
            }
            return;
        }
        
        foreach ($blocks as $index => $block) {
            $block_type = isset($block['type']) ? $block['type'] : '';
            
            if (empty($block_type)) {
                continue;
            }
            
            // Debug: 输出块信息
            if (defined('WP_DEBUG') && WP_DEBUG) {
                echo '<!-- EdgeX Content Builder: Rendering block type "' . esc_html($block_type) . '" -->';
            }
            
            // 查找模板文件
            $template_locations = array(
                get_stylesheet_directory() . '/template-parts/product/content-blocks/' . $block_type . '.php',
                get_template_directory() . '/template-parts/product/content-blocks/' . $block_type . '.php',
                EVERSHOP_CONTENT_BUILDER_DIR . 'templates/blocks/' . $block_type . '.php',
            );
            
            $template_file = false;
            foreach ($template_locations as $location) {
                if (file_exists($location)) {
                    $template_file = $location;
                    break;
                }
            }
            
            if ($template_file) {
                // 使块数据在模板中可用
                $block_data = isset($block['data']) ? $block['data'] : array();
                
                // Debug: 输出数据结构（开发环境）
                if (defined('WP_DEBUG') && WP_DEBUG && $block_type === 'video_carousel') {
                    echo '<!-- Video Carousel Data: ' . esc_html(json_encode($block_data, JSON_PRETTY_PRINT)) . ' -->';
                }
                
                include $template_file;
            } else {
                // Debug: 模板文件未找到
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    echo '<!-- EdgeX Content Builder: Template not found for "' . esc_html($block_type) . '" -->';
                }
                
                // 备选：使用钩子允许自定义渲染
                do_action('edgex_render_content_block_' . $block_type, $block, $product_id);
            }
        }
    }
}

// 初始化
EverShop_Content_Builder::init();

