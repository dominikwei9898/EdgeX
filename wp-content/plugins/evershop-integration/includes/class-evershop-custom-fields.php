<?php
/**
 * EverShop Custom Fields
 * 
 * 管理产品自定义字段（视频、评价、规格参数、徽章等）
 */

if (!defined('ABSPATH')) {
    exit;
}

class EverShop_Custom_Fields {
    
    public static function init() {
        $instance = new self();
        $instance->setup_hooks();
    }
    
    private function setup_hooks() {
        // 添加产品元数据框
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        
        // 保存产品元数据
        add_action('save_post_product', [$this, 'save_product_meta'], 10, 2);
        
        // 在 REST API 中暴露自定义字段
        add_action('rest_api_init', [$this, 'register_custom_fields_in_api']);
        
        // 添加管理脚本和样式
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // AJAX 保存 Product Features
        add_action('wp_ajax_save_product_features', [$this, 'ajax_save_product_features']);
    }
    
    /**
     * 添加产品元数据框
     * 
     * 注意：Videos、Testimonials、Key Benefits 已整合到 EdgeX Content Builder
     * 保留 Product Features、Badge 和 SubHeading 作为产品基本信息
     */
    public function add_meta_boxes() {
        // ✅ Product Features 保持独立显示（在价格下方）
        add_meta_box(
            'evershop_product_features',
            '✨ Product Features',
            [$this, 'render_features_metabox'],
            'product',
            'normal',
            'high'
        );
        
        // ✅ 已整合到 EdgeX Content Builder - 不再显示独立的 Meta Boxes
        // add_meta_box('evershop_product_videos', ...);
        // add_meta_box('evershop_product_testimonials', ...);
        // add_meta_box('evershop_key_benefits', ...);
        
        // 保留产品基本信息字段
        add_meta_box(
            'evershop_product_badge',
            '🏷️ Product Badge',
            [$this, 'render_badge_metabox'],
            'product',
            'side',
            'default'
        );
        
        add_meta_box(
            'evershop_product_extras',
            '📝 SubHeading',
            [$this, 'render_extras_metabox'],
            'product',
            'side',
            'default'
        );
    }
    
    // 视频功能已整合到 EdgeX Content Builder
    
