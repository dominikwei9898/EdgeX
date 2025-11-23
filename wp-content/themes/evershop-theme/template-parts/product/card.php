<?php
/**
 * Product Card Component
 * 统一的产品卡片组件 - 可在任何地方复用
 * 
 * @package Evershop_Theme
 * 
 * Expected variables:
 * @var WC_Product $product - WooCommerce product object (from $args or global)
 * @var string $wrapper_class - Optional wrapper class (default: 'product__list__item product__list__item__grid')
 */

defined( 'ABSPATH' ) || exit;

// 从 $args 或 global 获取产品对象
if ( ! isset( $product ) && isset( $args['product'] ) ) {
    $product = $args['product'];
}
if ( ! isset( $product ) ) {
    global $product;
}

if ( empty( $product ) || ! is_object( $product ) ) {
    return;
}

// 确保产品可见
if ( ! $product->is_visible() ) {
    return;
}

// 默认 wrapper class
if ( ! isset( $wrapper_class ) && isset( $args['wrapper_class'] ) ) {
    $wrapper_class = $args['wrapper_class'];
}
$wrapper_class = isset( $wrapper_class ) ? $wrapper_class : 'product__list__item product__list__item__grid';

// 是否需要外层 wrapper
$use_wrapper = ! isset( $args['no_wrapper'] ) || ! $args['no_wrapper'];

// 获取 EverShop 自定义 Badge 数据
$badge_enabled = get_post_meta( $product->get_id(), '_espf_badge_enabled', true );
$badge_text    = get_post_meta( $product->get_id(), '_espf_badge_text', true );
$badge_color   = get_post_meta( $product->get_id(), '_espf_badge_color', true );

// 如果没有设置颜色，使用默认红色
if ( empty( $badge_color ) ) {
    $badge_color = '#ef4444';
}
?>

<?php if ( $use_wrapper ) : ?>
<div class="<?php echo esc_attr( $wrapper_class ); ?>">
<?php endif; ?>
    <div class="product__list__item__inner">
        <!-- 产品链接和图片 -->
        <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="product__list__link">
            <div class="product__list__image relative">
                <?php 
                if ( has_post_thumbnail( $product->get_id() ) ) {
                    echo get_the_post_thumbnail( 
                        $product->get_id(), 
                        'woocommerce_thumbnail', 
                        ['class' => 'w-full h-full object-cover'] 
                    );
                } else {
                    echo '<img src="' . esc_url( wc_placeholder_img_src() ) . '" alt="Placeholder" class="w-full h-full object-cover">';
                }
                
                // EverShop Custom Badge
                if ( $badge_enabled && ! empty( $badge_text ) ) : ?>
                    <div class="product__badge__wrapper product__badge__absolute">
                        <span 
                            class="product__badge" 
                            style="background-color: <?php echo esc_attr( $badge_color ); ?>; --badge-color: <?php echo esc_attr( $badge_color ); ?>;"
                        >
                            <?php echo esc_html( $badge_text ); ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="product__list__info">
                <h3 class="product__list__name"><?php echo esc_html( $product->get_name() ); ?></h3>
                <div class="product__list__price"><?php echo $product->get_price_html(); ?></div>
            </div>
        </a>

        <!-- Add to Basket 按钮 -->
        <div class="product__list__actions px-4 pb-4">
            <?php
            $is_in_stock = $product->is_in_stock();
            
            if ( ! $is_in_stock ) {
                // 缺货产品
                ?>
                <button class="button product-card-out-of-stock w-full block text-center" disabled>
                    <?php esc_html_e( 'Out of Stock', 'evershop-theme' ); ?>
                </button>
                <?php
            } else {
                // 所有有库存的产品统一显示 "SHOP NOW" 并跳转到详情页
                ?>
                <a 
                    href="<?php echo esc_url( $product->get_permalink() ); ?>" 
                    class="button w-full block text-center"
                    aria-label="<?php echo esc_attr( $product->add_to_cart_description() ); ?>"
                >
                    <?php esc_html_e( 'SHOP NOW', 'evershop-theme' ); ?>
                </a>
                <?php
            }
            ?>
        </div>
    </div>
<?php if ( $use_wrapper ) : ?>
</div>
<?php endif; ?>
