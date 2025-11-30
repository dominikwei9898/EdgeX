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
        // ═══════════════════════════════════════════════════════════════════
        if (is_product()) {
            $product_id = get_queried_object_id();
            $product = wc_get_product($product_id);
            
            if ($product) :
                $catalog_data = $this->get_tiktok_catalog_data($product);
            ?>
            <script>
            // Browser Side: ViewContent Event
            // 参考文档: https://ads.tiktok.com/help/article/how-to-use-pixel-upload-with-catalogs
            ttq.track('ViewContent', {
                // ─────────────────────────────────────────────
                // contents 数组（必需）
                // 用途：包含产品的基本标识信息
                // ─────────────────────────────────────────────
                "contents": [
                    {
                        // Catalog: sku_id | Pixel: content_id
                        // 产品唯一标识符（SKU 或产品 ID）
                        "content_id": "<?php echo esc_js($catalog_data['sku_id']); ?>",
                        
                        // Catalog: title | Pixel: content_name
                        // 产品名称
                        "content_name": "<?php echo esc_js($product->get_name()); ?>"
                    }
                ],
                
                // ─────────────────────────────────────────────
                // 外层必需字段（Required for Catalog Upload）
                // 用途：TikTok 从这些字段中提取数据并同步到 Catalog
                // ─────────────────────────────────────────────
                
                // Pixel: price (必需 - number 类型)
                // 产品单价（用于 Catalog 和事件跟踪）
                "price": <?php echo $product->get_price() ?: 0; ?>,
                
                // Pixel: currency (必需)
                // 货币代码（ISO 4217 标准，如 USD, EUR）
                "currency": "<?php echo get_woocommerce_currency(); ?>",
                
                // Pixel: value (必需)
                // 订单总价值（对于单品浏览，等于 price）
                "value": <?php echo $product->get_price() ?: 0; ?>,
                
                // Catalog: description | Pixel: description (必需)
                // 产品描述
                "description": "<?php echo esc_js($catalog_data['description']); ?>",
                
                // Catalog: availability | Pixel: availability (必需)
                // 库存状态: "in stock", "available for order", "preorder", "out of stock", "discontinued"
                "availability": "<?php echo esc_js($catalog_data['availability']); ?>",
                
                <?php if (isset($catalog_data['image_url'])): ?>
                // Catalog: image | Pixel: image_url (必需)
                // 产品图片 URL（用于广告创意生成）
                "image_url": "<?php echo esc_url($catalog_data['image_url']); ?>",
                <?php endif; ?>
                
                // Catalog: link | Pixel: product_url (必需)
                // 产品落地页 URL
                "product_url": "<?php echo esc_url($catalog_data['product_url']); ?>",
                
                // ─────────────────────────────────────────────
                // 可选字段（Optional but Recommended）
                // ─────────────────────────────────────────────
                
                // Pixel: content_type (可选)
                // 内容类型: "product" 或 "product_group"
                "content_type": "product",
                
                // Pixel: content_category (可选)
                // 产品类别（用于广告定位优化）
                "content_category": "<?php echo esc_js($catalog_data['content_category']); ?>"
            });
            
            // ═══════════════════════════════════════════════════════════════════
            // AddToCart Event Listener (Browser Side - 加购事件监听器)
            // ═══════════════════════════════════════════════════════════════════
            // 📌 作用：
            // 1. 监听 WooCommerce 的 "added_to_cart" 事件
            // 2. 当用户点击"加入购物车"按钮时触发 TikTok Pixel 事件
            // 3. 支持变体产品（可选不同尺寸、颜色等）
            // 4. 用于广告优化和转化跟踪
            // ═══════════════════════════════════════════════════════════════════
            jQuery(document).ready(function($) {
                $('body').on('added_to_cart', function(event, fragments, cart_hash, $button) {
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

                    // Browser Side: AddToCart Event
                    ttq.track('AddToCart', {
                        // ─────────────────────────────────────────────
                        // contents 数组（必需）
                        // ─────────────────────────────────────────────
                        "contents": [
                            {
                                "content_id": content_id,
                                "content_name": content_name
                            }
                        ],
                        
                        // ─────────────────────────────────────────────
                        // 外层必需字段（Required for Catalog Upload）
                        // ─────────────────────────────────────────────
                        "price": price,
                        "currency": "<?php echo get_woocommerce_currency(); ?>",
                        "value": price,
                        "description": "<?php echo esc_js($catalog_data['description']); ?>",
                        "availability": "<?php echo esc_js($catalog_data['availability']); ?>",
                        <?php if (isset($catalog_data['image_url'])): ?>
                        "image_url": "<?php echo esc_url($catalog_data['image_url']); ?>",
                        <?php endif; ?>
                        "product_url": "<?php echo esc_url($catalog_data['product_url']); ?>",
                        
                        // 可选字段
                        "content_type": "product",
                        "content_category": "<?php echo esc_js($catalog_data['content_category']); ?>"
                    });
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
                ttq.track('InitiateCheckout', {
                    "contents": <?php echo json_encode($contents); ?>,
                    "value": <?php echo $cart->get_total('float'); ?>,
                    "currency": "<?php echo get_woocommerce_currency(); ?>"
                });

                // AddPaymentInfo Trigger (on Place Order click)
                jQuery(document).ready(function($) {
                    $('form.checkout').on('checkout_place_order', function() {
                        ttq.track('AddPaymentInfo', {
                            "contents": <?php echo json_encode($contents); ?>,
                            "value": <?php echo $cart->get_total('float'); ?>,
                            "currency": "<?php echo get_woocommerce_currency(); ?>"
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
                        event_id: "<?php echo $order_id; ?>"
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
        
        ?>
        <script>
        ttq.identify({
            "email": "<?php echo $hashed_email; ?>",
            "external_id": "<?php echo $hashed_external_id; ?>"
        });
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
     * ═══════════════════════════════════════════════════════════════════
     */
    public function track_server_view_content() {
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
}
