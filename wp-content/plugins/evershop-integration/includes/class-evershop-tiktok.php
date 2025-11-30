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

        // 2. ViewContent (Product Page)
        if (is_product()) {
            $product_id = get_queried_object_id();
            $product = wc_get_product($product_id);
            
            if ($product) :
                $catalog_data = $this->get_tiktok_catalog_data($product);
            ?>
            <script>
            ttq.track('ViewContent', {
                "contents": [
                    {
                        "content_id": "<?php echo $product->get_id(); ?>",
                        "content_type": "product",
                        "content_name": "<?php echo esc_js($product->get_name()); ?>"
                    }
                ],
                "value": <?php echo $product->get_price() ?: 0; ?>,
                "currency": "<?php echo get_woocommerce_currency(); ?>",
                "description": "<?php echo esc_js($catalog_data['description']); ?>",
                "availability": "<?php echo esc_js($catalog_data['availability']); ?>",
                "image_url": "<?php echo esc_url($catalog_data['image_url']); ?>",
                "product_url": "<?php echo esc_url($catalog_data['product_url']); ?>",
                "price": "<?php echo ($product->get_price() ?: 0) . ' ' . get_woocommerce_currency(); ?>",
                "content_category": "Health > Vitamins & Supplements > Sports Nutrition",
                "brand": "<?php echo esc_js($catalog_data['brand']); ?>",
                "condition": "<?php echo esc_js($catalog_data['condition']); ?>"
            });
            
            // 额外添加 AddToCart 监听器 (Browser Side)
            jQuery(document).ready(function($) {
                $('body').on('added_to_cart', function(event, fragments, cart_hash, $button) {
                    // 默认基础数据
                    var content_id = "<?php echo $product->get_id(); ?>";
                    var content_name = "<?php echo esc_js($product->get_name()); ?>";
                    var price = <?php echo $product->get_price(); ?>;
                    
                    // [变体支持] 尝试获取当前表单选中的变体 ID
                    // 1. 检查是否有 variation_id 输入框且有值
                    var $form = $button.closest('form.cart');
                    if ($form.length === 0) $form = $('form.cart'); // fallback
                    
                    var $variation_input = $form.find('input[name="variation_id"]');
                    if ($variation_input.length > 0 && $variation_input.val() && $variation_input.val() != '0') {
                        content_id = $variation_input.val(); // 使用变体 ID
                    }

                    ttq.track('AddToCart', {
                        "contents": [
                            {
                                "content_id": content_id,
                                "content_type": "product",
                                "content_name": content_name
                            }
                        ],
                        "value": price, 
                        "currency": "<?php echo get_woocommerce_currency(); ?>"
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

    // --- Server Side Events (Keep existing implementation) ---

    /**
     * Server Side: ViewContent
     */
    public function track_server_view_content() {
        if (!is_product()) return;
        
        $product_id = get_queried_object_id();
        $product = wc_get_product($product_id);
        
        if (!$product) return;

        // 生成唯一的 Event ID
        $event_id = uniqid('vc_');

        // 获取 Catalog 必需字段
        $catalog_data = $this->get_tiktok_catalog_data($product);

        $properties = [
            'contents' => [
                [
                    'content_id' => (string)$product->get_id(),
                    'content_type' => 'product',
                    'content_name' => $product->get_name()
                ]
            ],
            'value' => (float)$product->get_price(),
            'currency' => get_woocommerce_currency(),
            
            // Catalog Fields
            'description' => $catalog_data['description'],
            'availability' => $catalog_data['availability'],
            'image_url' => $catalog_data['image_url'],
            'product_url' => $catalog_data['product_url'],
            'price' => (float)$product->get_price(),
            'content_category' => $catalog_data['content_category'],
            'brand' => $catalog_data['brand'],
            'condition' => $catalog_data['condition']
        ];

        $this->send_server_event('ViewContent', $properties, [], $event_id);
    }

    /**
     * Server Side: AddToCart
     */
    public function track_server_add_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        $product = wc_get_product($variation_id ? $variation_id : $product_id);
        $event_id = uniqid('atc_'); 

        $properties = [
            'contents' => [
                [
                    'content_id' => (string)$product->get_id(),
                    'content_type' => 'product',
                    'content_name' => $product->get_name()
                ]
            ],
            'value' => $product->get_price() * $quantity,
            'currency' => get_woocommerce_currency(),
            'quantity' => $quantity
        ];

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
     * 获取符合 TikTok Catalog 要求的额外产品数据
     * 
     * 参见: https://ads.tiktok.com/help/article/how-to-use-pixel-upload-with-catalogs
     */
    private function get_tiktok_catalog_data($product) {
        $data = [];
        
        // Description (Pixel Param: description)
        $description = $product->get_short_description();
        if (empty($description)) {
            $description = $product->get_description();
        }
        // 截取适度长度，去除HTML
        $data['description'] = mb_substr(wp_strip_all_tags($description), 0, 500); 
        if (empty($data['description'])) {
            $data['description'] = $product->get_name(); // Fallback
        }
        
        // Availability (Pixel Param: availability)
        // TikTok: in stock, available for order, preorder, out of stock, discontinued
        $stock_status = $product->get_stock_status(); // instock, outofstock, onbackorder
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
        
        // Image URL (Pixel Param: image_url)
        $image_id = $product->get_image_id();
        if ($image_id) {
            $data['image_url'] = wp_get_attachment_url($image_id);
        } else {
            $data['image_url'] = ''; // 或者是默认图片
        }
        
        // Product URL (Pixel Param: product_url)
        $data['product_url'] = $product->get_permalink();
        
        // Content Category (Pixel Param: content_category)
        $data['content_category'] = '';
        $category_ids = $product->get_category_ids();
        if (!empty($category_ids)) {
            $term = get_term($category_ids[0], 'product_cat');
            if ($term && !is_wp_error($term)) {
                $data['content_category'] = $term->name;
            }
        }

        // Brand (Pixel Param: brand)
        $data['brand'] = ''; 
        // 尝试获取品牌属性 'brand' 或 'pa_brand'
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

        // Condition (Pixel Param: condition)
        // WooCommerce 默认没有 condition 字段，通常默认为 'new'
        $data['condition'] = 'new';
        
        return $data;
    }
}
