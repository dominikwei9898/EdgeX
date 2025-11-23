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
    }
    
    /**
     * 添加产品元数据框
     */
    public function add_meta_boxes() {
        add_meta_box(
            'evershop_product_videos',
            '📹 Product Videos (EverShop)',
            [$this, 'render_videos_metabox'],
            'product',
            'normal',
            'high'
        );
        
        add_meta_box(
            'evershop_product_testimonials',
            '💬 Customer Testimonials (EverShop)',
            [$this, 'render_testimonials_metabox'],
            'product',
            'normal',
            'high'
        );
        
        add_meta_box(
            'evershop_product_features',
            '✨ Product Features (EverShop)',
            [$this, 'render_features_metabox'],
            'product',
            'normal',
            'high'
        );
        
        add_meta_box(
            'evershop_key_benefits',
            '🎯 Key Benefits (EverShop)',
            [$this, 'render_key_benefits_metabox'],
            'product',
            'normal',
            'high'
        );
        
        add_meta_box(
            'evershop_product_badge',
            '🏷️ Product Badge (EverShop)',
            [$this, 'render_badge_metabox'],
            'product',
            'side',
            'default'
        );
        
        add_meta_box(
            'evershop_product_extras',
            '📝 Additional Info (EverShop)',
            [$this, 'render_extras_metabox'],
            'product',
            'side',
            'default'
        );
    }
    
    /**
     * 渲染视频元数据框
     */
    public function render_videos_metabox($post) {
        wp_nonce_field('evershop_save_product_meta', 'evershop_product_meta_nonce');
        
        $videos = get_post_meta($post->ID, '_product_videos', true);
        $videos_title = get_post_meta($post->ID, '_videos_title', true);
        $video_array = $videos ? json_decode($videos, true) : [];
        
        ?>
        <div class="evershop-videos-wrapper">
            <p>
                <label for="videos_title"><strong>Videos Section Title:</strong></label><br>
                <input type="text" id="videos_title" name="videos_title" value="<?php echo esc_attr($videos_title); ?>" class="widefat" placeholder="e.g., Watch Jay Cutler in Action">
            </p>
            
            <div id="evershop-videos-list">
                <?php
                if (!empty($video_array)) {
                    foreach ($video_array as $index => $video) {
                        $this->render_video_row($index, $video);
                    }
                }
                ?>
            </div>
            
            <button type="button" class="button button-secondary" id="add-video-btn">
                + Add Video URL
            </button>
            
            <p class="description">Add YouTube, Vimeo, or direct video URLs. These will be displayed on the product page.</p>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            let videoIndex = <?php echo count($video_array); ?>;
            
            $('#add-video-btn').on('click', function() {
                const html = `
                    <div class="evershop-video-row" style="margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <input type="text" name="product_videos[]" value="" class="widefat" placeholder="https://youtube.com/watch?v=..." style="margin-bottom: 5px;">
                        <button type="button" class="button button-small remove-video-btn" style="color: #dc3232;">Remove</button>
                    </div>
                `;
                $('#evershop-videos-list').append(html);
                videoIndex++;
            });
            
            $(document).on('click', '.remove-video-btn', function() {
                $(this).closest('.evershop-video-row').remove();
            });
        });
        </script>
        <?php
    }
    
    private function render_video_row($index, $video) {
        ?>
        <div class="evershop-video-row" style="margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
            <input type="text" name="product_videos[]" value="<?php echo esc_attr($video); ?>" class="widefat" placeholder="https://youtube.com/watch?v=..." style="margin-bottom: 5px;">
            <button type="button" class="button button-small remove-video-btn" style="color: #dc3232;">Remove</button>
        </div>
        <?php
    }
    
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
            
            <button type="button" class="button button-secondary" id="add-feature-btn">
                + Add Feature
            </button>
            
            <p class="description">Add product features (e.g., "High Protein", "Sugar Free"). These will be displayed with checkmark icons.</p>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            let featureIndex = <?php echo count($features_array); ?>;
            
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
            
            $(document).on('click', '.remove-feature-btn', function() {
                $(this).closest('.evershop-feature-row').remove();
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
     * 渲染 Key Benefits 元数据框
     */
    public function render_key_benefits_metabox($post) {
        $benefits = get_post_meta($post->ID, '_espf_key_benefits', true);
        $benefits_array = $benefits ? json_decode($benefits, true) : [];
        
        ?>
        <div class="evershop-benefits-wrapper">
            <p class="description" style="margin-bottom: 15px;">
                Add key benefits for your product. Each benefit includes an icon, title, and description.
                These will be displayed in a 3-column grid below the product description.
            </p>
            
            <div id="evershop-benefits-list">
                <?php
                if (!empty($benefits_array)) {
                    foreach ($benefits_array as $index => $benefit) {
                        $this->render_benefit_row($index, $benefit);
                    }
                }
                ?>
            </div>
            
            <button type="button" class="button button-secondary" id="add-benefit-btn">
                + Add Key Benefit
            </button>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            let benefitIndex = <?php echo count($benefits_array); ?>;
            
            $('#add-benefit-btn').on('click', function() {
                const html = `
                    <div class="evershop-benefit-row" style="margin: 15px 0; padding: 15px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                            <div>
                                <label><strong>Icon/Emoji:</strong></label>
                                <input type="text" name="benefit_icon[]" value="" class="widefat" placeholder="e.g., 💪 or 🎯">
                            </div>
                            <div>
                                <label><strong>Title:</strong></label>
                                <input type="text" name="benefit_title[]" value="" class="widefat" placeholder="e.g., Build Muscle">
                            </div>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <label><strong>Description:</strong></label>
                            <textarea name="benefit_description[]" class="widefat" rows="3" placeholder="Describe the benefit..."></textarea>
                        </div>
                        <button type="button" class="button button-small remove-benefit-btn" style="color: #dc3232;">Remove</button>
                    </div>
                `;
                $('#evershop-benefits-list').append(html);
                benefitIndex++;
            });
            
            $(document).on('click', '.remove-benefit-btn', function() {
                if (confirm('Remove this benefit?')) {
                    $(this).closest('.evershop-benefit-row').remove();
                }
            });
        });
        </script>
        <?php
    }
    
    private function render_benefit_row($index, $benefit) {
        ?>
        <div class="evershop-benefit-row" style="margin: 15px 0; padding: 15px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                <div>
                    <label><strong>Icon/Emoji:</strong></label>
                    <input type="text" name="benefit_icon[]" value="<?php echo esc_attr($benefit['icon'] ?? ''); ?>" class="widefat" placeholder="e.g., 💪 or 🎯">
                </div>
                <div>
                    <label><strong>Title:</strong></label>
                    <input type="text" name="benefit_title[]" value="<?php echo esc_attr($benefit['title'] ?? ''); ?>" class="widefat" placeholder="e.g., Build Muscle">
                </div>
            </div>
            <div style="margin-bottom: 10px;">
                <label><strong>Description:</strong></label>
                <textarea name="benefit_description[]" class="widefat" rows="3" placeholder="Describe the benefit..."><?php echo esc_textarea($benefit['description'] ?? ''); ?></textarea>
            </div>
            <button type="button" class="button button-small remove-benefit-btn" style="color: #dc3232;">Remove</button>
        </div>
        <?php
    }
    
    /**
     * 渲染徽章元数据框
     * 对应 EverShop 的 Marketing Badge 组件
     */
    public function render_badge_metabox($post) {
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
                    <option value="#fe0000" <?php selected($badge_color, '#fe0000'); ?>>🔴 Red (Jay Cutler)</option>
                    <option value="#ef4444" <?php selected($badge_color, '#ef4444'); ?>>🔴 Red (Alternative)</option>
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
        
        // 保存视频
        if (isset($_POST['product_videos'])) {
            $videos = array_filter($_POST['product_videos']);
            update_post_meta($post_id, '_product_videos', wp_json_encode($videos));
        } else {
            delete_post_meta($post_id, '_product_videos');
        }
        
        // 保存视频标题
        if (isset($_POST['videos_title'])) {
            update_post_meta($post_id, '_videos_title', sanitize_text_field($_POST['videos_title']));
        }
        
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
        
        // 保存 Key Benefits
        if (isset($_POST['benefit_title'])) {
            $benefits = [];
            $icons = $_POST['benefit_icon'];
            $titles = $_POST['benefit_title'];
            $descriptions = $_POST['benefit_description'];
            
            for ($i = 0; $i < count($titles); $i++) {
                if (!empty($titles[$i])) {
                    $benefits[] = [
                        'icon' => sanitize_text_field($icons[$i]),
                        'title' => sanitize_text_field($titles[$i]),
                        'description' => sanitize_textarea_field($descriptions[$i])
                    ];
                }
            }
            
            if (!empty($benefits)) {
                update_post_meta($post_id, '_espf_key_benefits', wp_json_encode($benefits));
            } else {
                delete_post_meta($post_id, '_espf_key_benefits');
            }
        } else {
            delete_post_meta($post_id, '_espf_key_benefits');
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
        // 注册产品视频
        register_rest_field('product', 'product_videos', [
            'get_callback' => function($object) {
                $videos = get_post_meta($object['id'], '_product_videos', true);
                return $videos ? json_decode($videos, true) : [];
            },
            'schema' => [
                'description' => 'Product video URLs',
                'type' => 'array'
            ]
        ]);
        
        register_rest_field('product', 'videos_title', [
            'get_callback' => function($object) {
                return get_post_meta($object['id'], '_videos_title', true);
            },
            'schema' => [
                'description' => 'Videos section title',
                'type' => 'string'
            ]
        ]);
        
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
        
        // 注册 Key Benefits
        register_rest_field('product', 'key_benefits', [
            'get_callback' => function($object) {
                $benefits = get_post_meta($object['id'], '_espf_key_benefits', true);
                return $benefits ? json_decode($benefits, true) : [];
            },
            'schema' => [
                'description' => 'Product key benefits',
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'icon' => ['type' => 'string'],
                        'title' => ['type' => 'string'],
                        'description' => ['type' => 'string']
                    ]
                ]
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
}
