<?php
/**
 * Home Page Customizer Settings
 * 复刻 EverShop 的 Widget 配置功能
 */

function evershop_home_customize_register($wp_customize) {
    // 添加面板：首页小工具
    $wp_customize->add_panel('evershop_home_panel', array(
        'title'       => __('Home Page Widgets', 'evershop-theme'),
        'description' => __('Manage Home Page Sections like EverShop Widgets', 'evershop-theme'),
        'priority'    => 20,
    ));

    // ============================================================
    // 1. Top Notification / Flash Sale (倒计时通告栏)
    // ============================================================
    $wp_customize->add_section('evershop_flash_sale', array(
        'title'    => __('Top Flash Sale (Countdown)', 'evershop-theme'),
        'panel'    => 'evershop_home_panel',
        'priority' => 10,
    ));

    // 开关
    $wp_customize->add_setting('flash_sale_enabled', array('default' => true, 'sanitize_callback' => 'evershop_sanitize_checkbox'));
    $wp_customize->add_control('flash_sale_enabled', array(
        'label'    => __('Enable Flash Sale Bar', 'evershop-theme'),
        'section'  => 'evershop_flash_sale',
        'type'     => 'checkbox',
    ));

    // 文本
    $wp_customize->add_setting('flash_sale_text', array('default' => '24-HOUR FLASH SALE ENDS TODAY', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('flash_sale_text', array(
        'label'    => __('Sale Text', 'evershop-theme'),
        'section'  => 'evershop_flash_sale',
        'type'     => 'text',
    ));

    // 结束时间
    $wp_customize->add_setting('flash_sale_end_date', array('default' => date('Y-m-d H:i:s', strtotime('+1 day')), 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('flash_sale_end_date', array(
        'label'       => __('End Date (YYYY-MM-DD HH:MM:SS)', 'evershop-theme'),
        'description' => 'Example: 2025-12-31 23:59:59',
        'section'     => 'evershop_flash_sale',
        'type'     => 'text',
    ));

    // ============================================================
    // 2. Hero Banner (主视觉)
    // ============================================================
    $wp_customize->add_section('evershop_hero_banner', array(
        'title'    => __('Hero Banner', 'evershop-theme'),
        'panel'    => 'evershop_home_panel',
        'priority' => 20,
    ));

    // 图片
    $wp_customize->add_setting('hero_image', array('sanitize_callback' => 'esc_url_raw'));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'hero_image', array(
        'label'    => __('Banner Image (Desktop)', 'evershop-theme'),
        'description' => __('Recommended size: 1920x800px', 'evershop-theme'),
        'section'  => 'evershop_hero_banner',
    )));

    // 图片 (Mobile) - 新增
    $wp_customize->add_setting('hero_image_mobile', array('sanitize_callback' => 'esc_url_raw'));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'hero_image_mobile', array(
        'label'    => __('Banner Image (Mobile)', 'evershop-theme'),
        'description' => __('Optional. Recommended size: 800x800px. Falls back to desktop image if not set.', 'evershop-theme'),
        'section'  => 'evershop_hero_banner',
    )));

    // 标题 (HTML允许)
    $wp_customize->add_setting('hero_title', array('default' => 'Super Sale <br><span class="text-red-600">BLACK FRIDAY</span>', 'sanitize_callback' => 'wp_kses_post'));
    $wp_customize->add_control('hero_title', array(
        'label'    => __('Headline (HTML Allowed)', 'evershop-theme'),
        'section'  => 'evershop_hero_banner',
        'type'     => 'textarea',
    ));

    // 副标题
    $wp_customize->add_setting('hero_subtitle', array('default' => 'Limited Offer', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('hero_subtitle', array(
        'label'    => __('Subtitle', 'evershop-theme'),
        'section'  => 'evershop_hero_banner',
        'type'     => 'text',
    ));

    // 按钮
    $wp_customize->add_setting('hero_btn_text', array('default' => 'SHOP NOW', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('hero_btn_text', array(
        'label'    => __('Button Text', 'evershop-theme'),
        'section'  => 'evershop_hero_banner',
        'type'     => 'text',
    ));

    $wp_customize->add_setting('hero_btn_link', array('default' => '#', 'sanitize_callback' => 'esc_url_raw'));
    $wp_customize->add_control('hero_btn_link', array(
        'label'    => __('Button Link', 'evershop-theme'),
        'section'  => 'evershop_hero_banner',
        'type'     => 'url',
    ));

    // ============================================================
    // 3. Product Collection (产品橱窗)
    // ============================================================
    $wp_customize->add_section('evershop_collection_1', array(
        'title'    => __('Product Collection', 'evershop-theme'),
        'panel'    => 'evershop_home_panel',
        'priority' => 30,
    ));

    $wp_customize->add_setting('collection_1_title', array('default' => 'VETERANS DAY COUNTDOWN SALE', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('collection_1_title', array(
        'label'    => __('Section Title', 'evershop-theme'),
        'section'  => 'evershop_collection_1',
        'type'     => 'text',
    ));

    // 获取 WooCommerce 分类
    $categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));
    $cats = array('0' => 'All Products');
    if (!empty($categories) && !is_wp_error($categories)) {
        foreach ($categories as $cat) {
            $cats[$cat->term_id] = $cat->name;
        }
    }

    $wp_customize->add_setting('collection_1_cat', array('default' => '0', 'sanitize_callback' => 'absint'));
    $wp_customize->add_control('collection_1_cat', array(
        'label'    => __('Select Category', 'evershop-theme'),
        'section'  => 'evershop_collection_1',
        'type'     => 'select',
        'choices'  => $cats,
    ));

    $wp_customize->add_setting('collection_1_limit', array('default' => 4, 'sanitize_callback' => 'absint'));
    $wp_customize->add_control('collection_1_limit', array(
        'label'    => __('Number of Products', 'evershop-theme'),
        'section'  => 'evershop_collection_1',
        'type'     => 'number',
    ));
}
add_action('customize_register', 'evershop_home_customize_register');

// Sanitize Checkbox
function evershop_sanitize_checkbox($checked) {
    return ((isset($checked) && true == $checked) ? true : false);
}

