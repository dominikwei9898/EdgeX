<?php
/**
 * Custom Widgets for Evershop Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 1. Hero Banner Widget
 */
class Evershop_Hero_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'evershop_hero_widget',
            __('Evershop: Hero Banner', 'evershop-theme'),
            array('description' => __('Displays a large hero banner with image and text', 'evershop-theme'))
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        $image_url = !empty($instance['image_url']) ? $instance['image_url'] : '';
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $link = !empty($instance['link']) ? $instance['link'] : '#';
        
        if ($image_url) {
            ?>
            <div class="hero-banner relative w-full">
                <a href="<?php echo esc_url($link); ?>" class="block relative group">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>" class="w-full h-auto object-cover" />
                    <?php if ($title) : ?>
                    <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-20 transition-opacity group-hover:bg-opacity-10">
                        <h2 class="text-white text-4xl font-bold shadow-sm"><?php echo esc_html($title); ?></h2>
                    </div>
                    <?php endif; ?>
                </a>
            </div>
            <?php
        }
        echo $args['after_widget'];
    }

    public function form($instance) {
        $image_url = !empty($instance['image_url']) ? $instance['image_url'] : '';
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $link = !empty($instance['link']) ? $instance['link'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('image_url'); ?>"><?php _e('Image URL:', 'evershop-theme'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('image_url'); ?>" name="<?php echo $this->get_field_name('image_url'); ?>" type="text" value="<?php echo esc_attr($image_url); ?>" />
            <small><?php _e('Paste the full URL of the image.', 'evershop-theme'); ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title (Optional):', 'evershop-theme'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('link'); ?>"><?php _e('Link URL:', 'evershop-theme'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('link'); ?>" name="<?php echo $this->get_field_name('link'); ?>" type="text" value="<?php echo esc_attr($link); ?>" />
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['image_url'] = (!empty($new_instance['image_url'])) ? strip_tags($new_instance['image_url']) : '';
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['link'] = (!empty($new_instance['link'])) ? strip_tags($new_instance['link']) : '';
        return $instance;
    }
}

/**
 * 2. Flash Sale Countdown Widget
 */
class Evershop_Flash_Sale_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'evershop_flash_sale_widget',
            __('Evershop: Flash Sale Bar', 'evershop-theme'),
            array('description' => __('Displays a countdown timer bar', 'evershop-theme'))
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        $text = !empty($instance['text']) ? $instance['text'] : '24-HOUR FLASH SALE ENDS TODAY';
        $end_date = !empty($instance['end_date']) ? $instance['end_date'] : ''; // Format: 2025-12-31 23:59:59
        
        // JavaScript for countdown needs to be handled. For now, static markup with data attribute.
        ?>
        <div class="flash-sale-bar bg-[#1a1a1a] text-[#fbce07] py-3 px-4 flex flex-wrap items-center justify-center gap-4 text-center" data-end-date="<?php echo esc_attr($end_date); ?>">
            <div class="flex items-center gap-2 font-bold uppercase tracking-wider">
                <span>⚡</span>
                <span><?php echo esc_html($text); ?></span>
                <span>⚡</span>
            </div>
            <div class="countdown-timer flex items-center gap-2 font-mono text-xl" id="flash-countdown-<?php echo $this->id; ?>">
                <!-- JS will populate this -->
                <div class="timer-block"><span class="hours">00</span> <span class="label text-xs block">HOURS</span></div> :
                <div class="timer-block"><span class="minutes">00</span> <span class="label text-xs block">MINUTES</span></div> :
                <div class="timer-block"><span class="seconds">00</span> <span class="label text-xs block">SECONDS</span></div>
            </div>
        </div>
        <script>
        (function() {
            const endDate = new Date("<?php echo esc_js($end_date); ?>").getTime();
            const timer = document.getElementById("flash-countdown-<?php echo $this->id; ?>");
            if (!endDate || !timer) return;
            
            const update = () => {
                const now = new Date().getTime();
                const distance = endDate - now;
                
                if (distance < 0) {
                    timer.innerHTML = "EXPIRED";
                    return;
                }
                
                const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((distance % (1000 * 60)) / 1000);
                
                timer.querySelector('.hours').innerText = h < 10 ? '0' + h : h;
                timer.querySelector('.minutes').innerText = m < 10 ? '0' + m : m;
                timer.querySelector('.seconds').innerText = s < 10 ? '0' + s : s;
            };
            setInterval(update, 1000);
            update();
        })();
        </script>
        <?php
        echo $args['after_widget'];
    }

    public function form($instance) {
        $text = !empty($instance['text']) ? $instance['text'] : '24-HOUR FLASH SALE ENDS TODAY';
        $end_date = !empty($instance['end_date']) ? $instance['end_date'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Sale Text:', 'evershop-theme'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" type="text" value="<?php echo esc_attr($text); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('end_date'); ?>"><?php _e('End Date (YYYY-MM-DD HH:MM:SS):', 'evershop-theme'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('end_date'); ?>" name="<?php echo $this->get_field_name('end_date'); ?>" type="text" value="<?php echo esc_attr($end_date); ?>" placeholder="2025-12-31 23:59:59" />
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['text'] = (!empty($new_instance['text'])) ? strip_tags($new_instance['text']) : '';
        $instance['end_date'] = (!empty($new_instance['end_date'])) ? strip_tags($new_instance['end_date']) : '';
        return $instance;
    }
}

/**
 * 3. Product Collection Widget (Grid)
 */
class Evershop_Product_Collection_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'evershop_product_collection_widget',
            __('Evershop: Product Collection', 'evershop-theme'),
            array('description' => __('Displays a grid of products from a specific category', 'evershop-theme'))
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $category = !empty($instance['category']) ? $instance['category'] : '';
        $limit = !empty($instance['limit']) ? $instance['limit'] : 4;
        
        ?>
        <div class="product-collection py-8">
            <?php if ($title) : ?>
            <h2 class="text-center text-2xl font-bold mb-8 uppercase tracking-wide text-white"><?php echo esc_html($title); ?></h2>
            <?php endif; ?>
            
            <?php 
            if (shortcode_exists('products')) {
                // Use WooCommerce shortcode
                $shortcode_args = sprintf('limit="%d" columns="4"', $limit);
                if ($category) {
                    $shortcode_args .= sprintf(' category="%s"', $category);
                }
                echo do_shortcode('[products ' . $shortcode_args . ']');
            } else {
                echo '<p class="text-center text-gray-500">WooCommerce not active.</p>';
            }
            ?>
        </div>
        <?php
        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $category = !empty($instance['category']) ? $instance['category'] : '';
        $limit = !empty($instance['limit']) ? $instance['limit'] : 4;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Section Title:', 'evershop-theme'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category Slug:', 'evershop-theme'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" type="text" value="<?php echo esc_attr($category); ?>" />
            <small>Leave empty for all products.</small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Number of Products:', 'evershop-theme'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="number" value="<?php echo esc_attr($limit); ?>" />
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['category'] = (!empty($new_instance['category'])) ? strip_tags($new_instance['category']) : '';
        $instance['limit'] = (!empty($new_instance['limit'])) ? intval($new_instance['limit']) : 4;
        return $instance;
    }
}

