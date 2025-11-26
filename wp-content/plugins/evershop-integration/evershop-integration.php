<?php
/**
 * Plugin Name: EdgeX Content Builder
 * Plugin URI: https://github.com/dominikwei9898/EdgeX
 * Description: 为 EdgeX 主题和 WooCommerce 产品页面添加灵活的内容模块构建器。支持图文模块、视频轮播、客户评价、关键优势等多种内容块，无需第三方插件即可使用。
 * Version: 2.1.1
 * Author: Dominik Wei
 * Author URI: https://github.com/dominikwei9898
 * License: MIT
 * Text Domain: edgex-content-builder
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * WC requires at least: 8.0
 * WC tested up to: 8.5
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('EVERSHOP_CONTENT_BUILDER_VERSION', '2.1.1');
define('EVERSHOP_CONTENT_BUILDER_DIR', plugin_dir_path(__FILE__));
define('EVERSHOP_CONTENT_BUILDER_URL', plugin_dir_url(__FILE__));

// 兼容性常量（向后兼容）
define('EVERSHOP_INTEGRATION_VERSION', '2.0.0');
define('EVERSHOP_INTEGRATION_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EVERSHOP_INTEGRATION_PLUGIN_URL', plugin_dir_url(__FILE__));

// 定义默认配置
define('EVERSHOP_DEFAULT_BADGE_COLOR', '#ef4444');
define('EVERSHOP_CACHE_EXPIRATION', 5 * MINUTE_IN_SECONDS);

/**
 * 检查 WooCommerce 是否激活
 */
function evershop_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p><strong>EverShop Integration</strong> requires WooCommerce to be installed and activated.</p></div>';
        });
        return false;
    }
    return true;
}

/**
 * 插件激活钩子
 */
register_activation_hook(__FILE__, 'evershop_integration_activate');
function evershop_integration_activate() {
    // 刷新重写规则
    flush_rewrite_rules();
}

/**
 * 插件停用钩子
 */
register_deactivation_hook(__FILE__, 'evershop_integration_deactivate');
function evershop_integration_deactivate() {
    flush_rewrite_rules();
}

/**
 * 加载插件文本域
 */
