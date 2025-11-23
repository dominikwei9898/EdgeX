<?php
/**
 * Hero Banner Widget
 * 主视觉 Banner Widget
 */

if (!defined('ABSPATH')) exit;

class Evershop_Hero_Banner_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'evershop_hero_banner',
            __('Hero Banner', 'evershop-theme'),
            array('description' => __('Display a hero banner with image and CTA', 'evershop-theme'))
        );
    }

    public function widget($args, $instance) {
        $image = !empty($instance['image']) ? $instance['image'] : '';
        $image_mobile = !empty($instance['image_mobile']) ? $instance['image_mobile'] : '';
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $subtitle = !empty($instance['subtitle']) ? $instance['subtitle'] : '';
        $btn_text = !empty($instance['btn_text']) ? $instance['btn_text'] : '';
        $btn_link = !empty($instance['btn_link']) ? $instance['btn_link'] : '#';

        $has_content = !empty(trim(strip_tags($title))) || !empty($subtitle) || !empty($btn_text);

        echo $args['before_widget'];
        
        // 如果没有图片，显示占位符和配置提示
        if (empty($image)) {
            ?>
            <div class="hero-banner-placeholder relative w-full overflow-hidden bg-[#1a1a1a] min-h-[400px] flex items-center justify-center border-2 border-dashed border-gray-600">
                <div class="text-center p-8">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="text-xl font-bold text-white mb-2">Hero Banner</h3>
                    <p class="text-gray-400 mb-4">请上传 Banner 图片以显示内容</p>
                    <p class="text-sm text-gray-500">推荐尺寸: 1920x800px (Desktop)</p>
                </div>
            </div>
            <?php
            echo $args['after_widget'];
            return;
        }
        
        ?>
        <div class="hero-banner relative w-full overflow-hidden">
            <picture>
                <?php if($image_mobile): ?>
                    <source media="(max-width: 767px)" srcset="<?php echo esc_url($image_mobile); ?>">
                <?php endif; ?>
                <img src="<?php echo esc_url($image); ?>" alt="Hero Banner" class="w-full h-auto object-cover min-h-[300px] md:min-h-[500px]">
            </picture>
            
            <?php if ($has_content): ?>
            <div class="absolute inset-0 flex items-center banner-overlay">
                <div class="page-width w-full">
                    <div class="banner-content-box">
                        <?php if($subtitle): ?>
                            <p class="banner-subtitle"><?php echo esc_html($subtitle); ?></p>
                        <?php endif; ?>
                        
                        <?php if($title): ?>
                            <h2 class="banner-title"><?php echo wp_kses_post($title); ?></h2>
                        <?php endif; ?>
                        
                        <?php if($btn_text): ?>
                            <div class="banner-actions">
                                <a href="<?php echo esc_url($btn_link); ?>" class="banner-button">
                                    <?php echo esc_html($btn_text); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
        echo $args['after_widget'];
    }

    public function form($instance) {
        $image = !empty($instance['image']) ? $instance['image'] : '';
        $image_mobile = !empty($instance['image_mobile']) ? $instance['image_mobile'] : '';
        $title = !empty($instance['title']) ? $instance['title'] : 'Super Sale <br>BLACK FRIDAY';
        $subtitle = !empty($instance['subtitle']) ? $instance['subtitle'] : 'Limited Offer';
        $btn_text = !empty($instance['btn_text']) ? $instance['btn_text'] : 'SHOP NOW';
        $btn_link = !empty($instance['btn_link']) ? $instance['btn_link'] : '#';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('image'); ?>"><?php _e('Banner Image (Desktop):', 'evershop-theme'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('image'); ?>" name="<?php echo $this->get_field_name('image'); ?>" type="text" value="<?php echo esc_attr($image); ?>" placeholder="<?php _e('Image URL', 'evershop-theme'); ?>">
            <button type="button" class="button evershop-upload-image" data-target="<?php echo $this->get_field_id('image'); ?>"><?php _e('Upload Image', 'evershop-theme'); ?></button>
            <?php if ($image): ?>
                <img src="<?php echo esc_url($image); ?>" style="max-width: 100%; height: auto; margin-top: 10px; border: 1px solid #ddd; border-radius: 4px;" alt="Preview">
            <?php endif; ?>
            <br><small><?php _e('Recommended: 1920x800px', 'evershop-theme'); ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('image_mobile'); ?>"><?php _e('Banner Image (Mobile):', 'evershop-theme'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('image_mobile'); ?>" name="<?php echo $this->get_field_name('image_mobile'); ?>" type="text" value="<?php echo esc_attr($image_mobile); ?>" placeholder="<?php _e('Mobile Image URL (Optional)', 'evershop-theme'); ?>">
            <button type="button" class="button evershop-upload-image" data-target="<?php echo $this->get_field_id('image_mobile'); ?>"><?php _e('Upload Mobile Image', 'evershop-theme'); ?></button>
            <?php if ($image_mobile): ?>
                <img src="<?php echo esc_url($image_mobile); ?>" style="max-width: 100%; height: auto; margin-top: 10px; border: 1px solid #ddd; border-radius: 4px;" alt="Mobile Preview">
            <?php endif; ?>
            <br><small><?php _e('Optional. Recommended: 800x800px', 'evershop-theme'); ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('subtitle'); ?>"><?php _e('Subtitle:', 'evershop-theme'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('subtitle'); ?>" name="<?php echo $this->get_field_name('subtitle'); ?>" type="text" value="<?php echo esc_attr($subtitle); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title (HTML Allowed):', 'evershop-theme'); ?></label>
            <textarea class="widefat" rows="3" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>"><?php echo esc_textarea($title); ?></textarea>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('btn_text'); ?>"><?php _e('Button Text:', 'evershop-theme'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('btn_text'); ?>" name="<?php echo $this->get_field_name('btn_text'); ?>" type="text" value="<?php echo esc_attr($btn_text); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('btn_link'); ?>"><?php _e('Button Link:', 'evershop-theme'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('btn_link'); ?>" name="<?php echo $this->get_field_name('btn_link'); ?>" type="url" value="<?php echo esc_attr($btn_link); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['image'] = (!empty($new_instance['image'])) ? esc_url_raw($new_instance['image']) : '';
        $instance['image_mobile'] = (!empty($new_instance['image_mobile'])) ? esc_url_raw($new_instance['image_mobile']) : '';
        $instance['title'] = (!empty($new_instance['title'])) ? wp_kses_post($new_instance['title']) : '';
        $instance['subtitle'] = (!empty($new_instance['subtitle'])) ? sanitize_text_field($new_instance['subtitle']) : '';
        $instance['btn_text'] = (!empty($new_instance['btn_text'])) ? sanitize_text_field($new_instance['btn_text']) : '';
        $instance['btn_link'] = (!empty($new_instance['btn_link'])) ? esc_url_raw($new_instance['btn_link']) : '';
        return $instance;
    }
}