    /**
     * 渲染评价元数据框
     */
    public function render_testimonials_metabox($post) {
        $testimonials = get_post_meta($post->ID, '_product_testimonials', true);
        $testimonials_title = get_post_meta($post->ID, '_testimonials_title', true);
        $testimonials_array = $testimonials ? json_decode($testimonials, true) : [];
        
        ?>
        <div class="evershop-testimonials-wrapper">
            <p>
                <label for="testimonials_title"><strong>Testimonials Section Title:</strong></label><br>
                <input type="text" id="testimonials_title" name="testimonials_title" value="<?php echo esc_attr($testimonials_title); ?>" class="widefat" placeholder="e.g., What Our Customers Say">
            </p>
            
            <div id="evershop-testimonials-list">
                <?php
                if (!empty($testimonials_array)) {
                    foreach ($testimonials_array as $index => $testimonial) {
                        $this->render_testimonial_row($index, $testimonial);
                    }
                }
                ?>
            </div>
            
            <button type="button" class="button button-secondary" id="add-testimonial-btn">
                + Add Testimonial
            </button>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            let testimonialIndex = <?php echo count($testimonials_array); ?>;
            
            $('#add-testimonial-btn').on('click', function() {
                const html = `
                    <div class="evershop-testimonial-row" style="margin: 15px 0; padding: 15px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
                        <p>
                            <label><strong>Author Name:</strong></label>
                            <input type="text" name="testimonial_author[]" value="" class="widefat" placeholder="John Doe">
                        </p>
                        <p>
                            <label><strong>Rating (1-5):</strong></label>
                            <input type="number" name="testimonial_rating[]" value="5" min="1" max="5" step="0.5" class="small-text">
                        </p>
                        <p>
                            <label><strong>Content:</strong></label>
                            <textarea name="testimonial_content[]" class="widefat" rows="3" placeholder="This product is amazing..."></textarea>
                        </p>
                        <p>
                            <label><strong>Avatar URL (optional):</strong></label>
                            <input type="text" name="testimonial_avatar[]" value="" class="widefat" placeholder="https://example.com/avatar.jpg">
                        </p>
                        <button type="button" class="button button-small remove-testimonial-btn" style="color: #dc3232;">Remove</button>
                    </div>
                `;
                $('#evershop-testimonials-list').append(html);
                testimonialIndex++;
            });
            
            $(document).on('click', '.remove-testimonial-btn', function() {
                $(this).closest('.evershop-testimonial-row').remove();
            });
        });
        </script>
        <?php
    }
    
    private function render_testimonial_row($index, $testimonial) {
        ?>
        <div class="evershop-testimonial-row" style="margin: 15px 0; padding: 15px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
            <p>
                <label><strong>Author Name:</strong></label>
                <input type="text" name="testimonial_author[]" value="<?php echo esc_attr($testimonial['author'] ?? ''); ?>" class="widefat" placeholder="John Doe">
            </p>
            <p>
                <label><strong>Rating (1-5):</strong></label>
                <input type="number" name="testimonial_rating[]" value="<?php echo esc_attr($testimonial['rating'] ?? 5); ?>" min="1" max="5" step="0.5" class="small-text">
            </p>
            <p>
                <label><strong>Content:</strong></label>
                <textarea name="testimonial_content[]" class="widefat" rows="3" placeholder="This product is amazing..."><?php echo esc_textarea($testimonial['content'] ?? ''); ?></textarea>
            </p>
            <p>
                <label><strong>Avatar URL (optional):</strong></label>
                <input type="text" name="testimonial_avatar[]" value="<?php echo esc_attr($testimonial['avatar'] ?? ''); ?>" class="widefat" placeholder="https://example.com/avatar.jpg">
            </p>
            <button type="button" class="button button-small remove-testimonial-btn" style="color: #dc3232;">Remove</button>
        </div>
        <?php
    }
    
    /**
     * 渲染产品特性元数据框
     */
    public function render_features_metabox($post) {
        wp_nonce_field('save_product_features', 'product_features_nonce');
        
        $features = get_post_meta($post->ID, '_espf_features', true);
        $features_array = $features ? json_decode($features, true) : [];
        
        ?>
        <div class="evershop-features-wrapper">
            <div id="evershop-features-list">
                <?php
                if (!empty($features_array)) {
                    foreach ($features_array as $index => $feature) {
                        $this->render_feature_row($index, $feature);
                    }
                }
                ?>
            </div>
            
            <div style="margin-top: 15px; display: flex; align-items: center; gap: 15px;">
            <button type="button" class="button button-secondary" id="add-feature-btn">
                + Add Feature
            </button>
            
                <button type="button" class="button button-primary" id="save-features-btn" data-post-id="<?php echo $post->ID; ?>">
                    💾 Save Features
                </button>
                
                <span id="features-save-status" style="display: none; padding: 5px 10px; border-radius: 3px;"></span>
            </div>
            
            <p class="description" style="margin-top: 10px;">Add product features (e.g., "High Protein", "Sugar Free"). These will be displayed with checkmark icons.</p>
        </div>
        
        <style>
        .features-save-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .features-save-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .features-save-loading {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            let featureIndex = <?php echo count($features_array); ?>;
            
            // 添加新特性
            $('#add-feature-btn').on('click', function() {
                const html = `
                    <div class="evershop-feature-row" style="margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <input type="text" name="product_features[]" value="" class="widefat" placeholder="e.g., High Protein Content" style="margin-bottom: 5px;">
                        <button type="button" class="button button-small remove-feature-btn" style="color: #dc3232;">Remove</button>
                    </div>
                `;
                $('#evershop-features-list').append(html);
                featureIndex++;
            });
            
            // 移除特性
            $(document).on('click', '.remove-feature-btn', function() {
                $(this).closest('.evershop-feature-row').remove();
            });
            
            // AJAX 保存特性
            $('#save-features-btn').on('click', function() {
                const $btn = $(this);
                const $status = $('#features-save-status');
                const postId = $btn.data('post-id');
                
                // 收集所有特性数据
                const features = [];
                $('input[name="product_features[]"]').each(function() {
                    const value = $(this).val().trim();
                    if (value) {
                        features.push(value);
                    }
                });
                
                // 显示加载状态
                $btn.prop('disabled', true).text('⏳ Saving...');
                $status.removeClass('features-save-success features-save-error')
                       .addClass('features-save-loading')
                       .text('Saving...')
                       .show();
                
                // 发送 AJAX 请求
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'save_product_features',
                        post_id: postId,
                        features: features,
                        nonce: $('#product_features_nonce').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            $status.removeClass('features-save-loading features-save-error')
                                   .addClass('features-save-success')
                                   .text('✓ ' + response.data.message);
                            
                            // 3秒后自动隐藏成功消息
                            setTimeout(function() {
                                $status.fadeOut();
                            }, 3000);
                        } else {
                            $status.removeClass('features-save-loading features-save-success')
                                   .addClass('features-save-error')
                                   .text('✗ ' + response.data.message);
                        }
                    },
                    error: function() {
                        $status.removeClass('features-save-loading features-save-success')
                               .addClass('features-save-error')
                               .text('✗ Save failed. Please try again.');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('💾 Save Features');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    private function render_feature_row($index, $feature) {
        ?>
        <div class="evershop-feature-row" style="margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            <input type="text" name="product_features[]" value="<?php echo esc_attr($feature); ?>" class="widefat" placeholder="e.g., High Protein Content" style="margin-bottom: 5px;">
            <button type="button" class="button button-small remove-feature-btn" style="color: #dc3232;">Remove</button>
        </div>
        <?php
    }
    
    /**
     * 渲染徽章元数据框
     * 对应 EverShop 的 Marketing Badge 组件
     */
    public function render_badge_metabox($post) {
        // 添加 nonce 字段用于安全验证
        wp_nonce_field('evershop_save_product_meta', 'evershop_product_meta_nonce');
        
        $badge_enabled = get_post_meta($post->ID, '_espf_badge_enabled', true);
        $badge_text = get_post_meta($post->ID, '_espf_badge_text', true);
        $badge_color = get_post_meta($post->ID, '_espf_badge_color', true) ?: '#fe0000'; // Jay Cutler Red: rgb(254, 0, 0)
        
        ?>
        <div class="evershop-badge-wrapper">
            <p>
                <label>
                    <input type="checkbox" name="badge_enabled" value="yes" <?php checked($badge_enabled, 'yes'); ?>>
                    <strong>Enable Badge</strong>
                </label>
            </p>
            
            <p>
                <label for="badge_text"><strong>Badge Text:</strong></label><br>
                <input type="text" id="badge_text" name="badge_text" value="<?php echo esc_attr($badge_text); ?>" class="widefat" placeholder="e.g., 30% OFF, NEW, BESTSELLER" maxlength="50">
            </p>
            
            <p>
                <label for="badge_color"><strong>Badge Color:</strong></label><br>
                <select id="badge_color_preset" name="badge_color" class="widefat" style="margin-bottom: 10px;">
                    <option value="#fe0000" <?php selected($badge_color, '#fe0000'); ?>>🔴 Red</option>
                    <option value="#f97316" <?php selected($badge_color, '#f97316'); ?>>🟠 Orange</option>
                    <option value="#eab308" <?php selected($badge_color, '#eab308'); ?>>🟡 Yellow</option>
                    <option value="#22c55e" <?php selected($badge_color, '#22c55e'); ?>>🟢 Green</option>
                    <option value="#3b82f6" <?php selected($badge_color, '#3b82f6'); ?>>🔵 Blue</option>
                    <option value="#8b5cf6" <?php selected($badge_color, '#8b5cf6'); ?>>🟣 Purple</option>
                    <option value="#ec4899" <?php selected($badge_color, '#ec4899'); ?>>🌸 Pink</option>
                    <option value="#000000" <?php selected($badge_color, '#000000'); ?>>⚫ Black</option>
                </select>
                <input type="color" id="badge_color_custom" value="<?php echo esc_attr($badge_color); ?>" style="height: 40px; width: 100%;">
                <span class="description">Select preset or use custom color picker</span>
            </p>
            
            <div id="badge-preview" style="margin-top: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
                <strong>Preview:</strong><br>
                <span id="badge-preview-display" style="display: inline-block; margin-top: 5px; padding: 5px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; color: white; background: <?php echo esc_attr($badge_color); ?>;">
                    <?php echo esc_html($badge_text ?: 'BADGE TEXT'); ?>
                </span>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            function updateBadgePreview() {
                const text = $('#badge_text').val() || 'BADGE TEXT';
                const presetColor = $('#badge_color_preset').val();
                const customColor = $('#badge_color_custom').val();
                const color = customColor || presetColor;
                $('#badge-preview-display').text(text).css('background-color', color);
            }
            
            // 同步颜色选择器
            $('#badge_color_preset').on('change', function() {
                $('#badge_color_custom').val($(this).val());
                updateBadgePreview();
            });
            
            $('#badge_color_custom').on('change', function() {
                const customColor = $(this).val();
                // 检查是否匹配预设颜色
                const matchingPreset = $('#badge_color_preset option[value="' + customColor + '"]');
                if (matchingPreset.length) {
                    $('#badge_color_preset').val(customColor);
                }
                updateBadgePreview();
            });
            
            $('#badge_text').on('input', updateBadgePreview);
        });
        </script>
        <?php
    }
    
    /**
     * 渲染副标题元数据框
     * 对应 EverShop 的 side_title 字段
     */
    public function render_extras_metabox($post) {
        $subheading = get_post_meta($post->ID, '_espf_subheading', true);
        
        ?>
        <div class="evershop-extras-wrapper">
            <p>
                <label for="product_subheading"><strong>Product Subheading (Side Title):</strong></label><br>
                <textarea id="product_subheading" name="product_subheading" rows="3" class="widefat" placeholder="e.g., ADVANCED L-CARNITINE FORMULA"><?php echo esc_textarea($subheading); ?></textarea>
                <span class="description">Displayed below the badge and above the product title. Typically shown in UPPERCASE.</span>
            </p>
            
            <div style="margin-top: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
                <strong>Preview:</strong><br>
                <div id="subheading-preview" style="margin-top: 5px; font-size: 12px; font-weight: 600; color: #666; text-transform: uppercase; letter-spacing: 0.5px;">
                    <?php echo esc_html($subheading ?: 'Product Subheading Preview'); ?>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#product_subheading').on('input', function() {
                const text = $(this).val() || 'Product Subheading Preview';
                $('#subheading-preview').text(text);
            });
        });
        </script>
        <?php
    }
    
    /**
     * 保存产品元数据
     */
    public function save_product_meta($post_id, $post) {
        // 验证 nonce
        if (!isset($_POST['evershop_product_meta_nonce']) || 
            !wp_verify_nonce($_POST['evershop_product_meta_nonce'], 'evershop_save_product_meta')) {
            return;
        }
        
        // 检查自动保存
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // 检查权限
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // 视频功能已整合到 EdgeX Content Builder
        
        // 保存评价
        if (isset($_POST['testimonial_author'])) {
            $testimonials = [];
            $authors = $_POST['testimonial_author'];
            $ratings = $_POST['testimonial_rating'];
            $contents = $_POST['testimonial_content'];
            $avatars = $_POST['testimonial_avatar'];
            
            for ($i = 0; $i < count($authors); $i++) {
                if (!empty($authors[$i]) && !empty($contents[$i])) {
                    $testimonials[] = [
                        'author' => sanitize_text_field($authors[$i]),
                        'rating' => floatval($ratings[$i]),
                        'content' => sanitize_textarea_field($contents[$i]),
                        'avatar' => esc_url_raw($avatars[$i])
                    ];
                }
            }
            
            update_post_meta($post_id, '_product_testimonials', wp_json_encode($testimonials));
        } else {
            delete_post_meta($post_id, '_product_testimonials');
        }
        
        // 保存评价标题
        if (isset($_POST['testimonials_title'])) {
            update_post_meta($post_id, '_testimonials_title', sanitize_text_field($_POST['testimonials_title']));
        }
        
        // 保存产品特性
        if (isset($_POST['product_features'])) {
            $features = array_filter($_POST['product_features'], function($value) {
                return !empty(trim($value));
            });
            $features = array_map('sanitize_text_field', $features);
            update_post_meta($post_id, '_espf_features', wp_json_encode(array_values($features)));
        } else {
            delete_post_meta($post_id, '_espf_features');
        }
        
        // 保存徽章（使用 _espf_ 前缀与主题一致）
        $badge_enabled = isset($_POST['badge_enabled']) ? 'yes' : 'no';
        update_post_meta($post_id, '_espf_badge_enabled', $badge_enabled);
        
        if (isset($_POST['badge_text'])) {
            $badge_text = sanitize_text_field($_POST['badge_text']);
            // 限制最大长度为 50 字符（对应 EverShop 的 VARCHAR(50)）
            $badge_text = mb_substr($badge_text, 0, 50);
            update_post_meta($post_id, '_espf_badge_text', $badge_text);
        }
        
        if (isset($_POST['badge_color'])) {
            update_post_meta($post_id, '_espf_badge_color', sanitize_hex_color($_POST['badge_color']));
        }
        
        // 保存副标题（对应 EverShop 的 side_title）
        if (isset($_POST['product_subheading'])) {
            update_post_meta($post_id, '_espf_subheading', sanitize_textarea_field($_POST['product_subheading']));
        }
    }
    
    /**
     * 在 REST API 中注册自定义字段
     */
    public function register_custom_fields_in_api() {
        // 视频功能已整合到 EdgeX Content Builder
        
        // 注册评价
        register_rest_field('product', 'testimonials', [
            'get_callback' => function($object) {
                $testimonials = get_post_meta($object['id'], '_product_testimonials', true);
                return $testimonials ? json_decode($testimonials, true) : [];
            },
            'schema' => [
                'description' => 'Customer testimonials',
                'type' => 'array'
            ]
        ]);
        
        register_rest_field('product', 'testimonials_title', [
            'get_callback' => function($object) {
                return get_post_meta($object['id'], '_testimonials_title', true);
            },
            'schema' => [
                'description' => 'Testimonials section title',
                'type' => 'string'
            ]
        ]);
        
        // 注册产品特性
        register_rest_field('product', 'features', [
            'get_callback' => function($object) {
                $features = get_post_meta($object['id'], '_espf_features', true);
                return $features ? json_decode($features, true) : [];
            },
            'schema' => [
                'description' => 'Product features list',
                'type' => 'array'
            ]
        ]);
        
        // 注册徽章（对应 EverShop 的 badge_enabled, badge_text, badge_color）
        register_rest_field('product', 'badge', [
            'get_callback' => function($object) {
                return [
                    'enabled' => get_post_meta($object['id'], '_espf_badge_enabled', true) === 'yes',
                    'text' => get_post_meta($object['id'], '_espf_badge_text', true),
                    'color' => get_post_meta($object['id'], '_espf_badge_color', true) ?: '#fe0000' // Jay Cutler Red
                ];
            },
            'schema' => [
                'description' => 'Product marketing badge',
                'type' => 'object',
                'properties' => [
                    'enabled' => ['type' => 'boolean'],
                    'text' => ['type' => 'string'],
                    'color' => ['type' => 'string']
                ]
            ]
        ]);
        
        register_rest_field('product', 'badge_enabled', [
            'get_callback' => function($object) {
                return get_post_meta($object['id'], '_espf_badge_enabled', true) === 'yes';
            },
            'schema' => ['type' => 'boolean']
        ]);
        
        register_rest_field('product', 'badge_text', [
            'get_callback' => function($object) {
                return get_post_meta($object['id'], '_espf_badge_text', true);
            },
            'schema' => ['type' => 'string']
        ]);
        
        register_rest_field('product', 'badge_color', [
            'get_callback' => function($object) {
                return get_post_meta($object['id'], '_espf_badge_color', true) ?: '#fe0000'; // Jay Cutler Red
            },
            'schema' => ['type' => 'string']
        ]);
        
        // 注册副标题（对应 EverShop 的 side_title）
        register_rest_field('product', 'subheading', [
            'get_callback' => function($object) {
                return get_post_meta($object['id'], '_espf_subheading', true);
            },
            'schema' => [
                'description' => 'Product subheading (side_title)',
                'type' => 'string'
            ]
        ]);
        
        register_rest_field('product', 'side_title', [
            'get_callback' => function($object) {
                return get_post_meta($object['id'], '_espf_subheading', true);
            },
            'schema' => [
                'description' => 'Product side title (alias for subheading)',
                'type' => 'string'
            ]
        ]);
    }
    
    /**
     * 加载管理资源
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'post.php' && $hook !== 'post-new.php') {
            return;
        }
        
        global $post_type;
        if ($post_type !== 'product') {
            return;
        }
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }
    
    /**
     * AJAX 保存产品特性
     */
    public function ajax_save_product_features() {
        // 验证 nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'save_product_features')) {
            wp_send_json_error([
                'message' => 'Security check failed.'
            ]);
            return;
        }
        
        // 验证权限
        $post_id = intval($_POST['post_id']);
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error([
                'message' => 'You do not have permission to edit this product.'
            ]);
            return;
        }
        
        // 获取并验证数据
        $features = isset($_POST['features']) ? $_POST['features'] : [];
        
        // 过滤空值
        $features = array_filter($features, function($feature) {
            return !empty(trim($feature));
        });
        
        // 重新索引数组
        $features = array_values($features);
        
        // 保存到数据库
        $json_features = json_encode($features);
        update_post_meta($post_id, '_espf_features', $json_features);
        
        // 返回成功响应
        wp_send_json_success([
            'message' => 'Product features saved successfully!',
            'count' => count($features),
            'features' => $features
        ]);
    }
}