add_action('plugins_loaded', 'evershop_integration_load_textdomain');
function evershop_integration_load_textdomain() {
    load_plugin_textdomain('evershop-integration', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

/**
 * 初始化插件
 */
add_action('plugins_loaded', 'evershop_integration_init', 11);
function evershop_integration_init() {
    // 检查 WooCommerce
    if (!evershop_check_woocommerce()) {
        return;
    }
    
    // 加载核心文件
    require_once EVERSHOP_INTEGRATION_PLUGIN_DIR . 'includes/class-evershop-api.php';
    require_once EVERSHOP_INTEGRATION_PLUGIN_DIR . 'includes/class-evershop-auth.php';
    require_once EVERSHOP_INTEGRATION_PLUGIN_DIR . 'includes/class-evershop-products.php';
    require_once EVERSHOP_INTEGRATION_PLUGIN_DIR . 'includes/class-evershop-cart.php';
    require_once EVERSHOP_INTEGRATION_PLUGIN_DIR . 'includes/class-evershop-checkout.php';
    require_once EVERSHOP_INTEGRATION_PLUGIN_DIR . 'includes/class-evershop-custom-fields.php';
    require_once EVERSHOP_INTEGRATION_PLUGIN_DIR . 'includes/class-evershop-blocks.php';
    require_once EVERSHOP_INTEGRATION_PLUGIN_DIR . 'includes/class-evershop-cors.php';
    require_once EVERSHOP_INTEGRATION_PLUGIN_DIR . 'includes/class-evershop-variant-system.php';
    require_once EVERSHOP_INTEGRATION_PLUGIN_DIR . 'includes/class-evershop-variation-gallery.php';
    require_once EVERSHOP_INTEGRATION_PLUGIN_DIR . 'includes/class-evershop-content-builder.php';
    
    // 初始化类
    EverShop_API::init();
    EverShop_Auth::init();
    EverShop_Products::init();
    EverShop_Cart::init();
    EverShop_Checkout::init();
    EverShop_Custom_Fields::init();
    EverShop_Blocks::init();
    EverShop_CORS::init();
    EverShop_Variant_System::init();
}

/**
 * 添加管理菜单
 */
add_action('admin_menu', 'evershop_integration_admin_menu');
function evershop_integration_admin_menu() {
    add_menu_page(
        'EverShop Integration',
        'EverShop',
        'manage_options',
        'evershop-integration',
        'evershop_integration_admin_page',
        'dashicons-cart',
        58
    );
    
    add_submenu_page(
        'evershop-integration',
        '字段参考',
        '字段参考',
        'manage_options',
        'evershop-field-reference',
        'evershop_integration_field_reference_page'
    );
}

/**
 * 主管理页面
 */
function evershop_integration_admin_page() {
    // 处理手动刷新缓存
    if (isset($_GET['refresh_cache']) && check_admin_referer('evershop_refresh_cache', '_wpnonce')) {
        delete_transient('evershop_field_migration_status');
        delete_transient('evershop_migration_status');
        wp_redirect(admin_url('admin.php?page=evershop-integration'));
        exit;
    }
    
    // 处理数据迁移
    if (isset($_POST['evershop_migrate_data']) && check_admin_referer('evershop_migrate_action', 'evershop_migrate_nonce')) {
        $result = evershop_migrate_specifications_to_features();
        delete_transient('evershop_migration_status');
        echo '<div class="notice notice-' . ($result['success'] ? 'success' : 'error') . ' is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
    }
    
    // 处理 Badge/Subheading 字段迁移
    if (isset($_POST['evershop_migrate_fields']) && check_admin_referer('evershop_migrate_fields_action', 'evershop_migrate_fields_nonce')) {
        $result = evershop_migrate_badge_subheading_fields();
        delete_transient('evershop_field_migration_status');
        echo '<div class="notice notice-' . ($result['success'] ? 'success' : 'error') . ' is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
    }
    
    ?>
    <div class="wrap">
        <h1>EverShop Integration for WooCommerce</h1>
        
        <div class="card" style="border-left: 4px solid #2271b1;">
            <h2>📦 插件架构说明</h2>
            <p><strong>架构类型：</strong> WordPress + WooCommerce 完整前后端架构</p>
            <p><strong>核心功能：</strong></p>
            <ul style="list-style: disc; margin-left: 20px; line-height: 1.8;">
                <li>为 WooCommerce 产品添加 EverShop 风格的自定义字段</li>
                <li>提供后台 Meta Boxes 管理界面（Badge、Features、Key Benefits、Videos、Testimonials）</li>
                <li>支持 WordPress 主题前端显示（非 Headless，完整服务端渲染）</li>
                <li>扩展 WooCommerce REST API 以支持自定义字段</li>
                <li>数据存储在 WordPress wp_postmeta 表</li>
            </ul>
            <p style="margin-top: 15px; padding: 10px; background: #f0f6fc; border-radius: 4px;">
                <strong>💡 提示：</strong> 本插件<strong>不是</strong> Headless CMS 方案，而是为标准 WordPress + WooCommerce 架构添加 EverShop 功能特性。
            </p>
        </div>
        
        <div class="card">
            <h2>Status</h2>
            <table class="widefat">
                <tbody>
                    <tr>
                        <td><strong>Plugin Version</strong></td>
                        <td><?php echo EVERSHOP_INTEGRATION_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td><strong>WordPress Version</strong></td>
                        <td><?php echo get_bloginfo('version'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>WooCommerce Version</strong></td>
                        <td><?php echo defined('WC_VERSION') ? WC_VERSION : 'Not Installed'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>PHP Version</strong></td>
                        <td><?php echo PHP_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Theme</strong></td>
                        <td><?php echo wp_get_theme()->get('Name'); ?> (<?php echo wp_get_theme()->get('Version'); ?>)</td>
                    </tr>
                    <tr>
                        <td><strong>REST API Base</strong></td>
                        <td><a href="<?php echo rest_url('wc/v3/products'); ?>" target="_blank">
                            <?php echo rest_url('wc/v3/products'); ?>
                        </a></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="card">
            <h2>Quick Links</h2>
            <p>
                <a href="<?php echo admin_url('edit.php?post_type=product'); ?>" class="button button-primary">管理产品</a>
                <a href="<?php echo admin_url('admin.php?page=wc-settings'); ?>" class="button">WooCommerce 设置</a>
                <a href="<?php echo admin_url('themes.php'); ?>" class="button">主题设置</a>
            </p>
        </div>
        
        <div class="card">
            <h2>Custom Product Fields</h2>
            <p>EverShop Integration adds the following custom fields to products (prefix: <code>_espf_</code>):</p>
            <ul style="list-style: disc; margin-left: 20px;">
                <li><strong>Badge:</strong>
                    <ul style="list-style: circle; margin-left: 20px;">
                        <li><code>_espf_badge_enabled</code> - Enable/disable badge (boolean)</li>
                        <li><code>_espf_badge_text</code> - Badge text (max 50 chars)</li>
                        <li><code>_espf_badge_color</code> - Badge color (hex, default: <?php echo EVERSHOP_DEFAULT_BADGE_COLOR; ?>)</li>
                    </ul>
                </li>
                <li><strong>Content:</strong>
                    <ul style="list-style: circle; margin-left: 20px;">
                        <li><code>_espf_subheading</code> - Product subheading/side_title</li>
                        <li><code>_espf_features</code> - Product features list (JSON array)</li>
                    </ul>
                </li>
                <li><strong>Media:</strong>
                    <ul style="list-style: circle; margin-left: 20px;">
                        <li><code>_product_videos</code> - Product video URLs (JSON array)</li>
                        <li><code>_videos_title</code> - Videos section title</li>
                    </ul>
                </li>
                <li><strong>Social Proof:</strong>
                    <ul style="list-style: circle; margin-left: 20px;">
                        <li><code>_product_testimonials</code> - Customer testimonials (JSON array)</li>
                        <li><code>_testimonials_title</code> - Testimonials section title</li>
                    </ul>
                </li>
            </ul>
            <p><strong>Note:</strong> All fields use <code>_espf_</code> prefix to match EverShop naming convention.</p>
        </div>
        
        <?php
        // 使用缓存机制检测迁移状态（避免每次都查询数据库）
        $field_migration_status = get_transient('evershop_field_migration_status');
        if ($field_migration_status === false) {
            $field_migration_status = evershop_check_field_migration_status();
            set_transient('evershop_field_migration_status', $field_migration_status, EVERSHOP_CACHE_EXPIRATION);
        }
        
        $features_migration_status = get_transient('evershop_migration_status');
        if ($features_migration_status === false) {
            $features_migration_status = evershop_check_migration_status();
            set_transient('evershop_migration_status', $features_migration_status, EVERSHOP_CACHE_EXPIRATION);
        }
        
        // 判断整体状态
        $has_field_migration = $field_migration_status['needs_migration'];
        $has_features_migration = $features_migration_status['needs_migration'];
        $has_any_migration = $has_field_migration || $has_features_migration;
        
        // 确定卡片颜色
        if ($has_any_migration) {
            $card_color = '#dc3232'; // 红色 - 需要迁移
        } elseif ($field_migration_status['count'] > 0 || $features_migration_status['migrated_count'] > 0) {
            $card_color = '#46b450'; // 绿色 - 已完成迁移
        } else {
            $card_color = '#2271b1'; // 蓝色 - 无需迁移
        }
        
        $current_time = current_time('Y-m-d H:i:s');
        ?>
        
        <div class="card" style="border-left: 4px solid <?php echo $card_color; ?>;">
            <h2>🔄 数据迁移中心</h2>
            <p><strong>最后检测时间：</strong> <?php echo esc_html($current_time); ?></p>
            
            <!-- 字段迁移状态 -->
            <div style="margin: 15px 0; padding: 12px; background: #f9f9f9; border-radius: 4px;">
                <h3 style="margin-top: 0;">📋 字段命名规范检查</h3>
                <?php if ($has_field_migration) : ?>
                    <p style="color: #dc3232; font-weight: 600; margin: 5px 0;">
                        ⚠️ 检测到 <strong><?php echo $field_migration_status['count']; ?> 个产品</strong>需要迁移字段命名
                    </p>
                    <p style="margin: 5px 0; font-size: 13px; color: #666;">
                        需要添加 <code>_espf_</code> 前缀：Badge、Subheading、Videos、Testimonials 字段
                    </p>
                <?php else : ?>
                    <p style="color: #46b450; font-weight: 600; margin: 5px 0;">
                        ✅ 所有产品字段已使用标准 <code>_espf_</code> 前缀命名
                    </p>
                    <p style="margin: 5px 0; font-size: 13px; color: #666;">
                        Badge、Subheading、Videos、Testimonials 字段命名符合 EverShop 规范
                    </p>
                <?php endif; ?>
            </div>
            
            <!-- Features 数据迁移状态 -->
            <div style="margin: 15px 0; padding: 12px; background: #f9f9f9; border-radius: 4px;">
                <h3 style="margin-top: 0;">🎯 Features 数据结构检查</h3>
                <?php if ($has_features_migration) : ?>
                    <p style="color: #dc3232; font-weight: 600; margin: 5px 0;">
                        ⚠️ 检测到 <strong><?php echo $features_migration_status['count']; ?> 个产品</strong>需要迁移数据结构
                    </p>
                    <p style="margin: 5px 0; font-size: 13px; color: #666;">
                        需要从 <code>_product_specifications</code> (Label/Value) 转换为 <code>_espf_features</code> (文本数组)
                    </p>
                <?php elseif ($features_migration_status['migrated_count'] > 0) : ?>
                    <p style="color: #46b450; font-weight: 600; margin: 5px 0;">
                        ✅ 所有产品已使用新的 Features 数据结构（共 <strong><?php echo $features_migration_status['migrated_count']; ?> 个产品</strong>）
                    </p>
                    <p style="margin: 5px 0; font-size: 13px; color: #666;">
                        数据已从 <code>_product_specifications</code> 成功迁移到 <code>_espf_features</code>
                    </p>
                <?php else : ?>
                    <p style="color: #2271b1; font-weight: 600; margin: 5px 0;">
                        ℹ️ 所有产品使用标准 <code>_espf_features</code> 字段
                    </p>
                    <p style="margin: 5px 0; font-size: 13px; color: #666;">
                        无需迁移，数据结构符合 EverShop 规范
                    </p>
                <?php endif; ?>
            </div>
            
            <!-- 操作按钮 -->
            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd;">
                <?php if ($has_any_migration) : ?>
                    <p style="margin-bottom: 10px;">
                        <strong>建议立即执行迁移以确保数据一致性</strong>
                    </p>
                    
                <?php if ($has_field_migration) : ?>
                    <form method="post" style="display: inline-block; margin-right: 10px;" onsubmit="return confirm('确定要迁移 <?php echo $field_migration_status['count']; ?> 个产品的字段命名吗？\n\n操作说明：\n• 旧字段将保留（不删除）\n• 新字段将被创建（添加 _espf_ 前缀）\n• 数据将被复制到新字段');">
                        <?php wp_nonce_field('evershop_migrate_fields_action', 'evershop_migrate_fields_nonce'); ?>
                        <button type="submit" name="evershop_migrate_fields" class="button button-primary">
                            🚀 添加 _espf_ 前缀（<?php echo $field_migration_status['count']; ?> 个产品）
                        </button>
                    </form>
                <?php endif; ?>
                
                <?php if ($has_features_migration) : ?>
                    <form method="post" style="display: inline-block; margin-right: 10px;" onsubmit="return confirm('确定要迁移 <?php echo $features_migration_status['count']; ?> 个产品的 Features 数据吗？\n\n操作说明：\n• Label/Value 结构将转换为文本数组\n• 旧字段将保留（不删除）\n• 数据示例：{\"label\":\"Weight\",\"value\":\"5 lbs\"} → \"Weight: 5 lbs\"');">
                        <?php wp_nonce_field('evershop_migrate_action', 'evershop_migrate_nonce'); ?>
                        <button type="submit" name="evershop_migrate_data" class="button button-primary">
                            🚀 转换 Features 结构（<?php echo $features_migration_status['count']; ?> 个产品）
                        </button>
                    </form>
                <?php endif; ?>
                    
                    <!-- 查看详细信息按钮 -->
                    <button type="button" class="button button-secondary" onclick="document.getElementById('migration-details').style.display = document.getElementById('migration-details').style.display === 'none' ? 'block' : 'none';">
                        📖 查看迁移详情
                    </button>
                <?php endif; ?>
                
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=evershop-integration&refresh_cache=1'), 'evershop_refresh_cache'); ?>" class="button button-secondary">
                    🔄 刷新检测
                </a>
            </div>
            
            <!-- 详细迁移信息（默认折叠）-->
            <?php if ($has_any_migration) : ?>
            <div id="migration-details" style="display: none; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                <?php if ($has_field_migration) : ?>
                <div style="margin-bottom: 20px;">
                    <h3>字段命名迁移映射表</h3>
                    <table class="widefat" style="max-width: 700px;">
                        <thead>
                            <tr>
                                <th>字段类别</th>
                                <th>旧字段名</th>
                                <th>→</th>
                                <th>新字段名</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td rowspan="3"><strong>Badge 徽章</strong></td>
                                <td><code>_badge_enabled</code></td>
                                <td>→</td>
                                <td><code>_espf_badge_enabled</code></td>
                            </tr>
                            <tr>
                                <td><code>_badge_text</code></td>
                                <td>→</td>
                                <td><code>_espf_badge_text</code></td>
                            </tr>
                            <tr>
                                <td><code>_badge_color</code></td>
                                <td>→</td>
                                <td><code>_espf_badge_color</code></td>
                            </tr>
                            <tr style="border-top: 2px solid #ddd;">
                                <td><strong>Subheading 副标题</strong></td>
                                <td><code>_product_subheading</code></td>
                                <td>→</td>
                                <td><code>_espf_subheading</code></td>
                            </tr>
                            <tr style="border-top: 2px solid #ddd;">
                                <td rowspan="2"><strong>Videos 视频</strong></td>
                                <td><code>_product_videos</code></td>
                                <td>→</td>
                                <td><code>_espf_product_videos</code></td>
                            </tr>
                            <tr>
                                <td><code>_videos_title</code></td>
                                <td>→</td>
                                <td><code>_espf_videos_title</code></td>
                            </tr>
                            <tr style="border-top: 2px solid #ddd;">
                                <td rowspan="2"><strong>Testimonials 评价</strong></td>
                                <td><code>_product_testimonials</code></td>
                                <td>→</td>
                                <td><code>_espf_product_testimonials</code></td>
                            </tr>
                            <tr>
                                <td><code>_testimonials_title</code></td>
                                <td>→</td>
                                <td><code>_espf_testimonials_title</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                
                <?php if ($has_features_migration) : ?>
                <div style="margin-bottom: 20px;">
                    <h3>Features 数据结构转换</h3>
                    <p><strong>从：</strong> <code>_product_specifications</code> (Label/Value 结构)</p>
                    <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; font-size: 12px;">[{"label": "Weight", "value": "5 lbs"}, {"label": "Servings", "value": "30"}]</pre>
                    
                    <p><strong>到：</strong> <code>_espf_features</code> (简单文本数组)</p>
                    <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; font-size: 12px;">["Weight: 5 lbs", "Servings: 30"]</pre>
                </div>
                <?php endif; ?>
                
                <div style="padding: 12px; background: #fff3cd; border-left: 4px solid #ffc107;">
                    <strong>⚠️ 安全提示：</strong>
                    <ul style="margin: 10px 0 0 20px; list-style: disc; font-size: 13px;">
                        <li>迁移操作会<strong>复制</strong>数据到新字段</li>
                        <li>旧字段<strong>不会被删除</strong>（保留用于回滚）</li>
                        <li>已有新字段数据的产品<strong>不会被覆盖</strong></li>
                        <li>操作安全，可随时在产品编辑页面手动调整</li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * 字段参考页面
 */
function evershop_integration_field_reference_page() {
    ?>
    <div class="wrap">
        <h1>EverShop 字段参考文档</h1>
        
        <div class="card">
            <h2>字段前缀说明</h2>
            <p>所有 EverShop 自定义字段使用 <code>_espf_</code> 前缀（EverShop Product Fields）</p>
            <p><strong>存储位置：</strong> WordPress <code>wp_postmeta</code> 表</p>
            <p><strong>关联：</strong> <code>post_type = 'product'</code></p>
        </div>
        
        <div class="card">
            <h2>1. Badge 徽章字段</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>字段名</th>
                        <th>数据类型</th>
                        <th>说明</th>
                        <th>示例值</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>_espf_badge_enabled</code></td>
                        <td>string</td>
                        <td>是否启用徽章（'yes' 或 'no'）</td>
                        <td>'yes'</td>
                    </tr>
                    <tr>
                        <td><code>_espf_badge_text</code></td>
                        <td>string (max 50)</td>
                        <td>徽章文字</td>
                        <td>'30% OFF'</td>
                    </tr>
                    <tr>
                        <td><code>_espf_badge_color</code></td>
                        <td>string (hex)</td>
                        <td>徽章背景颜色</td>
                        <td>'#ef4444'</td>
                    </tr>
                </tbody>
            </table>
            
            <h3>REST API 输出：</h3>
            <pre><code>GET <?php echo rest_url('wc/v3/products/{id}'); ?>

{
  "badge": {
    "enabled": true,
    "text": "30% OFF",
    "color": "#ef4444"
  },
  "badge_enabled": true,
  "badge_text": "30% OFF",
  "badge_color": "#ef4444"
}</code></pre>
        </div>
        
        <div class="card">
            <h2>2. Subheading 副标题字段</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>字段名</th>
                        <th>数据类型</th>
                        <th>说明</th>
                        <th>示例值</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>_espf_subheading</code></td>
                        <td>text</td>
                        <td>产品副标题/侧标题</td>
                        <td>'ADVANCED L-CARNITINE FORMULA'</td>
                    </tr>
                </tbody>
            </table>
            
            <h3>REST API 输出：</h3>
            <pre><code>{
  "subheading": "ADVANCED L-CARNITINE FORMULA",
  "side_title": "ADVANCED L-CARNITINE FORMULA"
}</code></pre>
        </div>
        
        <div class="card">
            <h2>3. Features 产品特性字段</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>字段名</th>
                        <th>数据类型</th>
                        <th>说明</th>
                        <th>示例值</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>_espf_features</code></td>
                        <td>JSON array</td>
                        <td>产品特性列表（文本数组）</td>
                        <td>["High Protein", "Sugar Free"]</td>
                    </tr>
                </tbody>
            </table>
            
            <h3>数据格式：</h3>
            <pre><code>// wp_postmeta.meta_value
'["30 Servings Per Container","Sugar Free & Gluten Free","Supports Lean Muscle Mass"]'

// REST API 输出
{
  "features": [
    "30 Servings Per Container",
    "Sugar Free & Gluten Free",
    "Supports Lean Muscle Mass"
  ]
}</code></pre>
        </div>
        
        <div class="card">
            <h2>4. Key Benefits 关键优势字段</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>字段名</th>
                        <th>数据类型</th>
                        <th>说明</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>_espf_key_benefits</code></td>
                        <td>JSON array of objects</td>
                        <td>关键优势列表（包含图标、标题、描述）</td>
                    </tr>
                </tbody>
            </table>
            
            <h3>数据格式：</h3>
            <pre><code>{
  "key_benefits": [
    {
      "icon": "💪",
      "title": "Build Muscle",
      "description": "Supports lean muscle growth with 30g protein per serving."
    },
    {
      "icon": "⚡",
      "title": "Boost Energy",
      "description": "Natural caffeine for sustained energy."
    }
  ]
}</code></pre>
        </div>
        
        <div class="card">
            <h2>5. Videos 视频字段</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>字段名</th>
                        <th>数据类型</th>
                        <th>说明</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>_product_videos</code></td>
                        <td>JSON array</td>
                        <td>产品视频 URL 列表</td>
                    </tr>
                    <tr>
                        <td><code>_videos_title</code></td>
                        <td>string</td>
                        <td>视频区域标题</td>
                    </tr>
                </tbody>
            </table>
            
            <h3>数据格式：</h3>
            <pre><code>{
  "product_videos": [
    "https://youtube.com/watch?v=xxx",
    "https://vimeo.com/123456"
  ],
  "videos_title": "Watch Jay Cutler in Action"
}</code></pre>
        </div>
        
        <div class="card">
            <h2>6. Testimonials 客户评价字段</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>字段名</th>
                        <th>数据类型</th>
                        <th>说明</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>_product_testimonials</code></td>
                        <td>JSON array of objects</td>
                        <td>客户评价列表</td>
                    </tr>
                    <tr>
                        <td><code>_testimonials_title</code></td>
                        <td>string</td>
                        <td>评价区域标题</td>
                    </tr>
                </tbody>
            </table>
            
            <h3>数据格式：</h3>
            <pre><code>{
  "testimonials": [
    {
      "author": "John Doe",
      "rating": 5,
      "content": "Amazing product!",
      "avatar": "https://example.com/avatar.jpg"
    }
  ],
  "testimonials_title": "Real Reviews from Real People"
}</code></pre>
        </div>
        
        <div class="card">
            <h2>主题模板使用示例</h2>
            <h3>读取 Badge：</h3>
            <pre><code>&lt;?php
$badge_enabled = get_post_meta(get_the_ID(), '_espf_badge_enabled', true);
$badge_text = get_post_meta(get_the_ID(), '_espf_badge_text', true);
$badge_color = get_post_meta(get_the_ID(), '_espf_badge_color', true);

if ($badge_enabled === 'yes' && $badge_text) :
?&gt;
    &lt;div class="product-badge" style="background-color: &lt;?php echo esc_attr($badge_color); ?&gt;;"&gt;
        &lt;?php echo esc_html($badge_text); ?&gt;
    &lt;/div&gt;
&lt;?php endif; ?&gt;</code></pre>
            
            <h3>读取 Features：</h3>
            <pre><code>&lt;?php
$features = get_post_meta(get_the_ID(), '_espf_features', true);
$features_array = $features ? json_decode($features, true) : [];

if (!empty($features_array)) :
    foreach ($features_array as $feature) :
        echo '&lt;li&gt;' . esc_html($feature) . '&lt;/li&gt;';
    endforeach;
endif;
?&gt;</code></pre>
            
            <h3>读取 Key Benefits：</h3>
            <pre><code>&lt;?php
$benefits = get_post_meta(get_the_ID(), '_espf_key_benefits', true);
$benefits_array = $benefits ? json_decode($benefits, true) : [];

foreach ($benefits_array as $benefit) :
?&gt;
    &lt;div class="benefit-card"&gt;
        &lt;div class="benefit-icon"&gt;&lt;?php echo esc_html($benefit['icon']); ?&gt;&lt;/div&gt;
        &lt;h3&gt;&lt;?php echo esc_html($benefit['title']); ?&gt;&lt;/h3&gt;
        &lt;p&gt;&lt;?php echo esc_html($benefit['description']); ?&gt;&lt;/p&gt;
    &lt;/div&gt;
&lt;?php endforeach; ?&gt;</code></pre>
        </div>
        
        <div class="card">
            <h2>WooCommerce REST API 扩展</h2>
            <p>所有自定义字段已自动注册到 WooCommerce REST API：</p>
            <pre><code>GET <?php echo rest_url('wc/v3/products/{id}'); ?>
Authorization: Basic BASE64(consumer_key:consumer_secret)

Response:
{
  "id": 123,
  "name": "Product Name",
  "badge": {...},
  "subheading": "...",
  "features": [...],
  "key_benefits": [...],
  "product_videos": [...],
  "testimonials": [...]
}</code></pre>
        </div>
    </div>
    <?php
}

/**
 * 添加自定义管理列
 */
add_filter('manage_product_posts_columns', 'evershop_product_columns');
function evershop_product_columns($columns) {
    $new_columns = [];
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'name') {
            $new_columns['evershop_badge'] = 'EverShop Badge';
            $new_columns['evershop_videos'] = 'Videos';
        }
    }
    return $new_columns;
}

add_action('manage_product_posts_custom_column', 'evershop_product_column_content', 10, 2);
function evershop_product_column_content($column, $post_id) {
    if ($column === 'evershop_badge') {
        $badge_enabled = get_post_meta($post_id, '_espf_badge_enabled', true);
        $badge_text = get_post_meta($post_id, '_espf_badge_text', true);
        if ($badge_enabled === 'yes' && $badge_text) {
            $badge_color = get_post_meta($post_id, '_espf_badge_color', true) ?: EVERSHOP_DEFAULT_BADGE_COLOR;
            echo '<span style="background:' . esc_attr($badge_color) . '; color: white; padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: 600;">' . esc_html($badge_text) . '</span>';
        } else {
            echo '<span style="color: #999;">-</span>';
        }
    }
    
    if ($column === 'evershop_videos') {
        $videos = get_post_meta($post_id, '_product_videos', true);
        if ($videos) {
            $video_array = json_decode($videos, true);
            $count = is_array($video_array) ? count($video_array) : 0;
            if ($count > 0) {
                echo '<span style="color: #2271b1;">📹 ' . $count . ' video' . ($count > 1 ? 's' : '') . '</span>';
            } else {
                echo '<span style="color: #999;">-</span>';
            }
        } else {
            echo '<span style="color: #999;">-</span>';
        }
    }
}

/**
 * 检查 Badge/Subheading 字段迁移状态
 */
function evershop_check_field_migration_status() {
    global $wpdb;
    
    $field_mapping = [
        '_badge_enabled' => '_espf_badge_enabled',
        '_badge_text' => '_espf_badge_text',
        '_badge_color' => '_espf_badge_color',
        '_product_subheading' => '_espf_subheading',
        '_product_videos' => '_espf_product_videos',
        '_videos_title' => '_espf_videos_title',
        '_product_testimonials' => '_espf_product_testimonials',
        '_testimonials_title' => '_espf_testimonials_title'
    ];
    
    $needs_migration_products = [];
    
    // 检查每个字段映射
    foreach ($field_mapping as $old_field => $new_field) {
        // 查找有旧字段但没有新字段的产品
        $query = $wpdb->prepare("
            SELECT DISTINCT pm1.post_id
            FROM {$wpdb->postmeta} pm1
            LEFT JOIN {$wpdb->postmeta} pm2 
                ON pm1.post_id = pm2.post_id 
                AND pm2.meta_key = %s
            WHERE pm1.meta_key = %s
            AND pm1.meta_value != ''
            AND (pm2.meta_id IS NULL OR pm2.meta_value = '')
        ", $new_field, $old_field);
        
        $products = $wpdb->get_col($query);
        $needs_migration_products = array_merge($needs_migration_products, $products);
    }
    
    // 去重
    $needs_migration_products = array_unique($needs_migration_products);
    $needs_migration_count = count($needs_migration_products);
    
    return [
        'needs_migration' => $needs_migration_count > 0,
        'count' => $needs_migration_count
    ];
}

/**
 * 执行 Badge/Subheading 字段迁移
 */
function evershop_migrate_badge_subheading_fields() {
    global $wpdb;
    
    $field_mapping = [
        '_badge_enabled' => '_espf_badge_enabled',
        '_badge_text' => '_espf_badge_text',
        '_badge_color' => '_espf_badge_color',
        '_product_subheading' => '_espf_subheading',
        '_product_videos' => '_espf_product_videos',
        '_videos_title' => '_espf_videos_title',
        '_product_testimonials' => '_espf_product_testimonials',
        '_testimonials_title' => '_espf_testimonials_title'
    ];
    
    $migrated_count = 0;
    $errors = [];
    
    foreach ($field_mapping as $old_field => $new_field) {
        // 查找使用旧字段的产品
        $products = $wpdb->get_results($wpdb->prepare("
            SELECT post_id, meta_value
            FROM {$wpdb->postmeta}
            WHERE meta_key = %s
            AND meta_value != ''
        ", $old_field));
        
        foreach ($products as $product) {
            // 检查新字段是否已存在
            $existing = get_post_meta($product->post_id, $new_field, true);
            
            // 只在新字段不存在时复制
            if (empty($existing)) {
                $result = update_post_meta(
                    $product->post_id,
                    $new_field,
                    $product->meta_value
                );
                
                if ($result) {
                    $migrated_count++;
                }
            }
        }
    }
    
    if ($migrated_count === 0) {
        return [
            'success' => true,
            'message' => '✅ 所有产品字段已使用 _espf_ 前缀命名，无需迁移。'
        ];
    }
    
    return [
        'success' => true,
        'message' => "✅ 成功为 {$migrated_count} 个字段添加 _espf_ 前缀！旧字段已保留用于回滚。"
    ];
}

/**
 * 检查数据迁移状态
 */
function evershop_check_migration_status() {
    global $wpdb;
    
    // 查找所有有旧字段但没有新字段的产品
    $query = "
        SELECT COUNT(DISTINCT pm1.post_id) as needs_migration
        FROM {$wpdb->postmeta} pm1
        INNER JOIN {$wpdb->posts} p ON pm1.post_id = p.ID
        LEFT JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id AND pm2.meta_key = '_espf_features'
        WHERE pm1.meta_key = '_product_specifications'
        AND p.post_type = 'product'
        AND p.post_status = 'publish'
        AND pm1.meta_value != ''
        AND pm2.meta_id IS NULL
    ";
    
    $needs_migration_count = $wpdb->get_var($query);
    
    // 查找已经迁移的产品数量
    $migrated_query = "
        SELECT COUNT(DISTINCT post_id) as migrated
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_espf_features'
        AND meta_value != ''
    ";
    
    $migrated_count = $wpdb->get_var($migrated_query);
    
    return [
        'needs_migration' => $needs_migration_count > 0,
        'count' => (int) $needs_migration_count,
        'migrated_count' => (int) $migrated_count
    ];
}

/**
 * 执行数据迁移
 */
function evershop_migrate_specifications_to_features() {
    global $wpdb;
    
    // 查找所有需要迁移的产品
    $query = "
        SELECT DISTINCT pm1.post_id, pm1.meta_value
        FROM {$wpdb->postmeta} pm1
        INNER JOIN {$wpdb->posts} p ON pm1.post_id = p.ID
        LEFT JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id AND pm2.meta_key = '_espf_features'
        WHERE pm1.meta_key = '_product_specifications'
        AND p.post_type = 'product'
        AND pm1.meta_value != ''
        AND pm2.meta_id IS NULL
    ";
    
    $products = $wpdb->get_results($query);
    
    if (empty($products)) {
        return [
            'success' => true,
            'message' => '✅ 所有产品已使用新的 Features 数据结构，无需迁移。'
        ];
    }
    
    $migrated_count = 0;
    $errors = [];
    
    foreach ($products as $product) {
        $specifications = json_decode($product->meta_value, true);
        
        if (!is_array($specifications)) {
            continue;
        }
        
        // 将 Label/Value 对转换为简单文本列表
        $features = [];
        foreach ($specifications as $spec) {
            if (isset($spec['label']) && isset($spec['value'])) {
                $features[] = $spec['label'] . ': ' . $spec['value'];
            } elseif (isset($spec['label'])) {
                $features[] = $spec['label'];
            } elseif (isset($spec['value'])) {
                $features[] = $spec['value'];
            }
        }
        
        if (!empty($features)) {
            $result = update_post_meta(
                $product->post_id,
                '_espf_features',
                wp_json_encode($features)
            );
            
            if ($result) {
                $migrated_count++;
            } else {
                $errors[] = "产品 ID {$product->post_id} 迁移失败";
            }
        }
    }
    
    if (!empty($errors)) {
        return [
            'success' => false,
            'message' => '⚠️ 迁移部分完成。成功：' . $migrated_count . ' 个产品，失败：' . count($errors) . ' 个产品'
        ];
    }
    
    return [
        'success' => true,
        'message' => "✅ 成功转换 {$migrated_count} 个产品的 Features 数据结构！旧数据（_product_specifications）已保留，可随时回滚。"
    ];
}

