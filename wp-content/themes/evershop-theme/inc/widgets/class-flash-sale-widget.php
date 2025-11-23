<?php
/**
 * Flash Sale Widget
 * 倒计时通告栏 Widget
 */

if (!defined('ABSPATH')) exit;

class Evershop_Flash_Sale_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'evershop_flash_sale',
            __('Flash Sale (Countdown)', 'evershop-theme'),
            array('description' => __('Display a countdown timer for flash sales', 'evershop-theme'))
        );
    }

    public function widget($args, $instance) {
        if (empty($instance['enabled'])) return;

        $text = !empty($instance['text']) ? $instance['text'] : '24-HOUR FLASH SALE ENDS TODAY';
        $end_date = !empty($instance['end_date']) ? $instance['end_date'] : date('Y-m-d H:i:s', strtotime('+1 day'));

        echo $args['before_widget'];
        ?>
        <div class="black-friday-countdown">
            <div class="countdown-content">
                <div class="countdown-title-wrapper">
                    <span class="countdown-icon">⚡</span>
                    <span class="countdown-title"><?php echo esc_html($text); ?></span>
                    <span class="countdown-icon">⚡</span>
                </div>
                <div id="flash-countdown" class="countdown-timer" data-end="<?php echo esc_attr($end_date); ?>">
                    <div class="time-unit">
                        <div class="time-value-wrapper">
                            <span class="time-value hours">00</span>
                        </div>
                        <span class="time-label">Hours</span>
                    </div>
                    <span class="time-separator">:</span>
                    <div class="time-unit">
                        <div class="time-value-wrapper">
                            <span class="time-value minutes">00</span>
                        </div>
                        <span class="time-label">Minutes</span>
                    </div>
                    <span class="time-separator">:</span>
                    <div class="time-unit">
                        <div class="time-value-wrapper">
                            <span class="time-value seconds">00</span>
                        </div>
                        <span class="time-label">Seconds</span>
                    </div>
                </div>
            </div>
        </div>
        <?php
        echo $args['after_widget'];
    }

    public function form($instance) {
        $enabled = !empty($instance['enabled']) ? 1 : 0;
        $text = !empty($instance['text']) ? $instance['text'] : '24-HOUR FLASH SALE ENDS TODAY';
        $end_date = !empty($instance['end_date']) ? $instance['end_date'] : date('Y-m-d H:i:s', strtotime('+1 day'));
        ?>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($enabled); ?> id="<?php echo $this->get_field_id('enabled'); ?>" name="<?php echo $this->get_field_name('enabled'); ?>" value="1">
            <label for="<?php echo $this->get_field_id('enabled'); ?>"><?php _e('Enable Flash Sale Bar', 'evershop-theme'); ?></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Sale Text:', 'evershop-theme'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" type="text" value="<?php echo esc_attr($text); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('end_date'); ?>"><?php _e('End Date (YYYY-MM-DD HH:MM:SS):', 'evershop-theme'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('end_date'); ?>" name="<?php echo $this->get_field_name('end_date'); ?>" type="text" value="<?php echo esc_attr($end_date); ?>">
            <small><?php _e('Example: 2025-12-31 23:59:59', 'evershop-theme'); ?></small>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['enabled'] = !empty($new_instance['enabled']) ? 1 : 0;
        $instance['text'] = (!empty($new_instance['text'])) ? sanitize_text_field($new_instance['text']) : '';
        $instance['end_date'] = (!empty($new_instance['end_date'])) ? sanitize_text_field($new_instance['end_date']) : '';
        return $instance;
    }
}

