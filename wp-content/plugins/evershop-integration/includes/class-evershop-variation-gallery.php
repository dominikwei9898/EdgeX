<?php
/**
 * EverShop Variation Gallery
 * 
 * 为 WooCommerce 变体产品添加多图上传功能
 * 每个变体可以有自己的产品图片库
 */

if (!defined('ABSPATH')) {
    exit;
}

class EverShop_Variation_Gallery {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * 初始化钩子
     */
    private function init_hooks() {
        // 后台管理：添加变体图片库字段
        add_action('woocommerce_product_after_variable_attributes', [$this, 'add_variation_gallery_field'], 10, 3);
        
        // 保存变体图片库
        add_action('woocommerce_save_product_variation', [$this, 'save_variation_gallery'], 10, 2);
        
        // 加载后台脚本和样式
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        
        // AJAX 处理：保存图片顺序
        add_action('wp_ajax_evershop_save_variation_gallery_order', [$this, 'ajax_save_gallery_order']);
    }
    
    /**
     * 添加变体图片库字段
     */
    public function add_variation_gallery_field($loop, $variation_data, $variation) {
        $variation_id = $variation->ID;
        $gallery_ids = $this->get_variation_gallery_ids($variation_id);
        ?>
        <div class="form-row form-row-full evershop-variation-gallery-wrapper">
            <h4><?php _e('变体图片库 (Product Gallery)', 'evershop-integration'); ?></h4>
            <p class="description">
                <?php _e('为此变体上传多张图片。第一张图片将作为主图显示。可以拖拽调整顺序。', 'evershop-integration'); ?>
            </p>
            
            <div class="evershop-variation-gallery-container">
                <input 
                    type="hidden" 
                    class="evershop-variation-gallery-ids" 
                    name="evershop_variation_gallery[<?php echo $variation_id; ?>]" 
                    value="<?php echo esc_attr(implode(',', $gallery_ids)); ?>"
                    data-variation-id="<?php echo esc_attr($variation_id); ?>"
                />
                
                <ul class="evershop-variation-gallery-images" data-variation-id="<?php echo esc_attr($variation_id); ?>">
                    <?php
                    if (!empty($gallery_ids)) {
                        foreach ($gallery_ids as $image_id) {
                            echo $this->get_gallery_image_html($image_id);
                        }
                    }
                    ?>
                </ul>
                
                <button type="button" class="button evershop-add-variation-gallery-image" data-variation-id="<?php echo esc_attr($variation_id); ?>">
                    <?php _e('添加图片', 'evershop-integration'); ?>
                </button>
                
                <button type="button" class="button evershop-clear-variation-gallery" data-variation-id="<?php echo esc_attr($variation_id); ?>">
                    <?php _e('清空图库', 'evershop-integration'); ?>
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * 获取图片库图片的 HTML
     */
    private function get_gallery_image_html($image_id) {
        $image = wp_get_attachment_image($image_id, 'thumbnail');
        
        if (!$image) {
            return '';
        }
        
        return sprintf(
            '<li class="image" data-attachment-id="%d">
                %s
                <a href="#" class="delete" title="%s">×</a>
            </li>',
            $image_id,
            $image,
            __('删除图片', 'evershop-integration')
        );
    }
    
    /**
     * 保存变体图片库
     */
    public function save_variation_gallery($variation_id, $i) {
        if (isset($_POST['evershop_variation_gallery'][$variation_id])) {
            $gallery_ids = sanitize_text_field($_POST['evershop_variation_gallery'][$variation_id]);
            
            if (empty($gallery_ids)) {
                delete_post_meta($variation_id, '_evershop_variation_gallery_ids');
            } else {
                $gallery_ids = array_filter(array_map('absint', explode(',', $gallery_ids)));
                update_post_meta($variation_id, '_evershop_variation_gallery_ids', $gallery_ids);
            }
        }
    }
    
    /**
     * 获取变体图片库 IDs
     */
    public static function get_variation_gallery_ids($variation_id) {
        $gallery_ids = get_post_meta($variation_id, '_evershop_variation_gallery_ids', true);
        
        if (empty($gallery_ids) || !is_array($gallery_ids)) {
            return [];
        }
        
        return array_map('absint', $gallery_ids);
    }
    
    /**
     * 获取变体图片库 URLs
     */
    public static function get_variation_gallery_images($variation_id, $size = 'woocommerce_single') {
        $gallery_ids = self::get_variation_gallery_ids($variation_id);
        $images = [];
        
        foreach ($gallery_ids as $image_id) {
            $image = wp_get_attachment_image_src($image_id, $size);
            $full_image = wp_get_attachment_image_src($image_id, 'full');
            
            if ($image) {
                $images[] = [
                    'id' => $image_id,
                    'src' => $image[0],
                    'full_src' => $full_image[0],
                    'width' => $image[1],
                    'height' => $image[2],
                    'srcset' => wp_get_attachment_image_srcset($image_id, $size),
                    'sizes' => wp_get_attachment_image_sizes($image_id, $size),
                    'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true),
                    'title' => get_the_title($image_id)
                ];
            }
        }
        
        return $images;
    }
    
    /**
     * 加载后台脚本
     */
    public function enqueue_admin_scripts($hook) {
        if ('post.php' !== $hook && 'post-new.php' !== $hook) {
            return;
        }
        
        global $post;
        if (empty($post) || 'product' !== $post->post_type) {
            return;
        }
        
        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');
        
        wp_enqueue_script(
            'evershop-variation-gallery-admin',
            EVERSHOP_INTEGRATION_PLUGIN_URL . 'assets/js/variation-gallery-admin.js',
            ['jquery', 'jquery-ui-sortable'],
            EVERSHOP_INTEGRATION_VERSION,
            true
        );
        
        wp_enqueue_style(
            'evershop-variation-gallery-admin',
            EVERSHOP_INTEGRATION_PLUGIN_URL . 'assets/css/variation-gallery-admin.css',
            [],
            EVERSHOP_INTEGRATION_VERSION
        );
        
        wp_localize_script('evershop-variation-gallery-admin', 'evershopGallery', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('evershop_variation_gallery'),
            'i18n' => [
                'select_images' => __('选择图片', 'evershop-integration'),
                'use_images' => __('使用这些图片', 'evershop-integration'),
                'confirm_clear' => __('确定要清空图库吗？', 'evershop-integration')
            ]
        ]);
    }
    
    /**
     * AJAX: 保存图片顺序
     */
    public function ajax_save_gallery_order() {
        check_ajax_referer('evershop_variation_gallery', 'nonce');
        
        if (!current_user_can('edit_products')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        
        $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
        $gallery_ids = isset($_POST['gallery_ids']) ? array_map('absint', $_POST['gallery_ids']) : [];
        
        if (!$variation_id) {
            wp_send_json_error(['message' => 'Invalid variation ID']);
        }
        
        update_post_meta($variation_id, '_evershop_variation_gallery_ids', $gallery_ids);
        
        wp_send_json_success(['message' => 'Gallery order saved']);
    }
}

// 初始化
function evershop_variation_gallery_init() {
    return EverShop_Variation_Gallery::get_instance();
}
add_action('plugins_loaded', 'evershop_variation_gallery_init', 20);

