<?php
/**
 * EverShop TikTok Integration
 * 
 * Handles TikTok Pixel (Browser) and Events API (Server)
 */

if (!defined('ABSPATH')) {
    exit;
}

class EverShop_TikTok {

    private static $pixel_id;
    private static $access_token;
    private static $api_endpoint = 'https://business-api.tiktok.com/open_api/v1.3/event/track/';
    private static $test_event_code;

    private static $test_mode = 'test';

    public static function init() {
        $instance = new self();
        $instance->load_settings();
        $instance->setup_hooks();
    }

    private function load_settings() {
        self::$pixel_id = get_option('evershop_tiktok_pixel_id');
        self::$access_token = get_option('evershop_tiktok_access_token');
        self::$test_event_code = get_option('evershop_tiktok_test_event_code');
        self::$test_mode = get_option('evershop_tiktok_test_mode', 'test');
        
        // 支持自定义 API Endpoint (为 Events API Gateway 预留)
        $custom_endpoint = get_option('evershop_tiktok_api_endpoint');
        if (!empty($custom_endpoint)) {
            self::$api_endpoint = $custom_endpoint;
        }
    }

    private function setup_hooks() {
        // Admin Settings
        add_action('admin_init', [$this, 'register_settings']);
        
        // Admin Menu
        add_action('admin_menu', [$this, 'add_catalog_menu']);
        
        // AJAX Handlers
        add_action('wp_ajax_tiktok_upload_to_catalog', [$this, 'ajax_upload_to_catalog']);
        add_action('wp_ajax_tiktok_get_products', [$this, 'ajax_get_products']);

        // 如果没有配置 Pixel ID，不执行后续操作
        if (empty(self::$pixel_id)) {
            return;
        }

        // 1. Browser Pixel Code
        add_action('wp_head', [$this, 'inject_base_pixel_code'], 1);
        add_action('wp_footer', [$this, 'inject_browser_events']);

        // 2. Server Side Events (CAPI)
        // ViewContent 
        add_action('wp', [$this, 'track_server_view_content']);

        // AddToCart
        add_action('woocommerce_add_to_cart', [$this, 'track_server_add_to_cart'], 10, 6);

        // InitiateCheckout
        add_action('template_redirect', [$this, 'track_server_initiate_checkout']);

        // AddPaymentInfo (Server Side)
        add_action('woocommerce_checkout_order_processed', [$this, 'track_server_add_payment_info']);

        // Purchase
        add_action('woocommerce_thankyou', [$this, 'track_server_purchase']);

        // CompleteRegistration
        add_action('user_register', [$this, 'track_server_registration']);
    }

    /**
     * 注册设置项
     */
    public function register_settings() {
        register_setting('evershop_tiktok_settings', 'evershop_tiktok_pixel_id');
        register_setting('evershop_tiktok_settings', 'evershop_tiktok_access_token');
        register_setting('evershop_tiktok_settings', 'evershop_tiktok_test_event_code');
        register_setting('evershop_tiktok_settings', 'evershop_tiktok_api_endpoint');
        // 注册 Test Mode 选项
        register_setting('evershop_tiktok_settings', 'evershop_tiktok_test_mode');
        // TikTok Catalog API 设置
        register_setting('evershop_tiktok_settings', 'evershop_tiktok_catalog_id');
        register_setting('evershop_tiktok_settings', 'evershop_tiktok_app_key');
        register_setting('evershop_tiktok_settings', 'evershop_tiktok_app_secret');
    }

    /**
     * 在后台管理页面中注入 TikTok Pixel 脚本
     * 用于 Pixel Upload 功能
     */
    private function inject_pixel_script_for_admin() {
        if (empty(self::$pixel_id)) {
            return;
        }
        ?>
        <!-- TikTok Pixel Code for Admin (Pixel Upload) -->
        <script>
        !function (w, d, t) {
          w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(
        var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script")
        ;n.type="text/javascript",n.async=!0,n.src=r+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};
          ttq.load('<?php echo esc_js(self::$pixel_id); ?>');
          ttq.page();
          
          // 在控制台显示 Pixel 已加载
          console.log('✅ TikTok Pixel 已加载 (Admin): <?php echo esc_js(self::$pixel_id); ?>');
        }(window, document, 'ttq');
        </script>
        <!-- End TikTok Pixel Code -->
        <?php
    }

    /**
     * 添加后台管理菜单
     */
    public function add_catalog_menu() {
        add_submenu_page(
            'woocommerce',
            'TikTok Catalog 管理',
            'TikTok Catalog',
            'manage_woocommerce',
            'tiktok-catalog',
            [$this, 'render_catalog_page']
        );
    }

