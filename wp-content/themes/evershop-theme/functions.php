<?php
/**
 * EverShop Theme Functions
 * 
 * @package EverShop_Theme
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // 防止直接访问
}

/**
 * 主题设置
 */
function evershop_theme_setup() {
    // 添加标题标签支持
    add_theme_support('title-tag');
    
    // 添加特色图片支持
    add_theme_support('post-thumbnails');
    
    // 添加自定义 Logo 支持
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    
    // 添加 HTML5 支持
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    
    register_nav_menus(array(
        'header-top'           => __('Header Top Menu', 'evershop-theme'),
        'header-middle-left'   => __('Header Middle Left', 'evershop-theme'),
        'header-middle-center' => __('Header Middle Center', 'evershop-theme'),
        'header-middle-right'  => __('Header Middle Right', 'evershop-theme'),
        'header-bottom'        => __('Header Bottom Menu', 'evershop-theme'),
        'footer-menu'          => __('Footer Menu', 'evershop-theme'),
    ));
    
    // 添加 WooCommerce 支持（如果需要电商功能）
    add_theme_support('woocommerce');
    // add_theme_support('wc-product-gallery-zoom'); // 已禁用 - 不需要 zoom 效果
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'evershop_theme_setup');

/**
 * 注册小工具区域
 */
