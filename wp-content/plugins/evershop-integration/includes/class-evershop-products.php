<?php
/**
 * EverShop Products API
 * 
 * 提供产品相关的 REST API 端点
 */

if (!defined('ABSPATH')) {
    exit;
}

class EverShop_Products {
    
    private static $namespace = 'evershop/v1';
    
    public static function init() {
        $instance = new self();
        $instance->setup_hooks();
    }
    
    private function setup_hooks() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * 注册 REST API 路由
     */
    public function register_routes() {
        // 获取产品列表
        register_rest_route(self::$namespace, '/products', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_products'],
                'permission_callback' => '__return_true'
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'create_product'],
                'permission_callback' => '__return_true' // 暂时允许所有请求，生产环境需要添加权限检查
            ]
        ]);
        
        // 根据 slug 获取产品
        register_rest_route(self::$namespace, '/products/(?P<slug>[a-zA-Z0-9-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_product_by_slug'],
            'permission_callback' => '__return_true'
        ]);
        
        // 根据 ID 获取产品
        register_rest_route(self::$namespace, '/products/id/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_product_by_id'],
            'permission_callback' => '__return_true'
        ]);
        
        // 获取特色产品
        register_rest_route(self::$namespace, '/products/featured', [
            'methods' => 'GET',
            'callback' => [$this, 'get_featured_products'],
            'permission_callback' => '__return_true'
        ]);
        
        // 获取产品分类
        register_rest_route(self::$namespace, '/categories', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_categories'],
                'permission_callback' => '__return_true'
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'create_category'],
                'permission_callback' => '__return_true'
            ]
        ]);
        
        // 获取产品属性
        register_rest_route(self::$namespace, '/attributes', [
            'methods' => 'GET',
            'callback' => [$this, 'get_attributes'],
            'permission_callback' => '__return_true'
        ]);
    }
    
    /**
     * 获取产品列表
     */
    public function get_products($request) {
        $params = $request->get_params();
        
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => isset($params['per_page']) ? intval($params['per_page']) : 12,
            'paged' => isset($params['page']) ? intval($params['page']) : 1,
            'orderby' => isset($params['orderby']) ? $params['orderby'] : 'date',
            'order' => isset($params['order']) ? $params['order'] : 'DESC'
        ];
        
        // 分类筛选
        if (isset($params['category'])) {
            $args['tax_query'][] = [
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $params['category']
            ];
        }
        
        // 搜索
        if (isset($params['search'])) {
            $args['s'] = sanitize_text_field($params['search']);
        }
        
        // 价格筛选
        if (isset($params['min_price']) || isset($params['max_price'])) {
            $args['meta_query'] = $args['meta_query'] ?? [];
            
            if (isset($params['min_price'])) {
                $args['meta_query'][] = [
                    'key' => '_price',
                    'value' => floatval($params['min_price']),
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                ];
            }
            
            if (isset($params['max_price'])) {
                $args['meta_query'][] = [
                    'key' => '_price',
                    'value' => floatval($params['max_price']),
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                ];
            }
        }
        
        $query = new WP_Query($args);
        $products = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $products[] = $this->format_product(get_the_ID());
            }
            wp_reset_postdata();
        }
        
        $response = rest_ensure_response($products);
        
        // 添加分页信息到响应头
        $response->header('X-WP-Total', $query->found_posts);
        $response->header('X-WP-TotalPages', $query->max_num_pages);
        
        return $response;
    }
    
    /**
     * 根据 slug 获取产品
     */
    public function get_product_by_slug($request) {
        $slug = $request->get_param('slug');
        
        $args = [
            'name' => $slug,
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 1
        ];
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            $query->the_post();
            $product = $this->format_product(get_the_ID(), true);
            wp_reset_postdata();
            
            return rest_ensure_response($product);
        }
        
        return new WP_Error('product_not_found', 'Product not found', ['status' => 404]);
    }
    
    /**
     * 根据 ID 获取产品
     */
    public function get_product_by_id($request) {
        $id = $request->get_param('id');
        
        $product_post = get_post($id);
        
        if (!$product_post || $product_post->post_type !== 'product' || $product_post->post_status !== 'publish') {
            return new WP_Error('product_not_found', 'Product not found', ['status' => 404]);
        }
        
        $product = $this->format_product($id, true);
        
        return rest_ensure_response($product);
    }
    
    /**
     * 获取特色产品
     */
    public function get_featured_products($request) {
        $params = $request->get_params();
        
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => isset($params['limit']) ? intval($params['limit']) : 8,
            'meta_query' => [
                [
                    'key' => '_featured',
                    'value' => 'yes'
                ]
            ]
        ];
        
        $query = new WP_Query($args);
        $products = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $products[] = $this->format_product(get_the_ID());
            }
            wp_reset_postdata();
        }
        
        return rest_ensure_response($products);
    }
    
    /**
     * 获取产品分类
     */
    public function get_categories($request) {
        $params = $request->get_params();
        
        $args = [
            'taxonomy' => 'product_cat',
            'hide_empty' => isset($params['hide_empty']) ? $params['hide_empty'] : false, // 默认显示空分类
            'orderby' => 'name',
            'order' => 'ASC'
        ];
        
        if (isset($params['parent'])) {
            $args['parent'] = intval($params['parent']);
        }
        
        $categories = get_terms($args);
        
        if (is_wp_error($categories)) {
            return new WP_Error('categories_error', $categories->get_error_message(), ['status' => 500]);
        }
        
        $formatted_categories = array_map(function($cat) {
            $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
            
            return [
                'id' => $cat->term_id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'description' => $cat->description,
                'count' => $cat->count,
                'parent' => $cat->parent,
                'image' => $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : null
            ];
        }, $categories);
        
        return rest_ensure_response($formatted_categories);
    }
    
    /**
     * 获取产品属性
     */
    public function get_attributes($request) {
        $attribute_taxonomies = wc_get_attribute_taxonomies();
        $attributes = [];
        
        foreach ($attribute_taxonomies as $tax) {
            $taxonomy = wc_attribute_taxonomy_name($tax->attribute_name);
            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => false
            ]);
            
            if (!is_wp_error($terms)) {
                $attributes[] = [
                    'id' => $tax->attribute_id,
                    'name' => $tax->attribute_label,
                    'slug' => $tax->attribute_name,
                    'type' => $tax->attribute_type,
                    'terms' => array_map(function($term) {
                        return [
                            'id' => $term->term_id,
                            'name' => $term->name,
                            'slug' => $term->slug
                        ];
                    }, $terms)
                ];
            }
        }
        
        return rest_ensure_response($attributes);
    }
    
    /**
     * 格式化产品数据
     */
    private function format_product($product_id, $include_full_details = false) {
        $product = wc_get_product($product_id);
        
        if (!$product) {
            return null;
        }
        
        // 基础数据
        $data = [
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'slug' => $product->get_slug(),
            'permalink' => get_permalink($product_id),
            'type' => $product->get_type(),
            'status' => $product->get_status(),
            'featured' => $product->is_featured(),
            'description' => $include_full_details ? $product->get_description() : '',
            'short_description' => $product->get_short_description(),
            'sku' => $product->get_sku(),
            'price' => $product->get_price(),
            'regular_price' => $product->get_regular_price(),
            'sale_price' => $product->get_sale_price(),
            'on_sale' => $product->is_on_sale(),
            'price_html' => $product->get_price_html(),
            'stock_status' => $product->get_stock_status(),
            'in_stock' => $product->is_in_stock(),
            'stock_quantity' => $product->get_stock_quantity(),
            'manage_stock' => $product->get_manage_stock(),
            'average_rating' => $product->get_average_rating(),
            'rating_count' => $product->get_rating_count(),
            'review_count' => $product->get_review_count(),
        ];
        
        // 图片
        $image_id = $product->get_image_id();
        if ($image_id) {
            $data['image'] = [
                'id' => $image_id,
                'src' => wp_get_attachment_url($image_id),
                'thumbnail' => wp_get_attachment_image_url($image_id, 'thumbnail'),
                'medium' => wp_get_attachment_image_url($image_id, 'medium'),
                'large' => wp_get_attachment_image_url($image_id, 'large'),
                'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true)
            ];
        }
        
        // 图片库
        $gallery_ids = $product->get_gallery_image_ids();
        $data['gallery'] = [];
        foreach ($gallery_ids as $img_id) {
            $data['gallery'][] = [
                'id' => $img_id,
                'src' => wp_get_attachment_url($img_id),
                'thumbnail' => wp_get_attachment_image_url($img_id, 'thumbnail'),
                'medium' => wp_get_attachment_image_url($img_id, 'medium'),
                'large' => wp_get_attachment_image_url($img_id, 'large'),
                'alt' => get_post_meta($img_id, '_wp_attachment_image_alt', true)
            ];
        }
        
        // 分类
        $categories = wp_get_post_terms($product_id, 'product_cat');
        $data['categories'] = array_map(function($cat) {
            return [
                'id' => $cat->term_id,
                'name' => $cat->name,
                'slug' => $cat->slug
            ];
        }, $categories);
        
        // 标签
        $tags = wp_get_post_terms($product_id, 'product_tag');
        $data['tags'] = array_map(function($tag) {
            return [
                'id' => $tag->term_id,
                'name' => $tag->name,
                'slug' => $tag->slug
            ];
        }, $tags);
        
        // EverShop 自定义字段
        // 视频功能已整合到 EdgeX Content Builder
        $data['testimonials'] = $this->get_json_meta($product_id, '_product_testimonials');
        $data['testimonials_title'] = get_post_meta($product_id, '_testimonials_title', true);
        $data['specifications'] = $this->get_json_meta($product_id, '_product_specifications');
        $data['subheading'] = get_post_meta($product_id, '_product_subheading', true);
        $data['side_title'] = get_post_meta($product_id, '_product_side_title', true);
        
        // 徽章
        $data['badge'] = [
            'enabled' => get_post_meta($product_id, '_badge_enabled', true) === 'yes',
            'text' => get_post_meta($product_id, '_badge_text', true),
            'color' => get_post_meta($product_id, '_badge_color', true) ?: '#ff6b35'
        ];
        
        // 变体产品
        if ($product->is_type('variable') && $include_full_details) {
            /** @var WC_Product_Variable $variable_product */
            $variable_product = $product;
            $variations = $variable_product->get_available_variations();
            $data['variations'] = array_map(function($variation) {
                return [
                    'id' => $variation['variation_id'],
                    'attributes' => $variation['attributes'],
                    'image' => $variation['image'],
                    'price' => $variation['display_price'],
                    'regular_price' => $variation['display_regular_price'],
                    'in_stock' => $variation['is_in_stock'],
                    'sku' => $variation['sku']
                ];
            }, $variations);
            
            // 产品属性
            $data['attributes'] = [];
            foreach ($product->get_attributes() as $attribute) {
                if ($attribute->get_variation()) {
                    $data['attributes'][] = [
                        'id' => $attribute->get_id(),
                        'name' => $attribute->get_name(),
                        'options' => $attribute->get_options(),
                        'variation' => $attribute->get_variation(),
                        'visible' => $attribute->get_visible()
                    ];
                }
            }
        }
        
        return $data;
    }
    
    /**
     * 获取 JSON 格式的元数据
     */
    private function get_json_meta($post_id, $meta_key) {
        $meta = get_post_meta($post_id, $meta_key, true);
        if (empty($meta)) {
            return [];
        }
        
        $decoded = json_decode($meta, true);
        return is_array($decoded) ? $decoded : [];
    }
    
    /**
     * 创建产品分类
     */
    public function create_category($request) {
        $data = $request->get_json_params();
        
        if (empty($data['name'])) {
            return new \WP_Error(
                'invalid_data',
                '分类名称是必需的',
                ['status' => 400]
            );
        }
        
        // 检查分类是否已存在
        $existing_term = term_exists($data['slug'] ?? $data['name'], 'product_cat');
        if ($existing_term) {
            return rest_ensure_response([
                'success' => true,
                'id' => $existing_term['term_id'],
                'message' => '分类已存在',
                'exists' => true
            ]);
        }
        
        // 创建分类
        $term_data = wp_insert_term(
            $data['name'],
            'product_cat',
            [
                'slug' => $data['slug'] ?? sanitize_title($data['name']),
                'description' => $data['description'] ?? ''
            ]
        );
        
        if (is_wp_error($term_data)) {
            return new \WP_Error(
                'create_failed',
                $term_data->get_error_message(),
                ['status' => 500]
            );
        }
        
        return rest_ensure_response([
            'success' => true,
            'id' => $term_data['term_id'],
            'category_id' => $term_data['term_id'],
            'message' => '分类创建成功'
        ]);
    }
    
    /**
     * 创建产品（用于迁移）
     */
    public function create_product($request) {
        $data = $request->get_json_params();
        
        if (empty($data['name']) || empty($data['sku'])) {
            return new \WP_Error(
                'invalid_data',
                '产品名称和 SKU 是必需的',
                ['status' => 400]
            );
        }
        
        // 创建 WooCommerce 产品
        $product = new \WC_Product_Simple();
        
        // 基本信息
        $product->set_name($data['name']);
        $product->set_sku($data['sku']);
        $product->set_status($data['status'] ?? 'publish');
        
        // 价格
        if (isset($data['regular_price'])) {
            $product->set_regular_price($data['regular_price']);
        }
        if (isset($data['sale_price'])) {
            $product->set_sale_price($data['sale_price']);
        }
        
        // 描述
        if (isset($data['description'])) {
            $product->set_description($data['description']);
        }
        if (isset($data['short_description'])) {
            $product->set_short_description($data['short_description']);
        }
        
        // 库存
        if (isset($data['manage_stock'])) {
            $product->set_manage_stock($data['manage_stock']);
        }
        if (isset($data['stock_quantity'])) {
            $product->set_stock_quantity($data['stock_quantity']);
        }
        if (isset($data['stock_status'])) {
            $product->set_stock_status($data['stock_status']);
        }
        
        // 重量
        if (isset($data['weight'])) {
            $product->set_weight($data['weight']);
        }
        
        // 处理分类
        if (isset($data['categories']) && is_array($data['categories'])) {
            $category_ids = [];
            foreach ($data['categories'] as $cat_identifier) {
                // 支持分类 slug 或 ID
                if (is_numeric($cat_identifier)) {
                    $category_ids[] = intval($cat_identifier);
                } else {
                    // 通过 slug 查找分类
                    $term = get_term_by('slug', $cat_identifier, 'product_cat');
                    if ($term) {
                        $category_ids[] = $term->term_id;
                    }
                }
            }
            if (!empty($category_ids)) {
                $product->set_category_ids($category_ids);
            }
        }
        
        // 保存产品
        $product_id = $product->save();
        
        if (!$product_id) {
            return new \WP_Error(
                'create_failed',
                '创建产品失败',
                ['status' => 500]
            );
        }
        
        // 保存元数据
        if (isset($data['meta_data']) && is_array($data['meta_data'])) {
            foreach ($data['meta_data'] as $meta) {
                if (isset($meta['key']) && isset($meta['value'])) {
                    update_post_meta($product_id, $meta['key'], $meta['value']);
                }
            }
        }
        
        return rest_ensure_response([
            'success' => true,
            'id' => $product_id,
            'message' => '产品创建成功'
        ]);
    }
}