    /**
     * 渲染 Catalog 管理页面
     */
    public function render_catalog_page() {
        // 在后台页面中也加载 TikTok Pixel 脚本
        $this->inject_pixel_script_for_admin();
        ?>
        <div class="wrap">
            <h1>TikTok Catalog 产品管理 (Pixel Upload)</h1>
            
            <div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">
                <h2>Pixel 配置状态</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Pixel ID</th>
                        <td>
                            <code><?php echo esc_html(get_option('evershop_tiktok_pixel_id') ?: '未配置'); ?></code>
                            <?php if (get_option('evershop_tiktok_pixel_id')): ?>
                                <span style="color: #46b450; margin-left: 10px;">✓ 已配置</span>
                            <?php else: ?>
                                <span style="color: #dc3232; margin-left: 10px;">✗ 未配置</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Access Token</th>
                        <td>
                            <?php if (get_option('evershop_tiktok_access_token')): ?>
                                <code>••••••••••••••••</code>
                                <span style="color: #46b450; margin-left: 10px;">✓ 已配置</span>
                            <?php else: ?>
                                <code>未配置</code>
                                <span style="color: #dc3232; margin-left: 10px;">✗ 未配置</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                <p class="description">
                    如需修改配置，请在 EverShop 插件设置页面中配置 Pixel ID 和 Access Token
                </p>
            </div>

            <div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">
                <h2>产品列表</h2>
                
                <div style="margin-bottom: 15px;">
                    <button type="button" class="button button-primary" id="tiktok-upload-selected">
                        📤 上传选中的产品到 TikTok Catalog (Pixel Upload)
                    </button>
                    <button type="button" class="button" id="tiktok-select-all">全选</button>
                    <button type="button" class="button" id="tiktok-deselect-all">取消全选</button>
                    <span id="selected-count" style="margin-left: 15px;">已选择: <strong>0</strong> 个产品</span>
                </div>

                <div id="upload-progress" style="display: none; margin-bottom: 15px; padding: 10px; background: #e7f3ff; border-left: 4px solid #0073aa;">
                    <div id="progress-text">正在触发 Pixel 事件...</div>
                    <progress id="progress-bar" value="0" max="100" style="width: 100%; height: 25px;"></progress>
                    <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                        💡 提示：产品信息将在触发事件后的 15 分钟内同步到 TikTok Catalog
                    </p>
                </div>

                <div id="upload-results" style="display: none; margin-bottom: 15px;"></div>

                <table class="wp-list-table widefat fixed striped" id="tiktok-products-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="select-all-checkbox">
                            </th>
                            <th style="width: 60px;">图片</th>
                            <th>产品名称</th>
                            <th>SKU</th>
                            <th style="width: 100px;">类型</th>
                            <th style="width: 80px;">价格</th>
                            <th style="width: 80px;">库存</th>
                            <th style="width: 120px;">状态</th>
                        </tr>
                    </thead>
                    <tbody id="products-tbody">
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px;">
                                加载中...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <style>
            .product-image { max-width: 50px; height: auto; }
            .product-type-badge { 
                display: inline-block; 
                padding: 3px 8px; 
                background: #0073aa; 
                color: #fff; 
                border-radius: 3px; 
                font-size: 11px; 
            }
            .product-type-badge.variable { background: #f0ad4e; }
            .upload-status { font-weight: bold; }
            .upload-status.success { color: #46b450; }
            .upload-status.error { color: #dc3232; }
            .upload-status.pending { color: #999; }
            .variations-list {
                margin-top: 5px;
                padding-left: 20px;
                font-size: 0.9em;
                color: #666;
            }
            .variations-list li {
                list-style: disc;
                margin: 2px 0;
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            let productsData = [];
            let selectedProducts = new Set();

            // 加载产品列表
            function loadProducts() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'tiktok_get_products'
                    },
                    success: function(response) {
                        if (response.success) {
                            productsData = response.data;
                            renderProducts(productsData);
                        } else {
                            $('#products-tbody').html('<tr><td colspan="8" style="text-align: center; color: red;">加载失败: ' + response.data.message + '</td></tr>');
                        }
                    },
                    error: function() {
                        $('#products-tbody').html('<tr><td colspan="8" style="text-align: center; color: red;">加载失败，请刷新页面重试</td></tr>');
                    }
                });
            }

            // 渲染产品列表
            function renderProducts(products) {
                let html = '';
                products.forEach(function(product) {
                    let typeClass = product.type === 'variable' ? 'variable' : '';
                    let variationsHtml = '';
                    
                    if (product.variations && product.variations.length > 0) {
                        variationsHtml = '<ul class="variations-list">';
                        product.variations.forEach(function(variation) {
                            variationsHtml += '<li>' + variation.name + ' (SKU: ' + variation.sku + ')</li>';
                        });
                        variationsHtml += '</ul>';
                    }
                    
                    html += '<tr data-product-id="' + product.id + '">';
                    html += '<td><input type="checkbox" class="product-checkbox" value="' + product.id + '"></td>';
                    html += '<td><img src="' + product.image + '" class="product-image" /></td>';
                    html += '<td>' + product.name + variationsHtml + '</td>';
                    html += '<td>' + product.sku + '</td>';
                    html += '<td><span class="product-type-badge ' + typeClass + '">' + product.type + '</span></td>';
                    html += '<td>' + product.price + '</td>';
                    html += '<td>' + product.stock + '</td>';
                    html += '<td><span class="upload-status pending" data-product-id="' + product.id + '">未上传</span></td>';
                    html += '</tr>';
                });
                
                $('#products-tbody').html(html);
            }

            // 更新选中数量
            function updateSelectedCount() {
                $('#selected-count strong').text(selectedProducts.size);
            }

            // 产品复选框
            $('body').on('change', '.product-checkbox', function() {
                let productId = $(this).val();
                if ($(this).is(':checked')) {
                    selectedProducts.add(productId);
                } else {
                    selectedProducts.delete(productId);
                }
                updateSelectedCount();
            });

            // 全选
            $('#select-all-checkbox, #tiktok-select-all').on('click', function() {
                $('.product-checkbox').prop('checked', true).trigger('change');
            });

            // 取消全选
            $('#tiktok-deselect-all').on('click', function() {
                $('.product-checkbox').prop('checked', false);
                selectedProducts.clear();
                updateSelectedCount();
            });

            // 上传到 Catalog
            $('#tiktok-upload-selected').on('click', function() {
                if (selectedProducts.size === 0) {
                    alert('请至少选择一个产品');
                    return;
                }

                let confirmMsg = '确定要上传 ' + selectedProducts.size + ' 个产品到 TikTok Catalog 吗？\n\n';
                confirmMsg += '📌 工作原理：\n';
                confirmMsg += '• 在浏览器中使用 TikTok Pixel (ttq.track) 触发 ViewContent 事件\n';
                confirmMsg += '• TikTok 自动从 Browser Pixel 事件中提取产品信息\n';
                confirmMsg += '• 产品将在 15 分钟内同步到 Catalog\n';
                confirmMsg += '• 变体产品的所有变体都会被触发\n\n';
                confirmMsg += '⚠️ 请确保：\n';
                confirmMsg += '• Pixel ID 已正确配置\n';
                confirmMsg += '• 浏览器未安装广告拦截插件（会阻止 Pixel）';
                
                if (!confirm(confirmMsg)) {
                    return;
                }

                let productIds = Array.from(selectedProducts);
                uploadProducts(productIds);
            });

            // 上传产品到 TikTok (使用 Browser Pixel)
            function uploadProducts(productIds) {
                $('#upload-progress').show();
                $('#upload-results').hide().html('');
                $('#progress-bar').val(0);
                $('#progress-text').text('正在触发 Pixel 事件 0/' + productIds.length);

                let completed = 0;
                let results = { success: [], error: [] };
                let allProductsData = []; // 存储所有产品数据

                // 第一步：获取所有产品数据
                function fetchProductData(index) {
                    if (index >= productIds.length) {
                        // 所有数据已获取，开始触发 Browser Pixel 事件
                        triggerBrowserPixelEvents();
                        return;
                    }

                    let productId = productIds[index];
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'tiktok_upload_to_catalog',
                            product_id: productId
                        },
                        success: function(response) {
                            if (response.success && response.data.products_data) {
                                allProductsData.push({
                                    productId: productId,
                                    productName: response.data.product_name,
                                    products: response.data.products_data
                                });
                            } else {
                                results.error.push({ 
                                    id: productId, 
                                    message: response.data.message || '获取产品数据失败' 
                                });
                            }
                            fetchProductData(index + 1);
                        },
                        error: function() {
                            results.error.push({ id: productId, message: '网络错误' });
                            fetchProductData(index + 1);
                        }
                    });
                }

