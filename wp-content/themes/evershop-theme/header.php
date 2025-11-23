<?php
/**
 * Header Template
 * 
 * 基于 Jay Cutler 主题风格的深色 Header
 * 
 * @package Gym_Nutrition_Theme
 * @author dominikwei
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class('bg-background'); ?>>
<?php wp_body_open(); ?>

<div id="app" class="bg-background">
    <div class="wrapper">
        
        <!-- Header - 完全基于 EverShop Header.tsx 结构 -->
        <header class="header px-6">
            <!-- Area: headerTop -->
            <?php if (is_active_sidebar('header-top')) : ?>
            <div class="header__top">
                <?php dynamic_sidebar('header-top'); ?>
            </div>
            <?php endif; ?>
            
            <!-- Header Middle - 三列 Flexbox 布局 -->
            <div class="header__middle flex items-center justify-between">
                
                <!-- Area: headerMiddleLeft - 汉堡菜单和桌面端Logo/菜单 -->
                <div class="header__middle__left flex justify-start items-center flex-shrink-0 gap-4">
                    <?php if (is_active_sidebar('header-middle-left')) : ?>
                        <?php dynamic_sidebar('header-middle-left'); ?>
                    <?php elseif (has_nav_menu('header-middle-left')) : ?>
                        <!-- Logo - 桌面端显示（基于 EverShop BasicMenu Line 47-59） -->
                        <div class="logo-container flex-shrink-0 hidden md:block">
                            <?php if (has_custom_logo()) : ?>
                                <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-link flex items-center">
                                    <?php the_custom_logo(); ?>
                                </a>
                            <?php else : ?>
                                <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-link flex items-center text-white hover:text-cutler-orange transition-colors">
                                    <h1 class="m-0 text-2xl font-bold">
                                        <?php bloginfo('name'); ?>
                                    </h1>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- 导航菜单 - 基于 EverShop BasicMenu Line 60-142 -->
                        <nav class="p-2 relative md:flex md:justify-center" role="navigation">
                            <div class="flex justify-between items-center">
                                <!-- 汉堡菜单图标 - 移动端显示（Line 62-87） -->
                                <div class="md:hidden">
                                    <a href="#" 
                                       id="mobile-menu-toggle"
                                       class="text-white focus:outline-none"
                                       aria-label="<?php esc_attr_e('Toggle menu', 'gym-nutrition-theme'); ?>"
                                       aria-expanded="false">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                                        </svg>
                                    </a>
                                </div>
                                
                                <!-- 菜单列表（Line 89-140） -->
                                <?php
                                wp_nav_menu(array(
                                    'theme_location' => 'header-middle-left',
                                    'menu_class'     => 'hidden md:flex md:justify-center md:space-x-6 absolute md:relative left-[-2.5rem] md:left-0 top-full md:top-auto mt-2 md:mt-0 w-screen md:w-auto md:bg-transparent p-2 md:p-0 min-w-[250px] bg-white z-30 divide-y md:divide-y-0',
                                    'container'      => false,
                                    'items_wrap'     => '<ul id="mobile-menu" class="%2$s">%3$s<li class="relative group md:hidden border-t border-gray-200 mt-2 pt-2"><a href="' . (is_user_logged_in() ? (class_exists('WooCommerce') ? esc_url(wc_get_page_permalink('myaccount')) : esc_url(admin_url('profile.php'))) : (class_exists('WooCommerce') ? esc_url(wc_get_page_permalink('myaccount')) : esc_url(wp_login_url()))) . '" class="hover:text-gray-300 transition-colors block px-2 py-2 flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg><span>' . (is_user_logged_in() ? esc_html__('My Account', 'gym-nutrition-theme') : esc_html__('Log in', 'gym-nutrition-theme')) . '</span></a></li></ul>',
                                    'fallback_cb'    => false,
                                ));
                                ?>
                            </div>
                        </nav>
                    <?php else : ?>
                        <!-- Logo - 桌面端显示在左侧 -->
                        <div class="logo-container flex-shrink-0 hidden md:block">
                            <?php if (has_custom_logo()) : ?>
                                <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-link flex items-center">
                                    <?php the_custom_logo(); ?>
                                </a>
                            <?php else : ?>
                                <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-link flex items-center text-white hover:text-cutler-orange transition-colors">
                                    <h1 class="m-0 text-2xl font-bold">
                                        <?php bloginfo('name'); ?>
                                    </h1>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- 汉堡菜单 - 移动端（优先使用 header-middle-center 的菜单） -->
                        <?php if (has_nav_menu('header-middle-center')) : ?>
                        <nav class="p-2 relative md:hidden" role="navigation">
                            <div class="flex justify-between items-center">
                                <!-- 汉堡菜单图标 -->
                                <div>
                                    <a href="#" 
                                       id="mobile-menu-toggle"
                                       class="text-white focus:outline-none"
                                       aria-label="<?php esc_attr_e('Toggle menu', 'gym-nutrition-theme'); ?>"
                                       aria-expanded="false">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                                        </svg>
                                    </a>
                                </div>
                                
                                <!-- 自定义菜单列表（来自 header-middle-center） -->
                                <?php
                                wp_nav_menu(array(
                                    'theme_location' => 'header-middle-center',
                                    'menu_class'     => 'hidden absolute left-[-2.5rem] top-full mt-2 w-screen p-2 min-w-[250px] bg-white z-30 divide-y',
                                    'container'      => false,
                                    'items_wrap'     => '<ul id="mobile-menu" class="%2$s">%3$s<li class="relative group border-t border-gray-200 mt-2 pt-2"><a href="' . (is_user_logged_in() ? (class_exists('WooCommerce') ? esc_url(wc_get_page_permalink('myaccount')) : esc_url(admin_url('profile.php'))) : (class_exists('WooCommerce') ? esc_url(wc_get_page_permalink('myaccount')) : esc_url(wp_login_url()))) . '" class="hover:text-gray-300 transition-colors block px-2 py-2 flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg><span>' . (is_user_logged_in() ? esc_html__('My Account', 'gym-nutrition-theme') : esc_html__('Log in', 'gym-nutrition-theme')) . '</span></a></li></ul>',
                                    'fallback_cb'    => false,
                                ));
                                ?>
                            </div>
                        </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Area: headerMiddleCenter - Logo移动端显示，桌面端显示菜单 -->
                <div class="header__middle__center flex justify-center items-center flex-grow">
                    <!-- Logo - 移动端始终显示在中间 -->
                    <div class="logo-container flex-shrink-0 md:hidden">
                        <?php if (has_custom_logo()) : ?>
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-link flex items-center">
                                <?php the_custom_logo(); ?>
                            </a>
                        <?php else : ?>
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-link flex items-center text-white hover:text-cutler-orange transition-colors">
                                <h1 class="m-0 text-xl font-bold">
                                    <?php bloginfo('name'); ?>
                                </h1>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- 桌面端：Widget 或自定义菜单 -->
                    <?php if (is_active_sidebar('header-middle-center')) : ?>
                        <div class="hidden md:block">
                            <?php dynamic_sidebar('header-middle-center'); ?>
                        </div>
                    <?php elseif (has_nav_menu('header-middle-center')) : ?>
                        <nav class="header-menu-center hidden md:block" role="navigation">
                            <?php
                            wp_nav_menu(array(
                                'theme_location' => 'header-middle-center',
                                'menu_class'     => 'flex items-center gap-6',
                                'container'      => false,
                                'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                            ));
                            ?>
                        </nav>
                    <?php endif; ?>
                </div>
                
                <!-- Area: headerMiddleRight - 三个功能图标 -->
                <div class="header__middle__right flex justify-end items-center gap-3 flex-shrink-0">
                        <!-- 搜索背景遮罩（桌面端和移动端）-->
                        <div class="search-backdrop"></div>
                        
                        <!-- 内联搜索容器 - 搜索框和图标 -->
                        <div class="inline-search-container">
                            <!-- 搜索输入框 - 初始隐藏，在图标左侧展开 -->
                            <div id="search-input-wrapper">
                                <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                                    <!-- 搜索输入框 -->
                                    <input type="text" 
                                           id="inline-search-input" 
                                           name="s" 
                                           placeholder="<?php esc_attr_e('Search products...', 'gym-nutrition-theme'); ?>" 
                                           autocomplete="off">
                                    
                                    <?php if (class_exists('WooCommerce')) : ?>
                                        <input type="hidden" name="post_type" value="product">
                                    <?php endif; ?>
                                
                                    <!-- 清除按钮 -->
                                    <button type="button" id="clear-search-btn" aria-label="<?php esc_attr_e('Clear', 'gym-nutrition-theme'); ?>">
                                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                    </svg>
                                </button>
                                </form>
                            </div>
                            
                            <!-- 搜索图标按钮 -->
                            <button id="search-icon-toggle" 
                                    class="search__box p-2 text-white hover:text-cutler-orange transition-colors focus:outline-none"
                                    aria-label="<?php esc_attr_e('Search', 'gym-nutrition-theme'); ?>">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- 2. 用户图标 (CustomerIcon) - 桌面端显示 -->
                        <a href="<?php echo is_user_logged_in() ? (class_exists('WooCommerce') ? esc_url(wc_get_page_permalink('myaccount')) : esc_url(admin_url('profile.php'))) : (class_exists('WooCommerce') ? esc_url(wc_get_page_permalink('myaccount')) : esc_url(wp_login_url())); ?>" 
                           class="user__icon hidden md:block p-2 text-white hover:text-cutler-orange transition-colors"
                           aria-label="<?php esc_attr_e(is_user_logged_in() ? 'My Account' : 'Login', 'gym-nutrition-theme'); ?>">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </a>
                        
                        <!-- 3. 购物车图标 (MiniCartIcon) - 触发 Mini Cart 弹窗 -->
                        <?php if (class_exists('WooCommerce')) : ?>
                        <button type="button" 
                                id="minicart-trigger"
                                class="mini-cart-icon relative p-2 text-white hover:text-cutler-orange transition-colors" 
                                aria-label="<?php esc_attr_e('Shopping cart', 'gym-nutrition-theme'); ?>">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <?php 
                            $cart_count = WC()->cart->get_cart_contents_count(); 
                            if ($cart_count > 0) : 
                            ?>
                                <span class="cart-count-badge absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-semibold">
                                    <?php echo $cart_count > 99 ? '99+' : $cart_count; ?>
                                </span>
                            <?php endif; ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Area: headerBottom - 导航菜单 -->
            <?php if (is_active_sidebar('header-bottom')) : ?>
            <div class="header__bottom">
                <?php dynamic_sidebar('header-bottom'); ?>
            </div>
            <?php elseif (has_nav_menu('header-bottom')) : ?>
            <div class="header__bottom">
                <nav class="main-navigation" role="navigation">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'header-bottom',
                        'menu_class'     => 'main-menu',
                        'container'      => false,
                        'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                    ));
                    ?>
                </nav>
            </div>
            <?php endif; ?>
        </header>
        
        <!-- Flash Sale Countdown Area - Between Header and Main Content -->
        <?php 
        // 从 Customizer 读取设置
        $countdown_enabled = get_theme_mod('evershop_countdown_enabled', true);
        $countdown_text = get_theme_mod('evershop_countdown_text', '24-HOUR FLASH SALE ENDS TODAY');
        $countdown_end_date = get_theme_mod('evershop_countdown_end_date', date('Y-m-d H:i:s', strtotime('+1 day')));
        
        // 只有启用时才显示
        if ($countdown_enabled) : 
        ?>
        <div class="flash-countdown-area">
            <div class="black-friday-countdown">
                <div class="countdown-content">
                    <div class="countdown-title-wrapper">
                        <span class="countdown-icon">⚡</span>
                        <span class="countdown-title"><?php echo esc_html($countdown_text); ?></span>
                        <span class="countdown-icon">⚡</span>
                    </div>
                    <div id="flash-countdown" class="countdown-timer" data-end="<?php echo esc_attr($countdown_end_date); ?>">
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
        </div>
        <?php endif; ?>
        
        <!-- Mini Cart 弹窗 -->
        <?php if (class_exists('WooCommerce')) : ?>
            <?php get_template_part('template-parts/mini-cart'); ?>
        <?php endif; ?>
        
        <!-- Main Content - 基于 Base.tsx 结构 -->
        <main class="content">

