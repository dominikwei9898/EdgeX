<?php
/**
 * Product Collection Widget
 * 产品橱窗 Widget
 */

if (!defined('ABSPATH')) exit;

class Evershop_Product_Collection_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'evershop_product_collection',
            __('Product Collection', 'evershop-theme'),
            array('description' => __('Display a collection of products', 'evershop-theme'))
        );
    }

    public function widget($args, $instance) {
        if (!class_exists('WooCommerce')) {
            echo '<p class="text-center text-red-400">Please install and activate WooCommerce.</p>';
            return;
        }

        $title = !empty($instance['title']) ? $instance['title'] : 'VETERANS DAY COUNTDOWN SALE';
        $category = !empty($instance['category']) ? intval($instance['category']) : 0;
        $limit = !empty($instance['limit']) ? intval($instance['limit']) : 4;

        echo $args['before_widget'];
        ?>
        <div class="product-collection" style="padding-top: 36px; padding-bottom: 36px;">
            <div class="page-width">
                <?php if ($title) : ?>
                    <div class="section-header text-center mb-8">
                        <h2 class="collection-title"><?php echo esc_html($title); ?></h2>
                    </div>
                <?php endif; ?>

                <div class="products-grid-wrapper">
                    <?php
                    $query_args = array(
                        'post_type'      => 'product',
                        'posts_per_page' => $limit,
                        'status'         => 'publish',
                    );
                    if ($category > 0) {
                        $query_args['tax_query'] = array(
                            array(
                                'taxonomy' => 'product_cat',
                                'field'    => 'term_id',
                                'terms'    => $category,
                            ),
                        );
                    }
                    
                    $loop = new WP_Query($query_args);
                    if ($loop->have_posts()) { ?>
                        <div class="product__grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <?php while ($loop->have_posts()) : $loop->the_post(); global $product; ?>
                                                <?php 
                                // 使用统一的产品卡片组件
                                get_template_part( 'template-parts/product/card', null, array(
                                    'product' => $product
                                ) );
                                            ?>
                            <?php endwhile; ?>
                        </div>
                    <?php } else {
                        echo '<p class="text-center text-gray-400">No products found.</p>';
                    }
                    wp_reset_postdata();
                    ?>
                </div>
            </div>
        </div>
        <?php
        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'VETERANS DAY COUNTDOWN SALE';
        $category = !empty($instance['category']) ? intval($instance['category']) : 0;
        $limit = !empty($instance['limit']) ? intval($instance['limit']) : 4;

        // Get categories
        $categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Section Title:', 'evershop-theme'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category:', 'evershop-theme'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>">
                <option value="0"><?php _e('All Products', 'evershop-theme'); ?></option>
                <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
                    <?php foreach ($categories as $cat) : ?>
                        <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected($category, $cat->term_id); ?>>
                            <?php echo esc_html($cat->name); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Number of Products:', 'evershop-theme'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="number" value="<?php echo esc_attr($limit); ?>" min="1" max="20">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['category'] = (!empty($new_instance['category'])) ? intval($new_instance['category']) : 0;
        $instance['limit'] = (!empty($new_instance['limit'])) ? intval($new_instance['limit']) : 4;
        return $instance;
    }
}