                // 第二步：触发 Browser Pixel 事件
                function triggerBrowserPixelEvents() {
                    // 检查 TikTok Pixel 是否已加载
                    if (typeof ttq === 'undefined') {
                        console.error('❌ TikTok Pixel (ttq) 未定义');
                        alert('TikTok Pixel 未加载，请确保 Pixel ID 已正确配置\n\n请刷新页面后重试');
                        $('#upload-progress').hide();
                        return;
                    }
                    
                    console.log('✅ TikTok Pixel 已检测到:', ttq);

                    let totalEvents = 0;
                    let completedEvents = 0;

                    // 计算总事件数
                    allProductsData.forEach(function(item) {
                        totalEvents += item.products.length;
                    });

                    if (totalEvents === 0) {
                        showResults(results);
                        return;
                    }
                    
                    console.log('📊 准备触发 ' + totalEvents + ' 个 Pixel 事件');

                    // 逐个触发事件（延迟避免过快）
                    let eventQueue = [];
                    allProductsData.forEach(function(item) {
                        item.products.forEach(function(productData) {
                            eventQueue.push({
                                mainProductId: item.productId,
                                mainProductName: item.productName,
                                data: productData
                            });
                        });
                    });

                    function triggerNext(index) {
                        if (index >= eventQueue.length) {
                            // 所有事件触发完成
                            console.log('✅ 所有 Pixel 事件触发完成');
                            showResults(results);
                            return;
                        }

                        let eventData = eventQueue[index];
                        let productData = eventData.data;

                        try {
                            // 生成唯一的 event_id
                            let eventId = 'catalog_' + productData.product_id + '_' + Date.now();

                            console.log('🚀 触发 Pixel 事件 [' + (index + 1) + '/' + eventQueue.length + ']:', {
                                product_id: productData.product_id,
                                product_name: productData.product_name,
                                sku_id: productData.sku_id,
                                event_id: eventId,
                                pixel_data: productData.pixel_data  // ✅ 完整的 pixel_data 对象
                            });

                            // 触发 Browser Pixel ViewContent 事件
                            ttq.track('ViewContent', productData.pixel_data, {
                                event_id: eventId
                            });

                            // 记录成功
                            results.success.push({
                                id: eventData.mainProductId,
                                name: eventData.mainProductName
                            });

                            // 更新状态
                            $('.upload-status[data-product-id="' + eventData.mainProductId + '"]')
                                .removeClass('pending error')
                                .addClass('success')
                                .text('✓ 已触发');

                            console.log('✅ Pixel 事件已触发:', productData.sku_id, eventId);

                        } catch (error) {
                            console.error('❌ Pixel 事件触发失败:', error);
                            results.error.push({
                                id: eventData.mainProductId,
                                message: 'Pixel 事件触发失败: ' + error.message
                            });

                            $('.upload-status[data-product-id="' + eventData.mainProductId + '"]')
                                .removeClass('pending success')
                                .addClass('error')
                                .text('✗ 失败');
                        }

                        completedEvents++;
                        let progress = Math.round((completedEvents / totalEvents) * 100);
                        $('#progress-bar').val(progress);
                        $('#progress-text').text('正在触发 Pixel 事件 ' + completedEvents + '/' + totalEvents);

                        // 延迟触发下一个事件（避免过快，每个事件间隔200ms）
                        setTimeout(function() {
                            triggerNext(index + 1);
                        }, 200);
                    }

                    // 开始触发第一个事件
                    triggerNext(0);
                }

