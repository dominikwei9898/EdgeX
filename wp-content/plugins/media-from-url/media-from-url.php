<?php
/**
 * Plugin Name: Media From URL
 * Plugin URI: https://example.com/media-from-url
 * Description: 允许通过 URL 直接添加图片和视频到 WordPress 媒体库
 * Version: 1.0.2
 * Author: dominikwei
 * Text Domain: media-from-url
 * Domain Path: /languages
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

class Media_From_URL {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_upload_from_url', array($this, 'upload_from_url'));
        add_action('post-upload-ui', array($this, 'add_url_upload_button'));
        add_action('admin_footer', array($this, 'add_url_upload_button_footer'));
    }
    
    /**
     * 加载脚本和样式
     */
    public function enqueue_scripts($hook) {
        // 只在媒体库页面加载
        if ($hook !== 'upload.php' && $hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }
        
        wp_enqueue_script(
            'media-from-url-js',
            plugins_url('assets/js/media-from-url.js', __FILE__),
            array('jquery'),
            '1.0.2',
            true
        );
        
        wp_enqueue_style(
            'media-from-url-css',
            plugins_url('assets/css/media-from-url.css', __FILE__),
            array(),
            '1.0.1'
        );
        
        // 传递 AJAX URL 和 nonce
        wp_localize_script('media-from-url-js', 'mediaFromUrlData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('media_from_url_nonce'),
            'strings' => array(
                'title' => __('从 URL 添加媒体', 'media-from-url'),
                'placeholder' => __('输入图片或视频的 URL...', 'media-from-url'),
                'button' => __('添加到媒体库', 'media-from-url'),
                'cancel' => __('取消', 'media-from-url'),
                'uploading' => __('正在上传...', 'media-from-url'),
                'success' => __('上传成功！', 'media-from-url'),
                'error' => __('上传失败，请检查 URL 是否正确', 'media-from-url'),
                'invalid_url' => __('请输入有效的 URL', 'media-from-url'),
            )
        ));
    }
    
    /**
     * 在媒体上传界面添加按钮
     */
    public function add_url_upload_button() {
        ?>
        <div class="media-from-url-wrapper" style="margin-top: 20px; padding: 20px; border: 1px dashed #ccc; background: #f9f9f9;">
            <h3><?php _e('从 URL 添加媒体', 'media-from-url'); ?></h3>
            <p><?php _e('粘贴图片或视频的 URL 地址，直接添加到媒体库', 'media-from-url'); ?></p>
            <button type="button" class="button button-primary" id="media-from-url-btn">
                <?php _e('从 URL 添加', 'media-from-url'); ?>
            </button>
        </div>
        <?php
    }
    
    /**
     * 在媒体库页面添加按钮（通过 JavaScript）
     */
    public function add_url_upload_button_footer() {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'upload') {
            ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                // 检查按钮是否已存在
                if ($('#media-from-url-page-btn').length > 0) {
                    return;
                }
                
                // 在"添加新文件"按钮后添加"从 URL 添加"按钮
                var addNewBtn = $('.page-title-action').first();
                if (addNewBtn.length > 0) {
                    addNewBtn.after('<a href="#" class="page-title-action" id="media-from-url-page-btn" style="margin-left: 10px;">从 URL 添加</a>');
                    
                    // 绑定点击事件
                    $('#media-from-url-page-btn').on('click', function(e) {
                        e.preventDefault();
                        $('#media-from-url-modal').fadeIn(200);
                        $('#media-from-url-input').val('').focus();
                        $('#media-from-url-preview').hide().html('');
                        $('#media-from-url-message').html('').removeClass('error success');
                    });
                } else {
                    // 如果找不到"添加新文件"按钮，在页面标题后添加
                    var pageTitle = $('.wp-heading-inline');
                    if (pageTitle.length > 0) {
                        pageTitle.after('<a href="#" class="page-title-action" id="media-from-url-page-btn" style="margin-left: 10px;">从 URL 添加</a>');
                        
                        $('#media-from-url-page-btn').on('click', function(e) {
                            e.preventDefault();
                            $('#media-from-url-modal').fadeIn(200);
                            $('#media-from-url-input').val('').focus();
                            $('#media-from-url-preview').hide().html('');
                            $('#media-from-url-message').html('').removeClass('error success');
                        });
                    }
                }
            });
            </script>
            <?php
        }
    }
    
    /**
     * AJAX 处理从 URL 上传
     */
    public function upload_from_url() {
        // 验证 nonce
        check_ajax_referer('media_from_url_nonce', 'nonce');
        
        // 检查用户权限
        if (!current_user_can('upload_files')) {
            wp_send_json_error(array('message' => '您没有上传文件的权限'));
            return;
        }
        
        $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
        
        if (empty($url)) {
            wp_send_json_error(array('message' => '请提供有效的 URL'));
            return;
        }
        
        // 验证 URL 格式
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            wp_send_json_error(array('message' => 'URL 格式无效'));
            return;
        }
        
        // 下载文件
        $tmp_file = download_url($url);
        
        if (is_wp_error($tmp_file)) {
            wp_send_json_error(array('message' => '下载文件失败: ' . $tmp_file->get_error_message()));
            return;
        }
        
        // 获取文件信息
        $file_array = array(
            'name' => basename(parse_url($url, PHP_URL_PATH)),
            'tmp_name' => $tmp_file
        );
        
        // 如果文件名为空，生成一个
        if (empty($file_array['name'])) {
            $file_array['name'] = 'media-' . time() . '.jpg';
        }
        
        // 验证文件类型
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/webm', 'video/ogg');
        $file_type = wp_check_filetype($file_array['name']);
        $mime_type = mime_content_type($tmp_file);
        
        if (!in_array($mime_type, $allowed_types)) {
            @unlink($tmp_file);
            wp_send_json_error(array('message' => '不支持的文件类型，仅支持图片（jpg, png, gif, webp）和视频（mp4, webm, ogg）'));
            return;
        }
        
        // 导入到媒体库
        $id = media_handle_sideload($file_array, 0);
        
        // 清理临时文件
        if (file_exists($tmp_file)) {
            @unlink($tmp_file);
        }
        
        if (is_wp_error($id)) {
            wp_send_json_error(array('message' => '导入媒体库失败: ' . $id->get_error_message()));
            return;
        }
        
        // 获取附件信息
        $attachment = get_post($id);
        $attachment_url = wp_get_attachment_url($id);
        
        wp_send_json_success(array(
            'message' => '上传成功！',
            'id' => $id,
            'url' => $attachment_url,
            'title' => $attachment->post_title,
            'type' => wp_attachment_is_image($id) ? 'image' : 'video'
        ));
    }
}

// 初始化插件
Media_From_URL::get_instance();

