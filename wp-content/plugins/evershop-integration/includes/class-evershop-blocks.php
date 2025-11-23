<?php
/**
 * EverShop Gutenberg Blocks
 * 
 * 注册和管理 Gutenberg 编辑器区块
 */

if (!defined('ABSPATH')) {
    exit;
}

class EverShop_Blocks {
    
    public static function init() {
        $instance = new self();
        $instance->setup_hooks();
    }
    
    private function setup_hooks() {
        // 注册区块
        add_action('init', [$this, 'register_blocks']);
        
        // 加载区块编辑器资源
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets']);
        
        // 加载区块前端资源
        add_action('enqueue_block_assets', [$this, 'enqueue_block_assets']);
    }
    
    /**
     * 注册所有 Gutenberg 区块
     */
    public function register_blocks() {
        // 1. Key Benefits 区块
        register_block_type(EVERSHOP_INTEGRATION_PLUGIN_DIR . 'blocks/key-benefits', [
            'render_callback' => [$this, 'render_key_benefits_block'],
            'attributes' => [
                'benefits' => [
                    'type' => 'array',
                    'default' => []
                ],
                'columns' => [
                    'type' => 'number',
                    'default' => 3
                ],
                'alignment' => [
                    'type' => 'string',
                    'default' => 'center'
                ]
            ]
        ]);
        
        // 2. Product Videos 区块
        register_block_type(EVERSHOP_INTEGRATION_PLUGIN_DIR . 'blocks/product-videos', [
            'render_callback' => [$this, 'render_product_videos_block'],
            'attributes' => [
                'title' => [
                    'type' => 'string',
                    'default' => 'Product Videos'
                ],
                'videos' => [
                    'type' => 'array',
                    'default' => []
                ],
                'autoplay' => [
                    'type' => 'boolean',
                    'default' => false
                ]
            ]
        ]);
        
        // 3. Testimonials 评价轮播区块
        register_block_type(EVERSHOP_INTEGRATION_PLUGIN_DIR . 'blocks/testimonials', [
            'render_callback' => [$this, 'render_testimonials_block'],
            'attributes' => [
                'title' => [
                    'type' => 'string',
                    'default' => 'Customer Reviews'
                ],
                'testimonials' => [
                    'type' => 'array',
                    'default' => []
                ],
                'autoplay' => [
                    'type' => 'boolean',
                    'default' => true
                ],
                'delay' => [
                    'type' => 'number',
                    'default' => 5000
                ]
            ]
        ]);
        
        // 4. Image + Text 图文区块
        register_block_type(EVERSHOP_INTEGRATION_PLUGIN_DIR . 'blocks/image-text', [
            'render_callback' => [$this, 'render_image_text_block'],
            'attributes' => [
                'imageUrl' => [
                    'type' => 'string',
                    'default' => ''
                ],
                'imagePosition' => [
                    'type' => 'string',
                    'default' => 'left'
                ],
                'title' => [
                    'type' => 'string',
                    'default' => ''
                ],
                'content' => [
                    'type' => 'string',
                    'default' => ''
                ],
                'buttonText' => [
                    'type' => 'string',
                    'default' => ''
                ],
                'buttonUrl' => [
                    'type' => 'string',
                    'default' => ''
                ]
            ]
        ]);
        
        // 5. Trust Badges 品牌保证区块
        register_block_type(EVERSHOP_INTEGRATION_PLUGIN_DIR . 'blocks/trust-badges', [
            'render_callback' => [$this, 'render_trust_badges_block'],
            'attributes' => [
                'badges' => [
                    'type' => 'array',
                    'default' => []
                ],
                'layout' => [
                    'type' => 'string',
                    'default' => 'horizontal'
                ]
            ]
        ]);
        
        // 6. Custom HTML 自定义 HTML 区块
        register_block_type(EVERSHOP_INTEGRATION_PLUGIN_DIR . 'blocks/custom-html', [
            'render_callback' => [$this, 'render_custom_html_block'],
            'attributes' => [
                'content' => [
                    'type' => 'string',
                    'default' => ''
                ],
                'className' => [
                    'type' => 'string',
                    'default' => ''
                ]
            ]
        ]);
    }
    
