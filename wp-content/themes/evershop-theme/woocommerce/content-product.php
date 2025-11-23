<?php
/**
 * The template for displaying product content within loops
 *
 * 使用统一的产品卡片组件
 *
 * @package Evershop_Theme
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}
?>
<li <?php wc_product_class( 'product-card', $product ); ?>>
    <?php
    // 使用统一的产品卡片组件 - 不需要外层 wrapper，因为 li 已经是容器
    get_template_part( 'template-parts/product/card', null, array(
        'product' => $product,
        'no_wrapper' => true
    ) );
    ?>
</li>