                // 开始获取产品数据
                fetchProductData(0);
            }

            // 显示上传结果
            function showResults(results) {
                let html = '<div style="padding: 15px; border: 1px solid #ccc; background: #fff;">';
                html += '<h3>✅ Browser Pixel 事件触发完成</h3>';
                
                if (results.success.length > 0) {
                    html += '<p style="color: #46b450;"><strong>✓ 成功: ' + results.success.length + ' 个产品</strong></p>';
                    html += '<div style="background: #e7f3ff; padding: 10px; border-radius: 3px; margin: 10px 0;">';
                    html += '<strong>📌 下一步：</strong><br>';
                    html += '• 产品信息将在 <strong>15 分钟内</strong>自动同步到 TikTok Catalog<br>';
                    html += '• 请前往 TikTok Ads Manager → Catalog 查看更新状态<br>';
                    html += '• 可以使用 TikTok Pixel Helper 浏览器插件验证事件是否成功触发<br>';
                    html += '• 如果是变体产品，所有变体都已被触发';
                    html += '</div>';
                    html += '<div style="background: #fff3cd; padding: 10px; border-radius: 3px; margin: 10px 0; border-left: 3px solid #ffc107;">';
                    html += '<strong>💡 提示：</strong><br>';
                    html += '• 首次配置 Pixel Upload 时，需要在 TikTok Ads Manager 中设置 Catalog 连接<br>';
                    html += '• 路径：Catalog → Add Products → Pixel Upload → 选择您的 Pixel<br>';
                    html += '• 添加信任的网站域名（Trusted Websites）';
                    html += '</div>';
                }
                
                if (results.error.length > 0) {
                    html += '<p style="color: #dc3232;"><strong>✗ 失败: ' + results.error.length + ' 个产品</strong></p>';
                    html += '<ul>';
                    results.error.forEach(function(err) {
                        html += '<li>产品 ID ' + err.id + ': ' + err.message + '</li>';
                    });
                    html += '</ul>';
                }
                
                html += '</div>';
                
                $('#upload-results').html(html).show();
                $('#upload-progress').hide();
            }

            // 初始加载
            loadProducts();
        });
        </script>
        <?php
    }

    /**
     * 注入 Pixel 基础代码 (Step 1 & 2)
     */
    public function inject_base_pixel_code() {
        ?>
        <!-- TikTok Pixel Code Start -->
        <script>
        !function (w, d, t) {
          w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(
        var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script")
        ;n.type="text/javascript",n.async=!0,n.src=r+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};
          ttq.load('<?php echo esc_js(self::$pixel_id); ?>');
          ttq.page();
        }(window, document, 'ttq');
        </script>
        <!-- TikTok Pixel Code End -->
        <?php
    }

    /**
     * 注入浏览器端事件 (Step 3)
     */
    public function inject_browser_events() {
        // 1. Identify User
        $this->inject_identify_event();

        // ═══════════════════════════════════════════════════════════════════
        // 2. ViewContent Event (Browser Side - 产品页面浏览事件)
        // ═══════════════════════════════════════════════════════════════════
        // 📌 作用：
        // 1. 跟踪用户浏览产品页面的行为（用于广告优化和再营销）
        // 2. 通过 Pixel Upload 自动将产品信息同步到 TikTok Catalog
        // 3. TikTok 会从此事件中提取产品数据并更新产品目录
        // 
        // 📌 去重机制：
        // - 使用 event_id 参数（TikTok 官方推荐）
        // - 每次页面加载时都会触发事件（符合最佳实践）
        // ═══════════════════════════════════════════════════════════════════
        if (is_product()) {
            $product_id = get_queried_object_id();
            $product = wc_get_product($product_id);
            
            if ($product) :
                $catalog_data = $this->get_tiktok_catalog_data($product);
            ?>
            <script>
            // ═══════════════════════════════════════════════════════════════════
            // Browser Side: ViewContent Event
            // ═══════════════════════════════════════════════════════════════════
            // 参考文档: https://ads.tiktok.com/help/article/how-to-use-pixel-upload-with-catalogs
            // 
            // 📌 注意：ViewContent 事件在每次页面加载时都应触发
            // TikTok 自身的去重机制（event_id）会处理重复事件
            // ═══════════════════════════════════════════════════════════════════
            
            console.log('TikTok Catalog Data:', '<?php echo esc_js(json_encode($catalog_data)); ?>');
            
            // 生成唯一的 event_id（用于去重）
            var eventId = 'vc_<?php echo uniqid(); ?>_' + Date.now();
            
            console.log('TikTok: 准备发送 ViewContent 事件, event_id:', eventId);
            
            ttq.track('ViewContent', {
                // ─────────────────────────────────────────────
                // contents 数组（必需）
                // 符合官方标准格式
                // ─────────────────────────────────────────────
                "contents": [
                    {
                        "content_id": "<?php echo esc_js($catalog_data['sku_id']); ?>",
                        "content_type": "product",  // ✅ 官方必需字段
                        "content_name": "<?php echo esc_js($product->get_name()); ?>",
                        "content_category": "<?php echo esc_js($catalog_data['content_category']); ?>",
                        "price": <?php echo $product->get_price() ?: 0; ?>
                    }
                ],
                
                // ─────────────────────────────────────────────
                // 外层标准字段（符合官方 API 规范）
                // ─────────────────────────────────────────────
                "value": <?php echo $product->get_price() ?: 0; ?>,
                "currency": "<?php echo get_woocommerce_currency(); ?>",
                "description": "<?php echo esc_js($catalog_data['description']); ?>",
                
                // ─────────────────────────────────────────────
                // Catalog Upload 专用字段（用于产品同步）
                // 这些字段不在官方标准事件模板中，但用于 Pixel Upload
                // ─────────────────────────────────────────────
                "availability": "<?php echo esc_js($catalog_data['availability']); ?>",
                <?php if (isset($catalog_data['image_url'])): ?>
                "image_url": "<?php echo esc_url($catalog_data['image_url']); ?>",
                <?php endif; ?>
                "product_url": "<?php echo esc_url($catalog_data['product_url']); ?>"
            }, {
                // ✅ 官方推荐：event_id 用于去重
                "event_id": eventId
            });
            
            console.log('TikTok: ViewContent 事件已发送, event_id:', eventId);
            
            // ═══════════════════════════════════════════════════════════════════
            // AddToCart Event Listener (Browser Side - 加购事件监听器)
            // ═══════════════════════════════════════════════════════════════════
            // 📌 作用：
            // 1. 监听 WooCommerce 的 "added_to_cart" 事件
            // 2. 当用户点击"加入购物车"按钮时触发 TikTok Pixel 事件
            // 3. 支持变体产品（可选不同尺寸、颜色等）
            // 4. 用于广告优化和转化跟踪
            // 
            // 📌 防重复机制：
            // - 使用时间戳检查，避免短时间内重复触发
            // - 每次加购至少间隔 1 秒才会发送新事件
            // ═══════════════════════════════════════════════════════════════════
            jQuery(document).ready(function($) {
                var lastAddToCartTime = 0;
                
                $('body').on('added_to_cart', function(event, fragments, cart_hash, $button) {
                    // 防重复检查：1 秒内只触发一次
                    var now = Date.now();
                    if (now - lastAddToCartTime < 1000) {
                        console.log('TikTok: AddToCart 事件被防重复机制拦截');
                        return;
                    }
                    lastAddToCartTime = now;
                    
                    // 默认使用主产品数据
                    var content_id = "<?php echo esc_js($catalog_data['sku_id']); ?>";
                    var content_name = "<?php echo esc_js($product->get_name()); ?>";
                    var price = <?php echo $product->get_price() ?: 0; ?>;
                    
                    // [变体支持] 如果是变体产品，尝试获取选中的变体信息
                    var $form = $button.closest('form.cart');
                    if ($form.length === 0) $form = $('form.cart');
                    
                    var $variation_input = $form.find('input[name="variation_id"]');
                    if ($variation_input.length > 0 && $variation_input.val() && $variation_input.val() != '0') {
                        // 使用变体 ID 而不是主产品 ID
                        content_id = $variation_input.val();
                        
                        // 尝试获取变体价格（如果有）
                        var $price_input = $form.find('.woocommerce-variation-price .amount');
                        if ($price_input.length > 0) {
                            var price_text = $price_input.text().replace(/[^\d.]/g, '');
                            if (price_text) price = parseFloat(price_text);
                        }
                    }

                    // 生成唯一的 event_id
                    var eventId = 'atc_' + content_id + '_' + now;

                    // Browser Side: AddToCart Event
                    ttq.track('AddToCart', {
                        "contents": [
                            {
                                "content_id": content_id,
                                "content_type": "product",  // ✅ 官方必需字段
                                "content_name": content_name,
                                "content_category": "<?php echo esc_js($catalog_data['content_category']); ?>",
                                "price": price
                            }
                        ],
                        "value": price,
                        "currency": "<?php echo get_woocommerce_currency(); ?>",
                        "description": "<?php echo esc_js($catalog_data['description']); ?>",
                        
                        // Catalog Upload 专用字段
                        "availability": "<?php echo esc_js($catalog_data['availability']); ?>",
                        <?php if (isset($catalog_data['image_url'])): ?>
                        "image_url": "<?php echo esc_url($catalog_data['image_url']); ?>",
                        <?php endif; ?>
                        "product_url": "<?php echo esc_url($catalog_data['product_url']); ?>"
                    }, {
                        // ✅ 官方推荐：event_id 用于去重
                        "event_id": eventId
                    });
                });
            });
            </script>
            <?php
            endif;
        }

        // 3. Search
        if (is_search()) {
            ?>
            <script>
            ttq.track('Search', {
                "contents": [],
                "search_string": "<?php echo esc_js(get_search_query()); ?>",
                "currency": "<?php echo get_woocommerce_currency(); ?>"
            }, {
                "event_id": "search_<?php echo uniqid(); ?>_" + Date.now()
            });
            </script>
            <?php
        }

        // 4. InitiateCheckout (Checkout Page)
        if (is_checkout() && !is_order_received_page()) {
            $cart = WC()->cart;
            if ($cart) {
                $contents = [];
                foreach ($cart->get_cart() as $cart_item) {
                    $product = $cart_item['data'];
                    // 优先使用变体 ID，如果不存在则使用产品 ID
                    $id_to_use = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : $cart_item['product_id'];
                    
                    $contents[] = [
                        "content_id" => (string)$id_to_use,
                        "content_type" => "product",
                        "content_name" => $product->get_name()
                    ];
                }
                ?>
                <script>
                // 生成唯一的 event_id
                var checkoutEventId = 'ic_<?php echo uniqid(); ?>_' + Date.now();
                
                ttq.track('InitiateCheckout', {
                    "contents": <?php echo json_encode($contents); ?>,
                    "value": <?php echo $cart->get_total('float'); ?>,
                    "currency": "<?php echo get_woocommerce_currency(); ?>"
                }, {
                    "event_id": checkoutEventId
                });

                // AddPaymentInfo Trigger (on Place Order click)
                jQuery(document).ready(function($) {
                    $('form.checkout').on('checkout_place_order', function() {
                        var paymentEventId = 'api_<?php echo uniqid(); ?>_' + Date.now();
                        
                        ttq.track('AddPaymentInfo', {
                            "contents": <?php echo json_encode($contents); ?>,
                            "value": <?php echo $cart->get_total('float'); ?>,
                            "currency": "<?php echo get_woocommerce_currency(); ?>"
                        }, {
                            "event_id": paymentEventId
                        });
                    });
                });
                </script>
                <?php
            }
        }

        // 5. Purchase (Order Received Page)
        if (is_order_received_page()) {
            global $wp;
            $order_id = isset($wp->query_vars['order-received']) ? $wp->query_vars['order-received'] : 0;
            if ($order_id) {
                $order = wc_get_order($order_id);
                if ($order) {
                    $contents = [];
                    foreach ($order->get_items() as $item) {
                        // 优先使用变体 ID
                        $id_to_use = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
                        
                        $contents[] = [
                            "content_id" => (string)$id_to_use,
                            "content_type" => "product",
                            "content_name" => $item->get_name()
                        ];
                    }
                    ?>
                    <script>
                    ttq.track('Purchase', {
                        "contents": <?php echo json_encode($contents); ?>,
                        "value": <?php echo $order->get_total(); ?>,
                        "currency": "<?php echo $order->get_currency(); ?>"
                    }, {
                        "event_id": "purchase_<?php echo $order_id; ?>"  // ✅ 使用字符串格式
                    });
                    </script>
                    <?php
                }
            }
        }
    }

    /**
     * 注入 Identify 事件
     */
    private function inject_identify_event() {
        if (!is_user_logged_in()) {
            return;
        }
        
        $current_user = wp_get_current_user();
        $email = $current_user->user_email;
        $user_id = $current_user->ID;
        
        // SHA-256 Hashing
        $hashed_email = hash('sha256', strtolower(trim($email)));
        $hashed_external_id = hash('sha256', (string)$user_id);
        $hashed_phone = hash('sha256', (string)$current_user->user_phone);
        // 尝试获取手机号（WordPress 默认没有 user_phone 字段）
        $phone = get_user_meta($user_id, 'billing_phone', true);
        $identify_data = [
            "email" => $hashed_email,
            "external_id" => $hashed_external_id
        ];
        
        // 只有在手机号存在时才添加（符合官方要求）
        if (!empty($phone)) {
            // 移除所有非数字字符后再哈希
            $phone_digits = preg_replace('/[^0-9]/', '', $phone);
            if (!empty($phone_digits)) {
                $identify_data["phone_number"] = hash('sha256', $phone_digits);
            }
        }
        ?>
        <script>
        ttq.identify(<?php echo json_encode($identify_data, JSON_UNESCAPED_UNICODE); ?>);
        </script>
        <?php
    }

    // ═══════════════════════════════════════════════════════════════════
    // SERVER SIDE EVENTS (服务器端事件)
    // ═══════════════════════════════════════════════════════════════════
    // 📌 Server Side Events 的作用：
    // 1. 补充 Browser Side Pixel 的数据（双重跟踪，提高数据准确性）
    // 2. 解决浏览器端被广告拦截器屏蔽的问题
    // 3. 通过 Event ID 去重，避免重复计数
    // 4. 提供更可靠的转化跟踪数据
    // 
    // 📌 与 Browser Side 的区别：
    // - Browser Side: 实时性好，用于 Pixel Upload 同步 Catalog
    // - Server Side: 可靠性高，用于补充和验证数据
    // ═══════════════════════════════════════════════════════════════════

    /**
     * ═══════════════════════════════════════════════════════════════════
     * Server Side: ViewContent Event
     * ═══════════════════════════════════════════════════════════════════
     * 📌 触发时机：用户访问产品页面时
     * 📌 作用：
     * 1. 补充 Browser Side 的 ViewContent 事件数据
     * 2. 通过 Event ID 与 Browser Side 事件去重
     * 3. 提供更可靠的浏览数据（不受广告拦截器影响）
     * 📌 注意：此方法已被禁用，避免与浏览器端事件重复
     * - Browser Pixel 已经在产品页发送 ViewContent
     * - 服务器端重复发送会导致数据重复统计
     * - 如需启用，必须确保与浏览器端使用相同的 event_id 去重
     * ═══════════════════════════════════════════════════════════════════
     */
    public function track_server_view_content() {
        // ⚠️ 已禁用服务器端 ViewContent，避免与浏览器端重复
        // 原因：TikTok Pixel Helper 显示同一页面有多个 ViewContent 事件
        // 解决方案：只使用浏览器端 Pixel Upload 来跟踪 ViewContent
        return;
        
        if (!is_product()) return;
        
        $product_id = get_queried_object_id();
        $product = wc_get_product($product_id);
        
        if (!$product) return;

        // 生成唯一的 Event ID（用于与 Browser Side 事件去重）
        $event_id = uniqid('vc_');

        // 获取 Catalog 数据
        $catalog_data = $this->get_tiktok_catalog_data($product);

        // 构建事件参数（遵循 TikTok Events API 规范）
        $properties = [
            // contents 数组（产品基本信息）
            'contents' => [
                [
                    'content_id' => $catalog_data['sku_id'],  // 使用 SKU ID
                    'content_name' => $product->get_name()
                ]
            ],
            
            // 外层必需字段（Catalog Upload Required Fields）
            'price' => (float)$product->get_price(),
            'currency' => get_woocommerce_currency(),
            'value' => (float)$product->get_price(),
            'description' => $catalog_data['description'],
            'availability' => $catalog_data['availability'],
            'product_url' => $catalog_data['product_url'],
            
            // 可选字段
            'content_type' => 'product',
            'content_category' => $catalog_data['content_category']
        ];
        
        // 添加图片 URL（如果存在）
        if (isset($catalog_data['image_url'])) {
            $properties['image_url'] = $catalog_data['image_url'];
        }

        // 发送到 TikTok Events API
        $this->send_server_event('ViewContent', $properties, [], $event_id);
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * Server Side: AddToCart Event
     * ═══════════════════════════════════════════════════════════════════
     * 📌 触发时机：用户点击"加入购物车"按钮后（WooCommerce Hook）
     * 📌 作用：
     * 1. 补充 Browser Side 的 AddToCart 事件
     * 2. 确保加购事件被准确记录（即使前端被拦截）
     * 3. 用于广告优化和再营销
     * ═══════════════════════════════════════════════════════════════════
     */
    public function track_server_add_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        $product = wc_get_product($variation_id ? $variation_id : $product_id);
        if (!$product) return;
        
        $event_id = uniqid('atc_'); 

        // 获取 Catalog 数据
        $catalog_data = $this->get_tiktok_catalog_data($product);

        // 构建事件参数
        $properties = [
            'contents' => [
                [
                    'content_id' => $catalog_data['sku_id'],
                    'content_name' => $product->get_name()
                ]
            ],
            
            // 外层必需字段
            'price' => (float)$product->get_price(),
            'currency' => get_woocommerce_currency(),
            'value' => (float)($product->get_price() * $quantity),
            'quantity' => (int)$quantity,
            'description' => $catalog_data['description'],
            'availability' => $catalog_data['availability'],
            'product_url' => $catalog_data['product_url'],
            
            // 可选字段
            'content_type' => 'product',
            'content_category' => $catalog_data['content_category']
        ];
        
        // 添加图片 URL（如果存在）
        if (isset($catalog_data['image_url'])) {
            $properties['image_url'] = $catalog_data['image_url'];
        }

        // 发送到 TikTok Events API
        $this->send_server_event('AddToCart', $properties, [], $event_id);
    }

    /**
     * Server Side: InitiateCheckout
     */
    public function track_server_initiate_checkout() {
        if (!is_checkout() || is_order_received_page()) return;

        $cart = WC()->cart;
        if (!$cart) return;

        $contents = [];
        foreach ($cart->get_cart() as $cart_item) {
            // 优先使用变体 ID
            $id_to_use = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : $cart_item['product_id'];
            
            $contents[] = [
                'content_id' => (string)$id_to_use,
                'content_type' => 'product',
                'content_name' => $cart_item['data']->get_name(),
                'quantity' => $cart_item['quantity'],
                'price' => $cart_item['data']->get_price()
            ];
        }

        $properties = [
            'contents' => $contents,
            'value' => $cart->get_total('float'),
            'currency' => get_woocommerce_currency()
        ];

        $this->send_server_event('InitiateCheckout', $properties);
    }

    /**
     * Server Side: AddPaymentInfo
     */
    public function track_server_add_payment_info($order_id) {
        if (!$order_id) return;
        $order = wc_get_order($order_id);
        if (!$order) return;

        // 避免重复发送
        if (get_post_meta($order_id, '_tiktok_add_payment_info_sent', true)) {
            return;
        }

        $contents = [];
        foreach ($order->get_items() as $item) {
            // 优先使用变体 ID
            $id_to_use = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
            
            $contents[] = [
                'content_id' => (string)$id_to_use,
                'content_type' => 'product',
                'content_name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'price' => $item->get_total()
            ];
        }

        $user_data = [
            'email' => $order->get_billing_email(),
            'phone' => $order->get_billing_phone(),
            'external_id' => (string)$order->get_user_id()
        ];

        $properties = [
            'contents' => $contents,
            'value' => $order->get_total(),
            'currency' => $order->get_currency(),
            'order_id' => (string)$order_id
        ];

        // 使用 order_id 前缀作为 event_id 确保唯一性
        $event_id = 'api_' . $order_id;

        $this->send_server_event('AddPaymentInfo', $properties, $user_data, $event_id);
        
        update_post_meta($order_id, '_tiktok_add_payment_info_sent', 'yes');
    }

    /**
     * Server Side: Purchase
     */
    public function track_server_purchase($order_id) {
        if (!$order_id) return;
        $order = wc_get_order($order_id);
        if (!$order) return;

        if (get_post_meta($order_id, '_tiktok_purchase_sent', true)) {
            return;
        }

        $contents = [];
        foreach ($order->get_items() as $item) {
            // 优先使用变体 ID
            $id_to_use = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
            
            $contents[] = [
                'content_id' => (string)$id_to_use,
                'content_type' => 'product',
                'content_name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'price' => $item->get_total()
            ];
        }

        $user_data = [
            'email' => $order->get_billing_email(),
            'phone' => $order->get_billing_phone(),
            'external_id' => (string)$order->get_user_id()
        ];

        $properties = [
            'contents' => $contents,
            'value' => $order->get_total(),
            'currency' => $order->get_currency(),
            'order_id' => (string)$order_id
        ];

        $this->send_server_event('Purchase', $properties, $user_data, (string)$order_id);
        
        update_post_meta($order_id, '_tiktok_purchase_sent', 'yes');
    }

    /**
     * Server Side: Registration
     */
    public function track_server_registration($user_id) {
        $user = get_userdata($user_id);
        $user_data = [
            'email' => $user->user_email,
            'external_id' => (string)$user_id
        ];
        
        $this->send_server_event('CompleteRegistration', ['method' => 'website'], $user_data);
    }

    /**
     * 发送 API 请求核心方法
     */
    private function send_server_event($event_name, $properties = [], $user_data = [], $event_id = null) {
        if (empty(self::$access_token)) return;

        $customer_info = $this->get_customer_info($user_data);
        $event_id = $event_id ?: wp_generate_uuid4();

        // 构造单条事件数据
        $protocol = is_ssl() ? 'https://' : 'http://';
        // 使用 esc_url_raw 清洗 URL，防止恶意字符注入
        $current_url = esc_url_raw($protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

        $event_data = [
            'event' => $event_name,
            'event_id' => $event_id,
            'event_time' => current_time('timestamp'), // TikTok 推荐用 event_time (unix timestamp)
            'user' => [
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
            ],
            'page' => [
                'url' => $current_url
            ],
            'properties' => $properties
        ];

        // Add User Data (Hashed)
        if (isset($customer_info['email'])) {
            $event_data['user']['email'] = hash('sha256', strtolower(trim($customer_info['email'])));
        }
        if (isset($customer_info['phone'])) {
            $event_data['user']['phone'] = hash('sha256', preg_replace('/[^0-9]/', '', $customer_info['phone']));
        }
        if (isset($customer_info['external_id'])) {
            $event_data['user']['external_id'] = hash('sha256', $customer_info['external_id']);
        }

        // 构造最终 Payload (标准结构)
        $payload = [
            'event_source' => 'web',
            'event_source_id' => self::$pixel_id,
            'data' => [$event_data]
        ];

        // Test Event Code (放在顶层)
        if (self::$test_mode === 'test' && !empty(self::$test_event_code)) {
            $payload['test_event_code'] = self::$test_event_code;
        }
        
        // 记录请求日志 (Request)
        $this->log_api_call('POST', $event_name, $payload);

        // 发送请求
        $response = wp_remote_post(self::$api_endpoint, [
            'headers' => [
                'Access-Token' => self::$access_token,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'blocking' => true,
            'timeout' => 5
        ]);

        // 记录响应日志 (Response)
        $this->log_api_response($event_name, $response);
    }

    private function log_api_call($method, $event, $request_body) {
        $logs = get_option('evershop_tiktok_logs', []);
        
        // 限制日志数量，保留最近 50 条
        if (count($logs) > 50) {
            $logs = array_slice($logs, -50);
        }

        $logs[] = [
            'time' => current_time('mysql'),
            'method' => $method, // 新增 Method
            'type' => 'request',
            'event' => $event,
            'request_body' => $request_body,
            'response_code' => 'Pending...',
            'response_body' => ''
        ];

        update_option('evershop_tiktok_logs', $logs);
    }

    private function log_api_response($event, $response) {
        $logs = get_option('evershop_tiktok_logs', []);
        if (empty($logs)) return;

        // 获取最后一条日志（假设是刚才那条，单线程下通常没问题，并发高可能需要更严谨的 ID 匹配）
        $last_key = array_key_last($logs);
        
        if (is_wp_error($response)) {
            $logs[$last_key]['response_code'] = 'WP Error';
            $logs[$last_key]['response_body'] = $response->get_error_message();
        } else {
            $logs[$last_key]['response_code'] = wp_remote_retrieve_response_code($response);
            $logs[$last_key]['response_body'] = json_decode(wp_remote_retrieve_body($response), true);
        }

        update_option('evershop_tiktok_logs', $logs);
    }

    private function get_customer_info($overrides = []) {
        if (!empty($overrides)) return $overrides;
        
        $info = [];
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $info['email'] = $current_user->user_email;
            $info['external_id'] = (string)$current_user->ID;
        }
        return $info;
    }

    /**
     * ═══════════════════════════════════════════════════════════════════
     * 获取符合 TikTok Catalog 要求的产品数据
     * ═══════════════════════════════════════════════════════════════════
     * 📌 参考文档：
     * https://ads.tiktok.com/help/article/how-to-use-pixel-upload-with-catalogs
     * 
     * 📌 字段映射（Catalog Parameter → Pixel Parameter）：
     * - sku_id         → content_id
     * - title          → content_name  
     * - price          → price (number 类型)
     * - description    → description
     * - availability   → availability
     * - image          → image_url
     * - link           → product_url
     * 
     * 📌 返回的数据将用于：
     * 1. Browser Side Pixel Upload（自动同步到 TikTok Catalog）
     * 2. Server Side Events API（补充数据）
     * ═══════════════════════════════════════════════════════════════════
     */
    private function get_tiktok_catalog_data($product) {
        $data = [];
        
        // ─────────────────────────────────────────────
        // SKU ID (必需字段)
        // Catalog: sku_id | Pixel: content_id
        // ─────────────────────────────────────────────
        $sku = $product->get_sku();
        if (empty($sku)) {
            // 如果没有 SKU，使用 "product_{ID}" 作为备用
            $sku = 'product_' . $product->get_id();
        }
        $data['sku_id'] = $sku;
        
        // ─────────────────────────────────────────────
        // Description (必需字段)
        // Catalog: description | Pixel: description
        // ─────────────────────────────────────────────
        $description = $product->get_short_description();
        if (empty($description)) {
            $description = $product->get_description();
        }
        // 截取适度长度，去除HTML标签
        $data['description'] = mb_substr(wp_strip_all_tags($description), 0, 500); 
        if (empty($data['description'])) {
            // 确保 description 不为空（必需字段）
            $data['description'] = $product->get_name();
        }
        
        // ─────────────────────────────────────────────
        // Availability (必需字段)
        // Catalog: availability | Pixel: availability
        // 支持的值: "in stock", "available for order", "preorder", "out of stock", "discontinued"
        // ─────────────────────────────────────────────
        $stock_status = $product->get_stock_status(); // WC: instock, outofstock, onbackorder
        switch ($stock_status) {
            case 'instock':
                $data['availability'] = 'in stock';
                break;
            case 'outofstock':
                $data['availability'] = 'out of stock';
                break;
            case 'onbackorder':
                $data['availability'] = 'preorder';
                break;
            default:
                $data['availability'] = 'in stock';
        }
        
        // ─────────────────────────────────────────────
        // Image Link (必需字段)
        // Catalog 字段名: image | Pixel 参数名: image_url
        // 要求：≥500x500 像素，JPG 或 PNG 格式
        // ⚠️ 注意：存储时使用 Pixel 参数名 image_url，方便直接传递给事件
        // ─────────────────────────────────────────────
        $image_id = $product->get_image_id();
        if ($image_id) {
            $image_url = wp_get_attachment_url($image_id);
            if ($image_url && filter_var($image_url, FILTER_VALIDATE_URL)) {
                $data['image_url'] = $image_url;  // ✅ 使用 Pixel 参数名
            }
        }
        // 注意：如果没有图片，不设置此字段（而不是设置空字符串）
        
        // ─────────────────────────────────────────────
        // Product URL (必需字段)
        // Catalog 字段名: link | Pixel 参数名: product_url
        // ⚠️ 注意：存储时使用 Pixel 参数名 product_url，方便直接传递给事件
        // ─────────────────────────────────────────────
        $data['product_url'] = $product->get_permalink();  // ✅ 使用 Pixel 参数名
        
        // ─────────────────────────────────────────────
        // Content Category (可选字段)
        // Pixel: content_category
        // 用途：帮助 TikTok 更好地分类和定位广告
        // ─────────────────────────────────────────────
        $data['content_category'] = 'Health > Vitamins & Supplements > Sports Nutrition';
        /*
        // 如果需要动态获取分类，可以使用以下代码：
        $data['content_category'] = '';
        $category_ids = $product->get_category_ids();
        if (!empty($category_ids)) {
            $term = get_term($category_ids[0], 'product_cat');
            if ($term && !is_wp_error($term)) {
                $data['content_category'] = $term->name;
            }
        }
        */

        // ─────────────────────────────────────────────
        // Brand (可选字段，但推荐提供)
        // Pixel: brand (虽然表格中未列出，但 TikTok 支持此字段)
        // ─────────────────────────────────────────────
        $data['brand'] = ''; 
        // 尝试获取品牌属性
        $brand = $product->get_attribute('brand');
        if (empty($brand)) {
            $brand = $product->get_attribute('pa_brand');
        }
        if (!empty($brand)) {
            $data['brand'] = $brand;
        } else {
            // 如果没有品牌属性，使用站点名称作为默认值
            $data['brand'] = get_bloginfo('name');
        }

        // ─────────────────────────────────────────────
        // Condition (可选字段)
        // Catalog: condition
        // 支持的值: "new", "refurbished", "used"
        // ─────────────────────────────────────────────
        $data['condition'] = 'new';
        
        return $data;
    }

    /**
     * AJAX: 获取产品列表
     */
    public function ajax_get_products() {
        try {
            $products = [];
            
            // 仅获取已发布的产品
            $args = [
                'post_type' => 'product',
                'posts_per_page' => -1,
                'post_status' => 'publish'  // ✅ 只获取已发布的产品
            ];
            
            $query = new WP_Query($args);
            
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $product_id = get_the_ID();
                    $product = wc_get_product($product_id);
                    
                    // 跳过无效产品
                    if (!$product) continue;
                    
                    // 再次确认产品状态为已发布
                    if ($product->get_status() !== 'publish') continue;
                    
                    $product_data = [
                        'id' => $product_id,
                        'name' => $product->get_name(),
                        'sku' => $product->get_sku() ?: 'product_' . $product_id,
                        'type' => $product->get_type(),
                        'price' => wc_price($product->get_price()),
                        'stock' => $product->get_stock_status() === 'instock' ? '有货' : '缺货',
                        'image' => wp_get_attachment_url($product->get_image_id()) ?: wc_placeholder_img_src()
                    ];
                    
                    // 如果是变体产品，获取所有已发布的变体
                    if ($product->is_type('variable')) {
                        $variations = $product->get_available_variations();
                        $product_data['variations'] = [];
                        
                        foreach ($variations as $variation) {
                            $variation_obj = wc_get_product($variation['variation_id']);
                            
                            // 跳过无效或未发布的变体
                            if (!$variation_obj) continue;
                            if ($variation_obj->get_status() !== 'publish') continue;
                            
                            $product_data['variations'][] = [
                                'id' => $variation['variation_id'],
                                'name' => implode(', ', $variation['attributes']),
                                'sku' => $variation_obj->get_sku() ?: 'variation_' . $variation['variation_id'],
                                'price' => $variation_obj->get_price()
                            ];
                        }
                    }
                    
                    $products[] = $product_data;
                }
                wp_reset_postdata();
            }
            
            wp_send_json_success($products);
            
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: 获取产品的 Catalog 数据（用于前端 Browser Pixel Upload）
     * 仅处理已发布的产品
     */
    public function ajax_upload_to_catalog() {
        try {
            $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
            
            if (!$product_id) {
                throw new Exception('无效的产品 ID');
            }
            
            $product = wc_get_product($product_id);
            
            if (!$product) {
                throw new Exception('产品不存在');
            }
            
            // ✅ 确保产品已发布
            if ($product->get_status() !== 'publish') {
                throw new Exception('该产品未发布，无法上传到 Catalog');
            }
            
            $products_data = [];
            
            // 判断是否为变体产品
            if ($product->is_type('variable')) {
                // 变体产品：获取所有已发布的变体的数据
                $variations = $product->get_available_variations();
                
                foreach ($variations as $variation) {
                    $variation_obj = wc_get_product($variation['variation_id']);
                    
                    // ✅ 跳过无效或未发布的变体
                    if (!$variation_obj) continue;
                    if ($variation_obj->get_status() !== 'publish') continue;
                    
                    $products_data[] = $this->get_product_catalog_data_for_browser($variation_obj);
                }
                
                // 也获取主产品数据（作为产品组）
                $products_data[] = $this->get_product_catalog_data_for_browser($product);
                
            } else {
                // 简单产品：直接获取数据
                $products_data[] = $this->get_product_catalog_data_for_browser($product);
            }
            
            // 检查是否有有效的产品数据
            if (empty($products_data)) {
                throw new Exception('没有可用的已发布产品数据');
            }
            
            wp_send_json_success([
                'product_name' => $product->get_name(),
                'products_count' => count($products_data),
                'products_data' => $products_data
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * 获取产品的 Catalog 数据（用于 Browser Pixel）
     * 返回符合 TikTok Pixel Upload 要求的数据格式
     * 
     * 根据官方文档: https://ads.tiktok.com/help/article/how-to-use-pixel-upload-with-catalogs
     * 必填字段映射：
     * - sku_id → content_id (在 contents 数组中)
     * - title → content_name (在 contents 数组中)
     * - price → price (在 contents 数组中，number 类型)
     * - description → description (外层字段)
     * - availability → availability (外层字段)
     * - image → image_url (外层字段)
     * - link → product_url (外层字段)
     * - currency → currency (外层字段)
     * - value → value (外层字段，number 类型)
     */
    private function get_product_catalog_data_for_browser($product) {
        $catalog_data = $this->get_tiktok_catalog_data($product);
        
        // 构建 Browser Pixel 事件数据（严格按照官方文档）
        $pixel_data = [
            // ═══════════════════════════════════════════════════════════
            // contents 数组（必需）- Either contents or content_id is required
            // ═══════════════════════════════════════════════════════════
            'contents' => [
                [
                    // ✅ sku_id → content_id (Required)
                    'content_id' => $catalog_data['sku_id'],
                    
                    // ✅ title → content_name (Required, string)
                    'content_name' => $product->get_name(),
                    
                    // ✅ price → price (Required, number)
                    'price' => (float)$product->get_price(),
                    
                    // 可选：content_type (Optional, Must be either product or product_group)
                    'content_type' => 'product',
                    
                    // 可选：content_category (Optional, string)
                    'content_category' => $catalog_data['content_category']
                ]
            ],
            
            // ═══════════════════════════════════════════════════════════
            // 外层必需字段（根据官方表格）
            // ═══════════════════════════════════════════════════════════
            
            // ✅ description → description (Required)
            'description' => $catalog_data['description'],
            
            // ✅ availability → availability (Required)
            // 支持的值: in stock, available for order, preorder, out of stock, discontinued
            'availability' => $catalog_data['availability'],
            
            // ✅ link → product_url (Required)
            'product_url' => $catalog_data['product_url'],
            
            // ✅ currency → currency (Required, enum(string))
            'currency' => get_woocommerce_currency(),
            
            // ✅ value → value (Required, number) - The total price of the order
            'value' => (float)$product->get_price(),
            
            // ═══════════════════════════════════════════════════════════
            // 可选字段（根据官方表格）
            // ═══════════════════════════════════════════════════════════
            
            // quantity (Optional, number)
            'quantity' => 1,
            
            // content_type (Optional, Must be either product or product_group)
            'content_type' => 'product',
            
            // content_category (Optional, string)
            'content_category' => $catalog_data['content_category']
        ];
        
        // ✅ image → image_url (Required, ≥500x500, JPG or PNG)
        if (isset($catalog_data['image_url'])) {
            $pixel_data['image_url'] = $catalog_data['image_url'];
        } else {
            // 如果没有图片，使用占位图（确保必填字段不为空）
            $pixel_data['image_url'] = wc_placeholder_img_src();
        }
        
        // 如果是变体产品，添加 item_group_id 和特殊处理
        if ($product->is_type('variation')) {
            $parent = wc_get_product($product->get_parent_id());
            if ($parent) {
                // 变体产品关联到主产品
                $pixel_data['item_group_id'] = $parent->get_sku() ?: 'product_' . $parent->get_id();
                
                // 变体产品的 content_type 应该是 product（不是 product_group）
                $pixel_data['content_type'] = 'product';
                $pixel_data['contents'][0]['content_type'] = 'product';
            }
        }
        
        return [
            'product_id' => $product->get_id(),
            'product_name' => $product->get_name(),
            'sku_id' => $catalog_data['sku_id'],
            'pixel_data' => $pixel_data
        ];
    }

}