function evershop_widgets_init() {
    register_sidebar(array(
        'name'          => __('Header Top', 'evershop-theme'),
        'id'            => 'header-top',
        'description'   => __('Header top area', 'evershop-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    register_sidebar(array(
        'name'          => __('Header Middle Left', 'evershop-theme'),
        'id'            => 'header-middle-left',
        'description'   => __('Header middle left area', 'evershop-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    register_sidebar(array(
        'name'          => __('Header Middle Center', 'evershop-theme'),
        'id'            => 'header-middle-center',
        'description'   => __('Header middle center area', 'evershop-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    register_sidebar(array(
        'name'          => __('Header Middle Right', 'evershop-theme'),
        'id'            => 'header-middle-right',
        'description'   => __('Header middle right area', 'evershop-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    register_sidebar(array(
        'name'          => __('Header Bottom', 'evershop-theme'),
        'id'            => 'header-bottom',
        'description'   => __('Header bottom area', 'evershop-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    // Flash Countdown 区域 (Header 和 Content 之间)
    register_sidebar(array(
        'name'          => __('Flash Sale Countdown', 'evershop-theme'),
        'id'            => 'flash-countdown',
        'description'   => __('倒计时通告栏区域 - 位于 Header 和 Main Content 之间，不受 Header CSS 影响', 'evershop-theme'),
        'before_widget' => '',
        'after_widget'  => '',
        'before_title'  => '<h3 class="widget-title" style="display:none;">',
        'after_title'   => '</h3>',
    ));

    // Homepage Content 区域 (专用于首页 Widgets)
    register_sidebar(array(
        'name'          => __('Homepage Content', 'evershop-theme'),
        'id'            => 'homepage-content',
        'description'   => __('首页内容区域 - 拖拽 Flash Sale、Hero Banner、Product Collection 等 Widgets 到此', 'evershop-theme'),
        'before_widget' => '<div id="%1$s" class="widget homepage-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    // Footer Top 区域
    register_sidebar(array(
        'name'          => __('Footer Top', 'evershop-theme'),
        'id'            => 'footer-top',
        'description'   => __('页脚顶部区域', 'evershop-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    // Footer Middle Left 区域
    register_sidebar(array(
        'name'          => __('Footer Middle Left', 'evershop-theme'),
        'id'            => 'footer-middle-left',
        'description'   => __('页脚中间左侧区域', 'evershop-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    // Footer Middle Center 区域
    register_sidebar(array(
        'name'          => __('Footer Middle Center', 'evershop-theme'),
        'id'            => 'footer-middle-center',
        'description'   => __('页脚中间中心区域', 'evershop-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    // Footer Middle Right 区域
    register_sidebar(array(
        'name'          => __('Footer Middle Right', 'evershop-theme'),
        'id'            => 'footer-middle-right',
        'description'   => __('页脚中间右侧区域', 'evershop-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    // Sidebar 区域
    register_sidebar(array(
        'name'          => __('Sidebar', 'evershop-theme'),
        'id'            => 'sidebar-1',
        'description'   => __('主侧边栏区域', 'evershop-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'evershop_widgets_init');

/**
 * 后台 Widget 脚本
 */
function evershop_admin_scripts($hook) {
    // Load media uploader for widgets and customize screens
    if ($hook === 'widgets.php' || $hook === 'customize.php') {
        wp_enqueue_media();
        wp_enqueue_script('evershop-widget-admin', get_template_directory_uri() . '/inc/widgets/widget-admin.js', array('jquery'), '1.0.0', true);
    }
    
    // Load media uploader for product edit screens
    global $post_type;
    if (($hook === 'post.php' || $hook === 'post-new.php') && $post_type === 'product') {
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'evershop_admin_scripts');

/**
 * Helper: Get asset version based on file modification time
 */
function evershop_get_asset_version($path) {
    $file_path = get_template_directory() . $path;
    return file_exists($file_path) ? filemtime($file_path) : '1.0.0';
}

/**
 * Load frontend styles and scripts
 */
function evershop_scripts() {
    // 使用文件修改时间作为版本号，确保 CSS 修改后立即生效（清除缓存）
    wp_enqueue_style('evershop-style', get_stylesheet_uri(), array(), evershop_get_asset_version('/style.css'));
    
    // Cookie Consent 样式和脚本
    wp_enqueue_style(
        'evershop-cookie-consent',
        get_template_directory_uri() . '/assets/css/cookie-consent.css',
        array('evershop-style'),
        evershop_get_asset_version('/assets/css/cookie-consent.css')
    );
    
    wp_enqueue_script(
        'evershop-cookie-consent',
        get_template_directory_uri() . '/assets/js/cookie-consent.js',
        array('jquery'),
        evershop_get_asset_version('/assets/js/cookie-consent.js'),
        true
    );
    
    // 现代化搜索框样式
    wp_enqueue_style(
        'evershop-search-overlay',
        get_template_directory_uri() . '/assets/css/search-overlay.css',
        array('evershop-style'),
        evershop_get_asset_version('/assets/css/search-overlay.css')
    );
    
    // 搜索结果页面样式
    if (is_search() && get_query_var('post_type') === 'product') {
        wp_enqueue_style(
            'evershop-search-results',
            get_template_directory_uri() . '/assets/css/search-results.css',
            array('evershop-style'),
            evershop_get_asset_version('/assets/css/search-results.css')
        );
    }
    
    // Mini Cart styles and scripts (global)
    if (class_exists('WooCommerce')) {
        wp_enqueue_style(
            'evershop-mini-cart',
            get_template_directory_uri() . '/assets/css/mini-cart.css',
            array(),
            evershop_get_asset_version('/assets/css/mini-cart.css')
        );
        
        wp_enqueue_script(
            'evershop-mini-cart',
            get_template_directory_uri() . '/assets/js/mini-cart.js',
            array('jquery'),
            evershop_get_asset_version('/assets/js/mini-cart.js'),
            true
        );
        
        wp_localize_script('evershop-mini-cart', 'evershopMiniCart', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('minicart_nonce'),
            'checkout_url' => wc_get_checkout_url(),
            'currency_symbol' => get_woocommerce_currency_symbol()
        ));
        
        // 在产品分类/商店页面加载产品归档脚本
        if (function_exists('is_shop') && function_exists('is_product_category') && function_exists('is_product_tag') && function_exists('is_product_taxonomy') && (is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy())) {
            wp_enqueue_script(
                'evershop-product-archive',
                get_template_directory_uri() . '/assets/js/product-archive.js',
                array('jquery', 'wc-add-to-cart'),
                evershop_get_asset_version('/assets/js/product-archive.js'),
                true
            );
        }
        
        // 在结账页面添加深色主题应用脚本
        if (function_exists('is_checkout') && is_checkout()) {
            wp_add_inline_script('jquery', "
                jQuery(document).ready(function($) {
                    // 确保结账页面使用深色主题
                    $('article.single-page').css({
                        'background-color': '#1a1a1a !important',
                        'color': '#ffffff !important'
                    });
                });
            ");
        }
    }
    
    if (function_exists('is_product') && is_product()) {
        wp_enqueue_style('evershop-product-style', get_template_directory_uri() . '/product-styles.css', array(), evershop_get_asset_version('/product-styles.css'));
        
        wp_enqueue_style(
            'evershop-product-core-area',
            get_template_directory_uri() . '/assets/css/product-core-area.css',
            array('evershop-style', 'evershop-product-style'),
            evershop_get_asset_version('/assets/css/product-core-area.css')
        );
        
        wp_enqueue_style(
            'evershop-product-gallery',
            get_template_directory_uri() . '/assets/css/product-gallery.css',
            array('evershop-product-style'),
            evershop_get_asset_version('/assets/css/product-gallery.css')
        );
        
        wp_enqueue_style(
            'evershop-collapsible-tabs',
            get_template_directory_uri() . '/assets/css/product-collapsible-tabs.css',
            array('evershop-product-style'),
            evershop_get_asset_version('/assets/css/product-collapsible-tabs.css')
        );
        
        wp_enqueue_style(
            'evershop-content-blocks',
            get_template_directory_uri() . '/assets/css/product-content-blocks.css',
            array('evershop-product-style'),
            evershop_get_asset_version('/assets/css/product-content-blocks.css')
        );
        
        // 变体选择器样式
        wp_enqueue_style(
            'evershop-variant-selector',
            get_template_directory_uri() . '/assets/css/variant-selector.css',
            array('evershop-product-style'),
            evershop_get_asset_version('/assets/css/variant-selector.css')
        );

        wp_enqueue_style(
            'evershop-form-cart-optimize',
            get_template_directory_uri() . '/assets/css/form-cart-optimize.css',
            array('evershop-product-style'),
            evershop_get_asset_version('/assets/css/form-cart-optimize.css')
        );

        // 自定义 Add to Cart 组件样式
        wp_enqueue_style(
            'evershop-custom-add-to-cart',
            get_template_directory_uri() . '/assets/css/custom-add-to-cart.css',
            array('evershop-product-style'),
            evershop_get_asset_version('/assets/css/custom-add-to-cart.css')
        );
        
        // 自定义 Add to Cart 组件兼容性样式（必须在最后加载）
        wp_enqueue_style(
            'evershop-custom-add-to-cart-compat',
            get_template_directory_uri() . '/assets/css/custom-add-to-cart-compat.css',
            array('evershop-custom-add-to-cart', 'evershop-form-cart-optimize'),
            evershop_get_asset_version('/assets/css/custom-add-to-cart-compat.css')
        );
        
        // 调试样式（开发时使用，生产环境请移除）
        if (isset($_GET['debug']) || current_user_can('manage_options')) {
            wp_enqueue_style(
                'evershop-debug-variation-cart',
                get_template_directory_uri() . '/assets/css/debug-variation-cart.css',
                array(),
                time() // 使用时间戳防止缓存
            );
        }

        // Single Product AJAX Add to Cart
        wp_enqueue_script(
            'evershop-single-ajax-add-to-cart',
            get_template_directory_uri() . '/assets/js/single-product-ajax.js',
            array('jquery', 'wc-add-to-cart'),
            evershop_get_asset_version('/assets/js/single-product-ajax.js'),
            true
        );
        
        wp_enqueue_script(
            'evershop-collapsible-tabs',
            get_template_directory_uri() . '/assets/js/collapsible-tabs.js',
            array('jquery'),
            evershop_get_asset_version('/assets/js/collapsible-tabs.js'),
            true
        );
        
        wp_enqueue_script(
            'evershop-product-carousel',
            get_template_directory_uri() . '/assets/js/product-carousel.js',
            array(),
            evershop_get_asset_version('/assets/js/product-carousel.js'),
            true
        );
        
        wp_enqueue_script(
            'evershop-product-variant-display',
            get_template_directory_uri() . '/assets/js/product-variant-display.js',
            array('jquery'),
            evershop_get_asset_version('/assets/js/product-variant-display.js'),
            true
        );
        
        // 变体选择器脚本（已在前面加载样式）
        wp_enqueue_script(
            'evershop-variant-selector',
            get_template_directory_uri() . '/assets/js/variant-selector.js',
            array('jquery', 'wc-add-to-cart-variation'),
            evershop_get_asset_version('/assets/js/variant-selector.js'),
            true
        );
        
        // 自定义 Add to Cart 组件脚本
        wp_enqueue_script(
            'evershop-custom-add-to-cart',
            get_template_directory_uri() . '/assets/js/custom-add-to-cart.js',
            array('jquery', 'wc-add-to-cart', 'wc-add-to-cart-variation'),
            evershop_get_asset_version('/assets/js/custom-add-to-cart.js'),
            true
        );
        
        $current_product = wc_get_product(get_the_ID());
        if ($current_product && $current_product->is_type('variable')) {
            wp_localize_script('evershop-variant-selector', 'evershopVariantData', array(
                'productId' => $current_product->get_id(),
                'restUrl' => rest_url('evershop/v1/'),
                'nonce' => wp_create_nonce('wp_rest'),
                'currency_symbol' => get_woocommerce_currency_symbol()
            ));
        }
    }
    
    // 如果需要额外的 CSS 文件
    // wp_enqueue_style('evershop-custom', get_template_directory_uri() . '/assets/css/custom.css', array(), '1.0.0');
    
    // WordPress 默认脚本
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
    
    // 添加搜索框和移动端菜单 JavaScript - 使用 Tailwind CSS
    wp_add_inline_script('jquery', "
        jQuery(document).ready(function($) {
            // ============================================
            // 内联搜索框 - 响应式淡入淡出效果
            // ============================================
            var searchWrapper = $('#search-input-wrapper');
            var searchInput = $('#inline-search-input');
            var searchToggle = $('#search-icon-toggle');
            var searchContainer = $('.inline-search-container');
            var searchBackdrop = $('.search-backdrop');
            var clearBtn = $('#clear-search-btn');
            var isSearchOpen = false;
            var isMobile = false;
            
            // 检测是否为移动端
            function checkMobile() {
                isMobile = window.innerWidth < 768;
            }
            
            // 打开搜索框
            function openInlineSearch() {
                checkMobile();
                
                searchWrapper.addClass('active');
                searchBackdrop.addClass('active');
                isSearchOpen = true;
                
                if (isMobile) {
                    // 移动端：全屏展开
                    searchContainer.addClass('mobile-search-active');
                    
                    // 防止背景滚动，但保持 touchmove 事件可以触发
                    $('body').css({
                        'overflow': 'hidden',
                        'position': 'fixed',
                        'width': '100%',
                        'height': '100%'
                    });
                } else {
                    // 桌面端：淡化中间菜单
                    $('body').addClass('search-active');
                }
                
                // 聚焦输入框
                setTimeout(function() {
                    searchInput.focus();
                }, isMobile ? 350 : 100);
            }
            
            // 关闭搜索框
            function closeInlineSearch() {
                checkMobile();
                
                searchWrapper.removeClass('active');
                searchBackdrop.removeClass('active');
                searchContainer.removeClass('mobile-search-active');
                
                // 恢复页面滚动
                if (isMobile) {
                    $('body').css({
                        'overflow': '',
                        'position': '',
                        'width': '',
                        'height': ''
                    });
                } else {
                    $('body').removeClass('search-active').css('overflow', '');
                }
                
                isSearchOpen = false;
                
                // 清空输入框
                searchInput.val('');
            }
            
            // 切换搜索框
            function toggleInlineSearch() {
                if (isSearchOpen) {
                    closeInlineSearch();
                } else {
                    openInlineSearch();
                }
            }
            
            // 点击搜索图标切换
            searchToggle.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleInlineSearch();
            });
            
            // 点击清除按钮
            clearBtn.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                searchInput.val('').focus();
            });
            
            // ESC 键关闭
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && isSearchOpen) {
                    closeInlineSearch();
                }
            });
            
            // 点击背景遮罩关闭（桌面端和移动端）
            searchBackdrop.on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeInlineSearch();
            });
            
            // 点击页面其他地方关闭
            $(document).on('click', function(e) {
                if (isSearchOpen) {
                    checkMobile();
                    
                    if (isMobile) {
                        // 移动端：点击搜索输入框外部关闭
                        if (!$(e.target).closest('#search-input-wrapper').length && 
                            !$(e.target).closest('#search-icon-toggle').length) {
                            closeInlineSearch();
                        }
                    } else {
                        // 桌面端：点击搜索容器外部关闭
                        if (!$(e.target).closest('.inline-search-container').length && 
                            !$(e.target).closest('#search-input-wrapper').length) {
                            closeInlineSearch();
                        }
                    }
                }
            });
            
            // 窗口大小改变时重新检测
            $(window).on('resize', function() {
                if (isSearchOpen) {
                    var wasMobile = isMobile;
                    checkMobile();
                    
                    // 如果从桌面切换到移动端或反之，关闭搜索
                    if (wasMobile !== isMobile) {
                        closeInlineSearch();
                    }
                }
            });
            
            // 输入框提交时不关闭（允许搜索）
            searchInput.closest('form').on('submit', function() {
                // 表单正常提交，不阻止
            });
            
            // ============================================
            // 移动端菜单切换功能 - 带平滑动画
            // ============================================
            $('#mobile-menu-toggle').on('click', function(e) {
                e.preventDefault();
                var menu = $('#mobile-menu');
                var toggle = $(this);
                
                // 切换菜单显示（带淡入淡出动画）
                if (menu.hasClass('hidden')) {
                    // 显示菜单
                    menu.removeClass('hidden').addClass('block');
                    // 添加淡入动画
                    menu.css({opacity: 0, transform: 'translateY(-10px)'});
                    setTimeout(function() {
                        menu.css({
                            opacity: 1, 
                            transform: 'translateY(0)',
                            transition: 'opacity 0.2s ease, transform 0.2s ease'
                        });
                    }, 10);
                    toggle.attr('aria-expanded', 'true');
                } else {
                    // 隐藏菜单
                    menu.css({
                        opacity: 0, 
                        transform: 'translateY(-10px)',
                        transition: 'opacity 0.2s ease, transform 0.2s ease'
                    });
                    setTimeout(function() {
                        menu.removeClass('block').addClass('hidden');
                        menu.css({opacity: '', transform: '', transition: ''});
                    }, 200);
                    toggle.attr('aria-expanded', 'false');
                }
            });
            
            // 点击菜单外部关闭
            $(document).on('click', function(e) {
                var menu = $('#mobile-menu');
                var toggle = $('#mobile-menu-toggle');
                
                if (!menu.hasClass('hidden') && 
                    !menu.is(e.target) && 
                    menu.has(e.target).length === 0 &&
                    !toggle.is(e.target) && 
                    toggle.has(e.target).length === 0) {
                    // 带动画关闭
                    menu.css({
                        opacity: 0, 
                        transform: 'translateY(-10px)',
                        transition: 'opacity 0.2s ease, transform 0.2s ease'
                    });
                    setTimeout(function() {
                        menu.removeClass('block').addClass('hidden');
                        menu.css({opacity: '', transform: '', transition: ''});
                    }, 200);
                    toggle.attr('aria-expanded', 'false');
                }
            });
            
            // ESC 键关闭菜单
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && !$('#mobile-menu').hasClass('hidden')) {
                    var menu = $('#mobile-menu');
                    // 带动画关闭
                    menu.css({
                        opacity: 0, 
                        transform: 'translateY(-10px)',
                        transition: 'opacity 0.2s ease, transform 0.2s ease'
                    });
                    setTimeout(function() {
                        menu.removeClass('block').addClass('hidden');
                        menu.css({opacity: '', transform: '', transition: ''});
                    }, 200);
                    $('#mobile-menu-toggle').attr('aria-expanded', 'false');
                }
            });
            
            // ============================================
            // Flash Sale 倒计时功能 - 增强版动画
            // ============================================
            const countdowns = document.querySelectorAll('#flash-countdown');
            countdowns.forEach(function(el) {
                const endStr = el.getAttribute('data-end');
                if (!endStr) return;
                
                const endDate = new Date(endStr.replace(/-/g, '/')).getTime();
                
                // 格式化数字为两位数
                function padNumber(num) {
                    return String(num).padStart(2, '0');
                }
                
                // 更新数字并添加动画效果
                function updateNumber(element, newValue) {
                    const formattedValue = padNumber(newValue);
                    if (element && element.innerText !== formattedValue) {
                        // 添加缩放动画类
                        element.style.transform = 'scale(1.1)';
                        setTimeout(function() {
                            element.innerText = formattedValue;
                            element.style.transform = 'scale(1)';
                        }, 100);
                    }
                }
                
                function updateCountdown() {
                    const now = new Date().getTime();
                    const distance = endDate - now;
                    
                    if (distance < 0) {
                        // 倒计时结束，淡出隐藏
                        $(el).closest('.black-friday-countdown').fadeOut(500);
                        return;
                    }
                    
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    const hEl = el.querySelector('.hours');
                    const mEl = el.querySelector('.minutes');
                    const sEl = el.querySelector('.seconds');
                    
                    // 使用动画更新数字
                    updateNumber(hEl, hours);
                    updateNumber(mEl, minutes);
                    updateNumber(sEl, seconds);
                }
                
                // 立即执行一次
                updateCountdown();
                // 每秒更新
                setInterval(updateCountdown, 1000);
            });
        });
    ");
}
add_action('wp_enqueue_scripts', 'evershop_scripts');

/**
 * Mini Cart AJAX Handlers
 */

// Get mini cart data
function evershop_get_minicart_data() {
    check_ajax_referer('minicart_nonce', 'nonce');
    
    if (!class_exists('WooCommerce')) {
        wp_send_json_error(array('message' => 'WooCommerce not active'));
        return;
    }
    
    $cart = WC()->cart;
    
    if (!$cart) {
        wp_send_json_error(array('message' => 'Cart not available'));
        return;
    }
    
    $items = array();
    
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $product_id = $cart_item['product_id'];
        $variation_id = $cart_item['variation_id'];
        
        $thumbnail_id = $product->get_image_id();
        $image_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'woocommerce_thumbnail') : wc_placeholder_img_src();
        
        $variation_html = '';
        if ($variation_id && !empty($cart_item['variation'])) {
            $variation_html = '<div class="minicart-item-variants">';
            foreach ($cart_item['variation'] as $attribute => $value) {
                $attribute_name = str_replace('attribute_', '', $attribute);
                $attribute_name = str_replace('pa_', '', $attribute_name);
                $attribute_name = ucfirst(str_replace('-', ' ', $attribute_name));
                $variation_html .= '<div class="minicart-item-variant">' . esc_html($attribute_name) . ': ' . esc_html($value) . '</div>';
            }
            $variation_html .= '</div>';
        }
        
        $items[] = array(
            'key'          => $cart_item_key,
            'product_id'   => $product_id,
            'variation_id' => $variation_id,
            'name'         => $product->get_name(),
            'quantity'     => $cart_item['quantity'],
            'price'        => wc_get_price_to_display($product),
            'image'        => $image_url,
            'variation'    => $variation_html,
            'permalink'    => $product->get_permalink()
        );
    }
    
    $cart_data = array(
        'items'    => $items,
        'subtotal' => $cart->get_cart_subtotal(),
        'total'    => $cart->get_cart_total(),
        'count'    => $cart->get_cart_contents_count()
    );
    
    wp_send_json_success($cart_data);
}
add_action('wp_ajax_get_minicart_data', 'evershop_get_minicart_data');
add_action('wp_ajax_nopriv_get_minicart_data', 'evershop_get_minicart_data');

// Update cart item quantity
function evershop_update_cart_item_qty() {
    check_ajax_referer('minicart_nonce', 'nonce');
    
    if (!class_exists('WooCommerce')) {
        wp_send_json_error(array('message' => 'WooCommerce not active'));
        return;
    }
    
    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field($_POST['cart_item_key']) : '';
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    
    if (empty($cart_item_key) || $quantity < 0) {
        wp_send_json_error(array('message' => 'Invalid parameters'));
        return;
    }
    
    $cart = WC()->cart;
    
    if ($quantity == 0) {
        $cart->remove_cart_item($cart_item_key);
    } else {
        $cart->set_quantity($cart_item_key, $quantity, true);
    }
    
    $fragments = array();
    ob_start();
    woocommerce_mini_cart();
    $fragments['div.widget_shopping_cart_content'] = '<div class="widget_shopping_cart_content">' . ob_get_clean() . '</div>';
    
    wp_send_json_success(array(
        'fragments' => $fragments,
        'cart_hash' => WC()->cart->get_cart_hash()
    ));
}
add_action('wp_ajax_update_cart_item_qty', 'evershop_update_cart_item_qty');
add_action('wp_ajax_nopriv_update_cart_item_qty', 'evershop_update_cart_item_qty');

/**
 * 自定义主题选项
 */
function evershop_customize_register($wp_customize) {
    // ============================================
    // Flash Sale Countdown 设置区域
    // ============================================
    $wp_customize->add_section('evershop_countdown_section', array(
        'title'       => __('Flash Sale Countdown', 'evershop-theme'),
        'description' => __('配置全站倒计时横幅设置', 'evershop-theme'),
        'priority'    => 25,
    ));
    
    // 启用/禁用倒计时
    $wp_customize->add_setting('evershop_countdown_enabled', array(
        'default'           => true,
        'sanitize_callback' => 'evershop_sanitize_checkbox',
    ));
    
    $wp_customize->add_control('evershop_countdown_enabled', array(
        'label'       => __('启用倒计时横幅', 'evershop-theme'),
        'description' => __('显示在 Header 和 Content 之间的倒计时通告栏', 'evershop-theme'),
        'section'     => 'evershop_countdown_section',
        'type'        => 'checkbox',
    ));
    
    // 促销文本
    $wp_customize->add_setting('evershop_countdown_text', array(
        'default'           => '24-HOUR FLASH SALE ENDS TODAY',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('evershop_countdown_text', array(
        'label'       => __('促销文本', 'evershop-theme'),
        'description' => __('倒计时横幅显示的文字', 'evershop-theme'),
        'section'     => 'evershop_countdown_section',
        'type'        => 'text',
    ));
    
    // 结束时间
    $wp_customize->add_setting('evershop_countdown_end_date', array(
        'default'           => date('Y-m-d H:i:s', strtotime('+1 day')),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('evershop_countdown_end_date', array(
        'label'       => __('结束时间', 'evershop-theme'),
        'description' => __('格式: YYYY-MM-DD HH:MM:SS (例如: 2025-12-31 23:59:59)', 'evershop-theme'),
        'section'     => 'evershop_countdown_section',
        'type'        => 'text',
    ));
    
    // ============================================
    // Footer Settings 设置区域
    // ============================================
    $wp_customize->add_section('evershop_footer_section', array(
        'title'    => __('Footer Settings', 'evershop-theme'),
        'priority' => 30,
    ));
    
    $wp_customize->add_setting('evershop_copyright_text', array(
        'default'           => '© 2025 EverShop. All rights reserved.',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('evershop_copyright_text', array(
        'label'    => __('Copyright Text', 'evershop-theme'),
        'section'  => 'evershop_footer_section',
        'type'     => 'text',
    ));
    
    // 添加 Disclaimer 文本设置
    $wp_customize->add_setting('evershop_disclaimer_text', array(
        'default'           => '*STATEMENTS ON THIS SITE HAVE NOT BEEN EVALUATED BY THE FDA. THE PRODUCTS LISTED ARE NOT INTENDED TO DIAGNOSE, TREAT, CURE, OR PREVENT ANY DISEASE.',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    
    $wp_customize->add_control('evershop_disclaimer_text', array(
        'label'    => __('Disclaimer Text', 'evershop-theme'),
        'section'  => 'evershop_footer_section',
        'type'     => 'textarea',
    ));
}
add_action('customize_register', 'evershop_customize_register');

/**
 * Sanitize checkbox
 */
function evershop_sanitize_checkbox($checked) {
    return ((isset($checked) && true == $checked) ? true : false);
}

/**
 * 获取倒计时启用状态
 */
function evershop_is_countdown_enabled() {
    return get_theme_mod('evershop_countdown_enabled', true);
}

/**
 * 获取倒计时文本
 */
function evershop_get_countdown_text() {
    return get_theme_mod('evershop_countdown_text', '24-HOUR FLASH SALE ENDS TODAY');
}

/**
 * 获取倒计时结束时间
 */
function evershop_get_countdown_end_date() {
    return get_theme_mod('evershop_countdown_end_date', date('Y-m-d H:i:s', strtotime('+1 day')));
}

/**
 * 获取版权文本
 */
function evershop_get_copyright() {
    return get_theme_mod('evershop_copyright_text', '© 2025 Cutler Nutrition. All rights reserved.');
}

/**
 * 获取 Disclaimer 文本
 */
function evershop_get_disclaimer() {
    return get_theme_mod('evershop_disclaimer_text', '*STATEMENTS ON THIS SITE HAVE NOT BEEN EVALUATED BY THE FDA. THE PRODUCTS LISTED ARE NOT INTENDED TO DIAGNOSE, TREAT, CURE, OR PREVENT ANY DISEASE.');
}

/**
 * 自定义摘要长度
 */
function evershop_excerpt_length($length) {
    return 30;
}
add_filter('excerpt_length', 'evershop_excerpt_length');

/**
 * 自定义摘要结尾
 */
function evershop_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'evershop_excerpt_more');

/**
 * 添加 body class
 */
function evershop_body_classes($classes) {
    if (!is_singular()) {
        $classes[] = 'archive-page';
    }
    
    if (is_active_sidebar('sidebar-1')) {
        $classes[] = 'has-sidebar';
    } else {
        $classes[] = 'no-sidebar';
    }
    
    return $classes;
}
add_filter('body_class', 'evershop_body_classes');


/**
 * ============================================
 * 自定义 Widgets
 * ============================================
 * 
 * 加载主题自定义的 Widget 类
 * 注意：Variation Gallery 功能已移至 evershop-integration 插件
 */
require get_template_directory() . '/inc/widgets/class-flash-sale-widget.php';
require get_template_directory() . '/inc/widgets/class-hero-banner-widget.php';
require get_template_directory() . '/inc/widgets/class-product-collection-widget.php';

/**
 * 注册自定义 Widgets
 */
function evershop_register_widgets() {
    register_widget('Evershop_Flash_Sale_Widget');
    register_widget('Evershop_Hero_Banner_Widget');
    register_widget('Evershop_Product_Collection_Widget');
}
add_action('widgets_init', 'evershop_register_widgets');

/**
 * ============================================
 * 产品筛选器
 * ============================================
 */

/**
 * 在产品分类页面和商店页面添加筛选器
 * 
 * 功能包括：
 * - 库存状态筛选（In stock / Out of stock）
 * - 价格范围筛选（最小价格 - 最大价格）
 * - 产品排序
 * - 产品计数显示
 */
function evershop_product_filters() {
    if (!function_exists('is_product_category') || !function_exists('is_shop') || (!is_product_category() && !is_shop())) {
        return;
    }
    
    $current_url = add_query_arg(null, null);
    $current_url = remove_query_arg(array('min_price', 'max_price', 'stock_status', 'orderby'), $current_url);
    
    // 获取当前筛选值
    $min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : '';
    $max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : '';
    $stock_status = isset($_GET['stock_status']) ? sanitize_text_field($_GET['stock_status']) : '';
    
    // 使用 WooCommerce 标准方法获取价格范围和库存数量
    $min_price_range = 0;
    $max_price_range = 0;
    $instock_count = 0;
    $outofstock_count = 0;
    
    if (function_exists('wc_get_products')) {
        // 获取所有已发布的产品
        $all_products = wc_get_products(array(
            'status' => 'publish',
            'limit' => -1,
        ));
        
        // 计算价格范围
        $prices = array();
        foreach ($all_products as $product) {
            $price = $product->get_price();
            if ($price !== '' && $price !== null) {
                $prices[] = floatval($price);
            }
            
            // 同时统计库存状态
            if ($product->get_stock_status() === 'instock') {
                $instock_count++;
            } elseif ($product->get_stock_status() === 'outofstock') {
                $outofstock_count++;
            }
        }
        
        // 设置价格范围
        if (!empty($prices)) {
            $min_price_range = min($prices);
            $max_price_range = max($prices);
        }
    }
    
    // 创建价格范围对象以保持向后兼容
    $price_range = (object) array(
        'min_price' => $min_price_range,
        'max_price' => $max_price_range
    );
    
    // 获取产品总数
    global $wp_query;
    $total_products = $wp_query->found_posts;
    
    ?>
    <div class="facets-container">
        <div class="facets-wrapper">
            <div class="facets-left">
                <div class="facets-filters-wrapper">
                    <span class="facets-label">FILTER:</span>
                    <div class="facets-filters">
                        <!-- 库存状态筛选 -->
                        <details class="filter-group" <?php echo $stock_status ? 'open' : ''; ?>>                            <summary class="filter-summary">
                                <span>Availability</span>
                                <svg class="icon-caret" viewBox="0 0 10 6">
                                    <path d="M9.354.646a.5.5 0 00-.708 0L5 4.293 1.354.646a.5.5 0 00-.708.708l4 4a.5.5 0 00.708 0l4-4a.5.5 0 000-.708z"/>
                                </svg>
                            </summary>
                            <div class="filter-content">
                                <div class="filter-header">
                                    <span class="filter-selected"><?php echo $stock_status ? '1 selected' : '0 selected'; ?></span>
                                    <?php if ($stock_status): ?>
                                    <a href="<?php echo esc_url($current_url); ?>" class="filter-reset">Reset</a>
                                    <?php endif; ?>
                                </div>
                                <ul class="filter-list">
                                    <li class="filter-item">
                                        <label class="filter-checkbox">
                                            <input type="checkbox" 
                                                class="stock-filter-checkbox"
                                                name="stock_status" 
                                                value="instock" 
                                                <?php checked($stock_status, 'instock'); ?>>
                                            <span>IN STOCK (<?php echo $instock_count; ?>)</span>
                                        </label>
                                    </li>
                                    <li class="filter-item">
                                        <label class="filter-checkbox">
                                            <input type="checkbox" 
                                                class="stock-filter-checkbox"
                                                name="stock_status" 
                                                value="outofstock" 
                                                <?php checked($stock_status, 'outofstock'); ?>>
                                            <span>OUT OF STOCK (<?php echo $outofstock_count; ?>)</span>
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </details>
                        
                        <!-- 价格筛选 -->
                        <details class="filter-group" <?php echo ($min_price || $max_price) ? 'open' : ''; ?>>
                            <summary class="filter-summary">
                                <span>Price</span>
                                <svg class="icon-caret" viewBox="0 0 10 6">
                                    <path d="M9.354.646a.5.5 0 00-.708 0L5 4.293 1.354.646a.5.5 0 00-.708.708l4 4a.5.5 0 00.708 0l4-4a.5.5 0 000-.708z"/>
                                </svg>
                            </summary>
                            <div class="filter-content">
                                <div class="filter-header">
                                    <span class="filter-selected"><?php echo ($min_price || $max_price) ? '1 selected' : '0 selected'; ?></span>
                                    <?php if ($min_price || $max_price): ?>
                                    <a href="<?php echo esc_url($current_url); ?>" class="filter-reset">Reset</a>
                                    <?php endif; ?>
                                </div>
                                <div class="price-filter">
                                    <div class="price-inputs">
                                        <div class="price-field">
                                            <label for="min_price">From</label>
                                            <input type="number" 
                                                id="min_price" 
                                                name="min_price" 
                                                min="<?php echo floor($price_range->min_price); ?>" 
                                                max="<?php echo ceil($price_range->max_price); ?>" 
                                                step="1"
                                                value="<?php echo $min_price; ?>"
                                                placeholder="£<?php echo floor($price_range->min_price); ?>">
                                        </div>
                                        <div class="price-field">
                                            <label for="max_price">To</label>
                                            <input type="number" 
                                                id="max_price" 
                                                name="max_price" 
                                                min="<?php echo floor($price_range->min_price); ?>" 
                                                max="<?php echo ceil($price_range->max_price); ?>" 
                                                step="1"
                                                value="<?php echo $max_price; ?>"
                                                placeholder="£<?php echo ceil($price_range->max_price); ?>">
                                        </div>
                                    </div>
                                    <p class="price-range-label">
                                        The highest price is £<?php echo number_format($price_range->max_price, 2); ?>
                                    </p>
                                </div>
                            </div>
                        </details>
                    </div>
                </div>
            </div>
            
            <div class="facets-right">
                <div class="facets-sort-wrapper">
                    <label for="orderby" class="sort-label">SORT BY:</label>
                    <?php woocommerce_catalog_ordering(); ?>
                </div>
                <div class="product-count-wrapper">
                    <span class="product-count-number"><?php echo $total_products; ?> PRODUCTS</span>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterGroups = document.querySelectorAll('.filter-group');
        const form = document.querySelector('.woocommerce-filters-form');
        
        // 在表单提交前清理空的价格字段
        if (form) {
            form.addEventListener('submit', function(e) {
                const minPriceInput = form.querySelector('input[name="min_price"]');
                const maxPriceInput = form.querySelector('input[name="max_price"]');
                
                // 如果价格输入为空，移除 name 属性，这样就不会提交到 URL
                if (minPriceInput && (!minPriceInput.value || minPriceInput.value.trim() === '')) {
                    minPriceInput.removeAttribute('name');
                }
                if (maxPriceInput && (!maxPriceInput.value || maxPriceInput.value.trim() === '')) {
                    maxPriceInput.removeAttribute('name');
                }
            });
        }
        
        // 点击外部区域关闭所有打开的 details
        document.addEventListener('click', function(event) {
            filterGroups.forEach(function(details) {
                // 检查点击是否在 details 元素外部
                if (!details.contains(event.target) && details.hasAttribute('open')) {
                    details.removeAttribute('open');
                }
            });
        });
        
        // 阻止点击 details 内部时的事件冒泡（防止立即关闭）
        filterGroups.forEach(function(details) {
            details.addEventListener('click', function(event) {
                event.stopPropagation();
            });
        });
        
        // 处理库存状态筛选的 checkbox 点击
        const stockCheckboxes = document.querySelectorAll('.stock-filter-checkbox');
        stockCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                // 取消选中其他 checkbox
                stockCheckboxes.forEach(function(cb) {
                    if (cb !== checkbox) {
                        cb.checked = false;
                    }
                });
                
                // 如果当前 checkbox 被选中，提交表单
                if (this.checked) {
                    form.submit();
                } else {
                    // 如果取消选中，移除 stock_status 参数并重新加载
                    const url = new URL(window.location.href);
                    url.searchParams.delete('stock_status');
                    window.location.href = url.toString();
                }
            });
        });
    });
    </script>
    <?php
}

/**
 * 应用产品筛选条件到主查询
 * 
 * 通过 pre_get_posts 钩子修改产品查询，支持：
 * - 价格范围筛选（min_price, max_price）
 * - 库存状态筛选（stock_status）- 使用 WooCommerce 标准方法
 * - 默认只显示有货产品
 */
function evershop_apply_product_filters($query) {
    if (!is_admin() && $query->is_main_query() && function_exists('is_product_category') && function_exists('is_shop') && (is_product_category() || is_shop())) {
        $meta_query = $query->get('meta_query') ?: array();
        
        // 价格筛选：最小价格
        if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
            $meta_query[] = array(
                'key' => '_price',
                'value' => floatval($_GET['min_price']),
                'compare' => '>=',
                'type' => 'DECIMAL(10,2)'
            );
        }
        
        // 价格筛选：最大价格
        if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
            $meta_query[] = array(
                'key' => '_price',
                'value' => floatval($_GET['max_price']),
                'compare' => '<=',
                'type' => 'DECIMAL(10,2)'
            );
        }
        
        // 库存状态筛选：使用 WooCommerce 标准方法
        // 如果用户选择了状态，使用选择的状态；否则默认只显示有货产品
        $stock_status = isset($_GET['stock_status']) && !empty($_GET['stock_status']) 
            ? sanitize_text_field($_GET['stock_status']) 
            : 'instock';
        
        $meta_query[] = array(
            'key' => '_stock_status',
            'value' => $stock_status,
            'compare' => '='
        );
        
        if (!empty($meta_query)) {
            $meta_query['relation'] = 'AND';
            $query->set('meta_query', $meta_query);
        }
    }
}
add_action('pre_get_posts', 'evershop_apply_product_filters');

/**
 * 在产品列表前输出筛选器表单
 */
function evershop_output_filter_form() {
    if (!function_exists('is_product_category') || !function_exists('is_shop') || (!is_product_category() && !is_shop())) {
        return;
    }
    
    echo '<form class="woocommerce-filters-form" method="get" action="">';
    evershop_product_filters();
    echo '</form>';
}
add_action('woocommerce_before_shop_loop', 'evershop_output_filter_form', 15);

/**
 * 移除 WooCommerce 默认的产品计数和排序显示
 * 
 * 使用自定义的筛选器界面替代，已在 evershop_product_filters() 中实现
 */
function evershop_remove_default_sorting() {
    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
}
add_action('init', 'evershop_remove_default_sorting');

/**
 * 变体表单处理说明
 * 
 * 不移除 woocommerce_variable_add_to_cart action，保持 WooCommerce 默认的变体表单加载
 * 通过主题模板覆盖（/woocommerce/single-product/add-to-cart/variable.php）和 CSS 来自定义显示样式
 * 
 * 如果移除此 action，整个 variable.php 模板将不会加载，导致变体产品无法正常工作
 */

/**
 * 隐藏 WooCommerce 默认的变体选择下拉框和相关元素
 * 
 * 通过 CSS 隐藏：
 * - table.variations（默认的下拉框变体选择器）
 * - .reset_variations（Clear 按钮）
 * - .woocommerce-variation.single_variation（单个变体信息显示区域）
 * 
 * 保持显示：
 * - .single_variation_wrap、.woocommerce-variation-add-to-cart、.evershop-variation-add-to-cart
 *   （自定义的 Add to Cart 容器，通过模板覆盖实现）
 */
function evershop_hide_default_variation_table() {
    ?>
    <style>
        /* 隐藏 WooCommerce 默认的变体选择下拉框 */
        .single-product table.variations {
            display: none !important;
        }
        
        /* 隐藏 "Clear" 按钮 */
        .single-product .reset_variations {
            display: none !important;
        }
        
        /* 确保自定义的 Add to Cart 容器显示 */
        .single-product .single_variation_wrap,
        .single-product .woocommerce-variation-add-to-cart,
        .single-product .evershop-variation-add-to-cart {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        /* 隐藏默认的单个变体信息显示区域（价格等） */
        .single-product .woocommerce-variation.single_variation {
            display: none !important;
        }
    </style>
    <?php
}
add_action('wp_head', 'evershop_hide_default_variation_table');

/**
 * ============================================
 * EverShop 变体产品系统 - 独立 URL 支持
 * ============================================
 * 
 * 修改 product_variation 的 post type 设置，使其支持：
 * - 公开访问和独立 URL
 * - 友好的 URL slug（父产品-slug-属性值）
 * - 前端独立展示
 * ============================================
 */

/**
 * 修改 product_variation 的 post type 设置
 * 启用公开访问、独立 URL 和 rewrite 规则
 */
function evershop_register_variation_post_type() {
    global $wp_post_types;
    
    // 检查 product_variation 是否已注册
    if (!isset($wp_post_types['product_variation'])) {
        return;
    }
    
    // 修改 product_variation 的设置
    $wp_post_types['product_variation']->public = true;
    $wp_post_types['product_variation']->publicly_queryable = true;
    $wp_post_types['product_variation']->show_ui = true;
    $wp_post_types['product_variation']->show_in_menu = false; // 不单独显示菜单
    $wp_post_types['product_variation']->show_in_nav_menus = true;
    $wp_post_types['product_variation']->show_in_admin_bar = true;
    $wp_post_types['product_variation']->has_archive = false;
    
    // ⚡ 关键：启用 rewrite
    $wp_post_types['product_variation']->rewrite = array(
        'slug' => 'product',
        'with_front' => false,
        'feeds' => false,
        'pages' => true
    );
}
add_action('init', 'evershop_register_variation_post_type', 999);

/**
 * 为 product_variation 生成友好的 URL slug
 * 格式: 父产品-slug-属性值
 */
function evershop_set_variation_permalink($post_id, $post, $update) {
    // 只处理 product_variation
    if ($post->post_type !== 'product_variation') {
        return;
    }
    
    // 避免无限循环
    if (defined('EVERSHOP_SETTING_VARIATION_SLUG')) {
        return;
    }
    define('EVERSHOP_SETTING_VARIATION_SLUG', true);
    
    $variation = wc_get_product($post_id);
    if (!$variation) {
        return;
    }
    
    $parent_id = $variation->get_parent_id();
    $parent = wc_get_product($parent_id);
    
    if (!$parent) {
        return;
    }
    
    // 构建 slug: 父产品-slug-属性值
    $parent_slug = $parent->get_slug();
    $attributes = $variation->get_variation_attributes();
    $attribute_slugs = array_map('sanitize_title', array_values($attributes));
    
    $variation_slug = $parent_slug . '-' . implode('-', $attribute_slugs);
    
    // 更新 post_name
    if ($post->post_name !== $variation_slug) {
        wp_update_post(array(
            'ID' => $post_id,
            'post_name' => $variation_slug
        ));
    }
}
add_action('save_post_product_variation', 'evershop_set_variation_permalink', 10, 3);

/**
 * 确保变体产品有独立的 permalink
 */
function evershop_variation_permalink($permalink, $post) {
    if ($post->post_type === 'product_variation') {
        // 变体产品使用自己的 permalink
        $variation = wc_get_product($post->ID);
        if ($variation) {
            $parent_id = $variation->get_parent_id();
            $parent = wc_get_product($parent_id);
            
            if ($parent) {
                // 格式: /product/父产品-slug-属性值/
                $parent_slug = $parent->get_slug();
                $attributes = $variation->get_variation_attributes();
                $attribute_slugs = array_map('sanitize_title', array_values($attributes));
                
                $variation_slug = $parent_slug . '-' . implode('-', $attribute_slugs);
                return home_url('/product/' . $variation_slug . '/');
            }
        }
    }
    return $permalink;
}
add_filter('post_type_link', 'evershop_variation_permalink', 10, 2);

/**
 * 刷新 rewrite 规则（仅在激活主题时执行一次）
 */
function evershop_flush_rewrite_rules_on_activation() {
    evershop_register_variation_post_type();
    flush_rewrite_rules();
    
    // 设置标记，提示用户
    set_transient('evershop_variations_enabled', true, 60);
}
add_action('after_switch_theme', 'evershop_flush_rewrite_rules_on_activation');

/**
 * 显示变体功能启用通知
 */
function evershop_show_variations_enabled_notice() {
    if (get_transient('evershop_variations_enabled')) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <strong>✅ EverShop 变体系统已启用！</strong><br>
                变体产品（Product Variations）现在会显示在产品列表中，并拥有独立的 URL。<br>
                <strong>重要：</strong>请访问 
                <a href="<?php echo admin_url('options-permalink.php'); ?>">设置 → 固定链接</a> 
                并点击"保存更改"以刷新 URL 规则。
            </p>
        </div>
        <?php
        delete_transient('evershop_variations_enabled');
    }
}
add_action('admin_notices', 'evershop_show_variations_enabled_notice');

/**
 * 为变体产品添加"查看"链接
 */
function evershop_add_variation_view_link($actions, $post) {
    if ($post->post_type === 'product_variation') {
        $permalink = get_permalink($post->ID);
        $actions['view'] = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url($permalink),
            __('View', 'evershop-theme')
        );
    }
    
    return $actions;
}
add_filter('post_row_actions', 'evershop_add_variation_view_link', 10, 2);

/**
 * ============================================
 * 变体属性选择器
 * ============================================
 * 
 * 在 WooCommerce Variations 面板添加属性选择器，功能：
 * - 显示所有可用于变体的属性
 * - 用户选择哪些属性用于生成变体
 * - 保存选择到 post meta（_evershop_variant_attributes）
 * - 前端读取数据以正确高亮选中的变体选项
 */

/**
 * 在 Variations 面板顶部添加属性选择器 UI
 */
function evershop_add_variation_attribute_selector() {
    global $post, $product_object;
    
    if (!$product_object) {
        $product_object = wc_get_product($post->ID);
    }
    
    if (!$product_object || !$product_object->is_type('variable')) {
        return;
    }
    
    // 获取所有用于变体的属性
    $attributes = $product_object->get_attributes();
    $variation_attributes = array_filter($attributes, function($attribute) {
        return $attribute->get_variation();
    });
    
    
    // 获取已选择的变体属性
    $selected_attributes = get_post_meta($post->ID, '_evershop_variant_attributes', true);
    if (!is_array($selected_attributes)) {
        $selected_attributes = array();
    }
    
    // 如果还没有选择过，默认全选
    if (empty($selected_attributes)) {
        $selected_attributes = array_keys($variation_attributes);
    }
    
    ?>
    <div class="evershop-variant-selector" style="margin-bottom: 15px;">
        
        <div class="evershop-attribute-list" style="margin-bottom: 15px;">
            <?php foreach ($variation_attributes as $attribute) :
                $attribute_name = $attribute->get_name();
                $attribute_label = wc_attribute_label($attribute_name);
                $is_taxonomy = $attribute->is_taxonomy();
                $checked = in_array($attribute_name, $selected_attributes);
                
                // 获取属性选项
                if ($is_taxonomy) {
                    $terms = $attribute->get_terms();
                    $options = array();
                    foreach ($terms as $term) {
                        $options[] = $term->name;
                    }
                } else {
                    $options = $attribute->get_options();
                }
                
                $option_count = count($options);
                ?>
                <label class="evershop-attribute-item" style="display: flex; align-items: start; gap: 12px; padding: 12px 15px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px; cursor: pointer; transition: all 0.2s; background-color: <?php echo $checked ? '#eff6ff' : '#fff'; ?>; border-color: <?php echo $checked ? '#2271b1' : '#ddd'; ?>;">
                    <input 
                        type="checkbox" 
                        name="evershop_variant_attributes[]" 
                        value="<?php echo esc_attr($attribute_name); ?>"
                        class="evershop-attribute-checkbox"
                        style="margin-top: 3px;"
                        <?php checked($checked); ?>
                    />
                    <div style="flex: 1;">
                        <strong style="display: block; margin-bottom: 5px; color: #1d2939; font-size: 13px;">
                            <?php echo esc_html($attribute_label); ?>
                        </strong>
                        <span style="font-size: 12px; color: #6b7280;">
                            <?php echo sprintf(__('%d options: %s', 'evershop-theme'), $option_count, esc_html(implode(', ', array_slice($options, 0, 5)))); ?>
                            <?php if ($option_count > 5) : ?>
                                <span style="color: #2271b1;">...and <?php echo ($option_count - 5); ?> more</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>
        
        <div style="display: flex; gap: 10px; align-items: center; padding: 12px; background-color: #f9fafb; border-radius: 4px;">
            <button 
                type="button" 
                id="evershop-save-variant-attributes" 
                class="button button-primary"
                style="display: flex; align-items: center; gap: 5px;">
                <span class="dashicons dashicons-saved" style="margin-top: 3px;"></span>
                保存属性选择
            </button>
            <span id="evershop-attribute-status" style="font-size: 13px; color: #6b7280;"></span>
        </div>
    </div>
    
    <style>
        .evershop-attribute-item:hover {
            background-color: #f9fafb !important;
            border-color: #9ca3af !important;
        }
        .evershop-attribute-item:has(input:checked) {
            background-color: #eff6ff !important;
            border-color: #2271b1 !important;
        }
        .evershop-attribute-item:has(input:checked) strong {
            color: #2271b1 !important;
        }
    </style>
    
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        var $selector = $('.evershop-variant-selector');
        var $button = $('#evershop-save-variant-attributes');
        var $status = $('#evershop-attribute-status');
        var $checkboxes = $('.evershop-attribute-checkbox');
        
        // 初始化显示
        updateStatus();
        
        // 复选框变化时更新状态
        $checkboxes.on('change', function() {
            updateStatus();
        });
        
        // 保存按钮点击事件
        $button.on('click', function(e) {
            e.preventDefault();
            saveAttributes();
        });
        
        // 更新状态显示
        function updateStatus() {
            var count = $checkboxes.filter(':checked').length;
            var total = $checkboxes.length;
            
            if (count > 0) {
                $status.html('<span style="color: #1d4ed8;">已选择 ' + count + ' / ' + total + ' 个属性</span>');
            } else {
                $status.html('<span style="color: #dc3232;">⚠️ 至少选择一个属性</span>');
            }
        }
        
        // 保存属性选择
        function saveAttributes() {
            var selectedAttributes = [];
            $checkboxes.filter(':checked').each(function() {
                selectedAttributes.push($(this).val());
            });
            
            if (selectedAttributes.length === 0) {
                $status.html('<span style="color: #dc3232;">⚠️ 请至少选择一个属性</span>');
                return;
            }
            
            $button.prop('disabled', true).text('保存中...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'evershop_save_variant_attributes',
                    post_id: <?php echo $post->ID; ?>,
                    attributes: selectedAttributes,
                    nonce: '<?php echo wp_create_nonce('evershop_variant_attributes'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $status.html('<span style="color: #46b450;">✓ 已保存 (' + selectedAttributes.length + ' 个属性)</span>');
                        
                        // 3秒后恢复状态
                        setTimeout(function() {
                            updateStatus();
                        }, 3000);
                    } else {
                        $status.html('<span style="color: #dc3232;">❌ 保存失败：' + (response.data?.message || '未知错误') + '</span>');
                    }
                },
                error: function(xhr, status, error) {
                    $status.html('<span style="color: #dc3232;">❌ 保存失败：' + error + '</span>');
                },
                complete: function() {
                    $button.prop('disabled', false).html('<span class="dashicons dashicons-saved" style="margin-top: 3px;"></span> 保存属性选择');
                }
            });
        }
    });
    </script>
    <?php
}
add_action('woocommerce_variable_product_before_variations', 'evershop_add_variation_attribute_selector', 5);

/**
 * AJAX 处理：保存选中的变体属性
 * 
 * 接收参数：
 * - post_id: 产品 ID
 * - attributes: 选中的属性数组
 * - nonce: 安全验证
 */
function evershop_save_variant_attributes_ajax() {
    check_ajax_referer('evershop_variant_attributes', 'nonce');
    
    if (!current_user_can('edit_products')) {
        wp_send_json_error(array('message' => 'Permission denied'));
    }
    
    $post_id = intval($_POST['post_id']);
    $attributes = isset($_POST['attributes']) ? $_POST['attributes'] : array();
    
    // 验证
    if (empty($attributes)) {
        wp_send_json_error(array('message' => '至少选择一个属性'));
    }
    
    // 保存到 post meta
    update_post_meta($post_id, '_evershop_variant_attributes', $attributes);
    
    wp_send_json_success(array(
        'message' => 'Attributes saved successfully',
        'count' => count($attributes),
        'attributes' => $attributes
    ));
}
add_action('wp_ajax_evershop_save_variant_attributes', 'evershop_save_variant_attributes_ajax');

/**
 * 在产品保存时自动同步变体属性到所有 variations
 * 这样前端可以读取并正确高亮选中的变体选项
 */
function evershop_sync_variant_attributes_to_variations($post_id) {
    // 只处理产品
    if (get_post_type($post_id) !== 'product') {
        return;
    }
    
    $product = wc_get_product($post_id);
    if (!$product || !$product->is_type('variable')) {
        return;
    }
    
    // 获取选中的变体属性
    $variant_attributes = get_post_meta($post_id, '_evershop_variant_attributes', true);
    if (!is_array($variant_attributes) || empty($variant_attributes)) {
        return;
    }
    
    // 保存到每个 variation
    $variations = $product->get_children();
    foreach ($variations as $variation_id) {
        update_post_meta($variation_id, '_evershop_variant_attributes', $variant_attributes);
        update_post_meta($variation_id, '_evershop_parent_product_id', $post_id);
    }
}
add_action('woocommerce_update_product', 'evershop_sync_variant_attributes_to_variations', 20, 1);

/**
 * ============================================
 * WordPress 后台列表每页显示数量设置
 * ============================================
 */

/**
 * 修改文章列表默认每页显示数量为 50
 */
function evershop_set_post_per_page($per_page) {
    // 获取用户选择的每页数量（通过 user meta 存储）
    $user_id = get_current_user_id();
    
    // 获取当前文章类型
    global $typenow;
    $post_type = $typenow ? $typenow : 'post';
    
    // 构建 user meta key
    $meta_key = 'edit_' . $post_type . '_per_page';
    
    // 获取用户自定义的每页数量
    $user_per_page = get_user_meta($user_id, $meta_key, true);
    
    if ($user_per_page && is_numeric($user_per_page)) {
        return intval($user_per_page);
    }
    
    // 默认显示 50 条
    return 50;
}
add_filter('edit_posts_per_page', 'evershop_set_post_per_page', 10, 1);
add_filter('edit_pages_per_page', 'evershop_set_post_per_page', 10, 1);
add_filter('edit_product_per_page', 'evershop_set_post_per_page', 10, 1);

/**
 * 在列表页面顶部添加每页显示数量选择器（明显位置）
 */
function evershop_add_per_page_selector() {
    $screen = get_current_screen();
    
    // 只在列表页面显示
    if (!$screen || $screen->base !== 'edit') {
        return;
    }
    
    // 获取当前每页数量
    $user_id = get_current_user_id();
    $post_type = $screen->post_type;
    
    // 构建 user meta key
    $meta_key = 'edit_' . $post_type . '_per_page';
    $current_per_page = get_user_meta($user_id, $meta_key, true);
    
    if (!$current_per_page || !is_numeric($current_per_page)) {
        $current_per_page = 50;
    }
    
    $post_type_object = get_post_type_object($post_type);
    $post_type_label = $post_type_object ? $post_type_object->labels->name : 'Items';
    
    ?>
    <style>
        /* 与 WordPress 后台 bulk-action-selector 样式保持一致 */
        .tablenav .evershop-per-page-selector {
            float: left;
            margin: 1px 8px 0 0;
        }
        
        .evershop-per-page-selector label {
            display: inline-block;
            vertical-align: middle;
            margin-right: 5px;
            font-size: 13px;
            line-height: 2.15384615;
        }
        
        .evershop-per-page-selector select {
            display: inline-block;
            vertical-align: middle;
            height: 32px;
            line-height: 2.15384615;
            padding: 0 24px 0 8px;
            min-width: 80px;
            font-size: 14px;
            color: #2c3338;
            border: 1px solid #8c8f94;
            border-radius: 3px;
            background-color: #fff;
            background-image: url("data:image/svg+xml,%3Csvg width='20' height='20' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5 6l5 5 5-5 2 1-7 7-7-7 2-1z' fill='%23555d66'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 5px top 55%;
            background-size: 16px 16px;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
        
        .evershop-per-page-selector select:focus {
            border-color: #2271b1;
            outline: 2px solid transparent;
            box-shadow: 0 0 0 1px #2271b1;
        }
        
        .evershop-per-page-selector select:hover {
            border-color: #2271b1;
        }
        
        /* 移动端适配 */
        @media screen and (max-width: 782px) {
            .tablenav .evershop-per-page-selector {
                float: none;
                margin: 10px 0;
                display: block;
            }
            
            .evershop-per-page-selector label,
            .evershop-per-page-selector select {
                display: block;
                width: 100%;
            }
            
            .evershop-per-page-selector label {
                margin-bottom: 5px;
            }
            
            .evershop-per-page-selector select {
                height: 40px;
                font-size: 16px;
            }
        }
    </style>
    <div class="evershop-per-page-selector">
        <label for="evershop-per-page" class="screen-reader-text">每页显示数量</label>
        <label for="evershop-per-page" aria-hidden="true">每页显示：</label>
        <select id="evershop-per-page" name="evershop_per_page">
            <option value="10" <?php selected($current_per_page, 10); ?>>10 条</option>
            <option value="20" <?php selected($current_per_page, 20); ?>>20 条</option>
            <option value="50" <?php selected($current_per_page, 50); ?>>50 条</option>
            <option value="100" <?php selected($current_per_page, 100); ?>>100 条</option>
            <option value="200" <?php selected($current_per_page, 200); ?>>200 条</option>
        </select>
    </div>
    <script>
    jQuery(document).ready(function($) {
        $('#evershop-per-page').on('change', function() {
            var perPage = $(this).val();
            var url = new URL(window.location.href);
            url.searchParams.set('evershop_per_page', perPage);
            window.location.href = url.toString();
        });
    });
    </script>
    <?php
}
add_action('manage_posts_extra_tablenav', 'evershop_add_per_page_selector');

/**
 * 处理每页显示数量的更新
 */
function evershop_handle_per_page_update() {
    if (isset($_GET['evershop_per_page']) && is_numeric($_GET['evershop_per_page'])) {
        $user_id = get_current_user_id();
        $screen = get_current_screen();
        
        if ($screen && $screen->base === 'edit') {
            $post_type = $screen->post_type;
            
            // 构建 user meta key
            $meta_key = 'edit_' . $post_type . '_per_page';
            $new_per_page = intval($_GET['evershop_per_page']);
            
            // 保存用户选择
            update_user_meta($user_id, $meta_key, $new_per_page);
            
            // 重定向到清理 URL
            $redirect_url = remove_query_arg('evershop_per_page');
            wp_redirect($redirect_url);
            exit;
        }
    }
}
add_action('admin_init', 'evershop_handle_per_page_update');

/**
 * ============================================
 * 自定义购物车通知系统
 * ============================================
 * 
 * 禁用 WooCommerce 默认的通知消息，使用主题自定义的 mini-cart 通知
 * 提供更好的用户体验和视觉一致性
 */

/**
 * 禁用添加到购物车的默认成功消息
 */
add_filter('wc_add_to_cart_message_html', '__return_empty_string');

/**
 * 隐藏所有 WooCommerce 默认通知消息
 */
function evershop_hide_woocommerce_messages() {
    ?>
    <style>
        .woocommerce-message,
        .woocommerce-info,
        .woocommerce-error {
            display: none !important;
        }
    </style>
    <?php
}
add_action('wp_head', 'evershop_hide_woocommerce_messages', 999);

/**
 * 修复 WooCommerce Admin 设置向导错误
 * 
 * 解决 core-profiler.js "Cannot read properties of undefined (reading 'title')" 错误
 * 通过禁用 onboarding 功能和标记设置向导为已完成
 */
function evershop_fix_wc_admin_profiler() {
    // 禁用 WooCommerce Admin 的 Onboarding 功能
    add_filter('woocommerce_admin_features', function($features) {
        return array_diff($features, ['onboarding']);
    }, 99);
    
    // 在访问 WooCommerce Admin 页面时，标记设置向导为已完成
    if (isset($_GET['page']) && $_GET['page'] === 'wc-admin') {
        update_option('woocommerce_onboarding_profile', array(
            'completed' => true,
            'skipped' => true
        ));
        update_option('woocommerce_task_list_complete', 'yes');
        update_option('woocommerce_task_list_hidden', 'yes');
        update_option('woocommerce_extended_task_list_hidden', 'yes');
    }
}
add_action('admin_init', 'evershop_fix_wc_admin_profiler', 1);

/**
 * 产品搜索使用自定义模板
 * 
 * 当用户搜索产品时，使用 search-product.php 模板替代默认的搜索模板
 * 提供更好的产品搜索结果展示
 */
function evershop_search_product_template($template) {
    if (is_search() && get_query_var('post_type') === 'product') {
        $search_product_template = locate_template('search-product.php');
        
        // 开发调试：管理员可通过 ?debug 参数查看模板加载情况
        if (current_user_can('manage_options') && isset($_GET['debug'])) {
            error_log('Search Product Template: ' . ($search_product_template ? 'Found' : 'Not Found'));
            error_log('Template Path: ' . $search_product_template);
        }
        
        if ($search_product_template) {
            return $search_product_template;
        }
    }
    
    return $template;
}
add_filter('template_include', 'evershop_search_product_template', 999);

/**
 * 将 WooCommerce Shop 页面重定向到主页
 * 
 * 使用主页作为产品展示页，不使用独立的 /shop/ 页面
 * 301 永久重定向，对 SEO 友好
 */
function evershop_redirect_shop_to_home() {
    if (function_exists('is_shop') && is_shop() && !is_search()) {
        wp_redirect(home_url('/'), 301);
        exit;
    }
}
add_action('template_redirect', 'evershop_redirect_shop_to_home', 1);

/**
 * 加载 Checkout 调试工具（开发环境使用）
 * 
 * 如果存在 debug-checkout.php 文件，则加载用于调试 Checkout 流程
 */
if (file_exists(get_template_directory() . '/debug-checkout.php')) {
    require_once get_template_directory() . '/debug-checkout.php';
}

/**
 * ============================================
 * 强制 Checkout 页面使用 Classic Checkout
 * ============================================
 * 
 * 禁用 WooCommerce Blocks Checkout，强制使用传统的 Classic Checkout
 * 原因：主题的自定义 Checkout 模板基于 Classic Checkout 开发
 */

/**
 * 设置新创建的 Checkout 页面默认内容为 Classic Checkout shortcode
 */
add_filter('woocommerce_create_pages', function($pages) {
    if (isset($pages['checkout'])) {
        $pages['checkout']['content'] = '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->';
    }
    return $pages;
});

/**
 * 检测并替换 Checkout 页面的 Block 内容为 Classic Checkout
 */
add_action('init', function() {
    if (function_exists('wc_get_page_id')) {
        $checkout_page_id = wc_get_page_id('checkout');
        if ($checkout_page_id) {
            $post = get_post($checkout_page_id);
            if ($post && has_block('woocommerce/checkout', $post)) {
                add_action('template_redirect', function() use ($checkout_page_id) {
                    if (function_exists('is_checkout') && function_exists('is_wc_endpoint_url') && is_checkout() && !is_wc_endpoint_url()) {
                        add_filter('the_content', function($content) {
                            if (function_exists('is_checkout') && is_checkout() && in_the_loop() && is_main_query()) {
                                return do_shortcode('[woocommerce_checkout]');
                            }
                            return $content;
                        }, 999);
                    }
                });
            }
        }
    }
}, 20);

/**
 * 确保使用主题的自定义 Checkout 模板
 */
add_filter('woocommerce_locate_template', function($template, $template_name, $template_path) {
    if ($template_name === 'checkout/form-checkout.php') {
        $custom_template = get_stylesheet_directory() . '/woocommerce/checkout/form-checkout.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}, 10, 3);

/**
 * 强制 Checkout 页面输出 Classic Checkout 表单
 * 如果页面内容包含 Checkout Block，则替换为 Classic shortcode
 * 注意：只针对 Checkout 页面，不影响其他页面（如购物车、我的账户等）
 */
add_action('wp', function() {
    if (function_exists('is_checkout') && function_exists('is_wc_endpoint_url') && is_checkout() && !is_wc_endpoint_url()) {
        remove_filter('the_content', 'wpautop');
        add_filter('the_content', function($content) {
            if (function_exists('is_checkout') && is_checkout() && in_the_loop() && is_main_query()) {
                // 只替换 Checkout Block，不影响其他 WooCommerce blocks
                if (has_block('woocommerce/checkout', $content) || strpos($content, 'wp-block-woocommerce-checkout') !== false) {
                    ob_start();
                    echo do_shortcode('[woocommerce_checkout]');
                    return ob_get_clean();
                }
            }
            return $content;
        }, 9);
    }
}, 50);

/**
 * ============================================
 * My Account 页面自定义
 * ============================================
 */

/**
 * 从 My Account 菜单移除 "Downloads" 选项
 * 
 * 如果不提供数字下载产品，可移除此菜单项简化界面
 */
function evershop_remove_downloads_endpoint( $items ) {
    if ( isset( $items['downloads'] ) ) {
        unset( $items['downloads'] );
    }
    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'evershop_remove_downloads_endpoint' );

/**
 * 自定义订单日期列显示格式
 * 
 * 显示格式：
 * - 第一行：日期（例如：23 Nov 2025）
 * - 第二行：时间（例如：11:51）
 */
function evershop_add_time_to_orders_date_column( $order ) {
    $order_timestamp = $order->get_date_created();
    if ( ! $order_timestamp ) {
        return;
    }
    
    $date = $order_timestamp->date_i18n( 'j M Y' );
    $time = $order_timestamp->date_i18n( 'H:i' );
    
    echo '<div style="line-height: 1.5;">';
    echo '<time datetime="' . esc_attr( $order_timestamp->date( 'c' ) ) . '" style="display: block; font-weight: 500; color: #ffffff;">';
    echo esc_html( $date );
    echo '</time>';
    echo '<span style="display: block; font-size: 0.875rem; color: rgba(255, 255, 255, 0.6); margin-top: 2px;">';
    echo esc_html( $time );
    echo '</span>';
    echo '</div>';
}
add_action( 'woocommerce_my_account_my_orders_column_order-date', 'evershop_add_time_to_orders_date_column' );

/**
 * 自定义订单总价列显示格式
 * 
 * 显示格式：
 * - 第一行：总金额（加粗显示）
 * - 第二行：商品数量（例如：3 items）
 */
function evershop_format_order_total_column( $order ) {
    $total = $order->get_formatted_order_total();
    $item_count = $order->get_item_count();
    
    echo '<div style="line-height: 1.5;">';
    echo '<span style="display: block; font-weight: 600; color: #ffffff; font-size: 1rem;">';
    echo wp_kses_post( $total );
    echo '</span>';
    echo '<span style="display: block; font-size: 0.875rem; color: rgba(255, 255, 255, 0.6); margin-top: 2px;">';
    echo sprintf( _n( '%s item', '%s items', $item_count, 'evershop-theme' ), number_format_i18n( $item_count ) );
    echo '</span>';
    echo '</div>';
}
add_action( 'woocommerce_my_account_my_orders_column_order-total', 'evershop_format_order_total_column' );

/**
 * 过滤订单操作按钮，只保留 "View" 按钮
 * 
 * 移除其他操作按钮（如 Pay、Cancel 等），简化界面
 */
function evershop_filter_my_account_orders_actions( $actions, $order ) {
    $new_actions = array();
    
    if ( isset( $actions['view'] ) ) {
        $new_actions['view'] = $actions['view'];
    }
    
    return $new_actions;
}
add_filter( 'woocommerce_my_account_my_orders_actions', 'evershop_filter_my_account_orders_actions', 10, 2 );