    /**
     * 渲染 Key Benefits 区块
     */
    public function render_key_benefits_block($attributes) {
        $benefits = $attributes['benefits'] ?? [];
        $columns = $attributes['columns'] ?? 3;
        $alignment = $attributes['alignment'] ?? 'center';
        
        if (empty($benefits)) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="evershop-key-benefits" style="text-align: <?php echo esc_attr($alignment); ?>;">
            <div class="benefits-grid" style="grid-template-columns: repeat(<?php echo esc_attr($columns); ?>, 1fr);">
                <?php foreach ($benefits as $benefit) : ?>
                    <div class="benefit-card">
                        <?php if (!empty($benefit['icon'])) : ?>
                            <div class="benefit-icon"><?php echo esc_html($benefit['icon']); ?></div>
                        <?php endif; ?>
                        <h3 class="benefit-title"><?php echo esc_html($benefit['title']); ?></h3>
                        <p class="benefit-description"><?php echo esc_html($benefit['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * 渲染 Product Videos 区块
     */
    public function render_product_videos_block($attributes) {
        $title = $attributes['title'] ?? 'Product Videos';
        $videos = $attributes['videos'] ?? [];
        
        if (empty($videos)) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="evershop-product-videos">
            <h2 class="videos-title"><?php echo esc_html($title); ?></h2>
            <div class="videos-grid swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($videos as $video) : ?>
                        <div class="swiper-slide video-item">
                            <?php if (!empty($video['url'])) : ?>
                                <?php echo wp_oembed_get($video['url']); ?>
                            <?php endif; ?>
                            <?php if (!empty($video['title'])) : ?>
                                <h4 class="video-title"><?php echo esc_html($video['title']); ?></h4>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * 渲染 Testimonials 区块
     */
    public function render_testimonials_block($attributes) {
        $title = $attributes['title'] ?? 'Customer Reviews';
        $testimonials = $attributes['testimonials'] ?? [];
        $autoplay = $attributes['autoplay'] ?? true;
        $delay = $attributes['delay'] ?? 5000;
        
        if (empty($testimonials)) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="evershop-testimonials" data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>" data-delay="<?php echo esc_attr($delay); ?>">
            <h2 class="testimonials-title"><?php echo esc_html($title); ?></h2>
            <div class="testimonials-slider swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($testimonials as $testimonial) : ?>
                        <div class="swiper-slide testimonial-card">
                            <?php if (!empty($testimonial['rating'])) : ?>
                                <div class="testimonial-rating">
                                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                                        <span class="star <?php echo $i <= $testimonial['rating'] ? 'filled' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                            <div class="testimonial-content">
                                <?php echo wp_kses_post($testimonial['content']); ?>
                            </div>
                            <div class="testimonial-author">
                                <?php if (!empty($testimonial['avatar'])) : ?>
                                    <img src="<?php echo esc_url($testimonial['avatar']); ?>" alt="<?php echo esc_attr($testimonial['author']); ?>" class="testimonial-avatar">
                                <?php endif; ?>
                                <strong><?php echo esc_html($testimonial['author']); ?></strong>
                                <?php if (!empty($testimonial['role'])) : ?>
                                    <span class="testimonial-role"><?php echo esc_html($testimonial['role']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * 渲染 Image + Text 区块
     */
    public function render_image_text_block($attributes) {
        $imageUrl = $attributes['imageUrl'] ?? '';
        $imagePosition = $attributes['imagePosition'] ?? 'left';
        $title = $attributes['title'] ?? '';
        $content = $attributes['content'] ?? '';
        $buttonText = $attributes['buttonText'] ?? '';
        $buttonUrl = $attributes['buttonUrl'] ?? '';
        
        ob_start();
        ?>
        <div class="evershop-image-text <?php echo esc_attr('image-' . $imagePosition); ?>">
            <?php if ($imageUrl) : ?>
                <div class="image-section">
                    <img src="<?php echo esc_url($imageUrl); ?>" alt="<?php echo esc_attr($title); ?>">
                </div>
            <?php endif; ?>
            <div class="text-section">
                <?php if ($title) : ?>
                    <h2><?php echo esc_html($title); ?></h2>
                <?php endif; ?>
                <?php if ($content) : ?>
                    <div class="content"><?php echo wp_kses_post($content); ?></div>
                <?php endif; ?>
                <?php if ($buttonText && $buttonUrl) : ?>
                    <a href="<?php echo esc_url($buttonUrl); ?>" class="button"><?php echo esc_html($buttonText); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * 渲染 Trust Badges 区块
     */
    public function render_trust_badges_block($attributes) {
        $badges = $attributes['badges'] ?? [];
        $layout = $attributes['layout'] ?? 'horizontal';
        
        if (empty($badges)) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="evershop-trust-badges layout-<?php echo esc_attr($layout); ?>">
            <div class="badges-container">
                <?php foreach ($badges as $badge) : ?>
                    <div class="trust-badge">
                        <?php if (!empty($badge['icon'])) : ?>
                            <div class="badge-icon">
                                <?php if (filter_var($badge['icon'], FILTER_VALIDATE_URL)) : ?>
                                    <img src="<?php echo esc_url($badge['icon']); ?>" alt="<?php echo esc_attr($badge['title']); ?>">
                                <?php else : ?>
                                    <?php echo esc_html($badge['icon']); ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="badge-content">
                            <strong><?php echo esc_html($badge['title']); ?></strong>
                            <?php if (!empty($badge['description'])) : ?>
                                <span><?php echo esc_html($badge['description']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * 渲染 Custom HTML 区块
     */
    public function render_custom_html_block($attributes) {
        $content = $attributes['content'] ?? '';
        $className = $attributes['className'] ?? '';
        
        if (empty($content)) {
            return '';
        }
        
        return '<div class="evershop-custom-html ' . esc_attr($className) . '">' . $content . '</div>';
    }
    
    /**
     * 加载区块编辑器资源
     */
    public function enqueue_block_editor_assets() {
        wp_enqueue_script(
            'evershop-blocks-editor',
            EVERSHOP_INTEGRATION_PLUGIN_URL . 'assets/js/blocks-editor.js',
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
            EVERSHOP_INTEGRATION_VERSION,
            true
        );
        
        wp_enqueue_style(
            'evershop-blocks-editor',
            EVERSHOP_INTEGRATION_PLUGIN_URL . 'assets/css/blocks-editor.css',
            ['wp-edit-blocks'],
            EVERSHOP_INTEGRATION_VERSION
        );
    }
    
    /**
     * 加载区块前端资源
     */
    public function enqueue_block_assets() {
        if (!is_admin()) {
            wp_enqueue_style(
                'evershop-blocks',
                EVERSHOP_INTEGRATION_PLUGIN_URL . 'assets/css/blocks.css',
                [],
                EVERSHOP_INTEGRATION_VERSION
            );
            
            // 如果页面包含视频或评价轮播，加载 Swiper
            if (has_block('evershop/product-videos') || has_block('evershop/testimonials')) {
                wp_enqueue_script(
                    'swiper',
                    'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
                    [],
                    '11.0.0',
                    true
                );
                wp_enqueue_style(
                    'swiper',
                    'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
                    [],
                    '11.0.0'
                );
                
                // 加载前端 JavaScript 来初始化 Swiper
                wp_enqueue_script(
                    'evershop-blocks-frontend',
                    EVERSHOP_INTEGRATION_PLUGIN_URL . 'assets/js/blocks-frontend.js',
                    ['swiper'],
                    EVERSHOP_INTEGRATION_VERSION,
                    true
                );
            }
        }
    }
}

