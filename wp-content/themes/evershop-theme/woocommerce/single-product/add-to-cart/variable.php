<?php
/**
 * Custom Variable Product Add to Cart
 * 
 * EverShop Theme - 优化的变体商品模板
 * 移除了不必要的 data-product_variations（因为使用自定义变体选择器）
 * 
 * @package EverShop_Theme
 * @version 9.6.0
 */

defined('ABSPATH') || exit;

global $product;

// 注意：$available_variations 和 $attributes 变量由 WooCommerce 自动提供
$available_variations_count = is_array($available_variations) ? count($available_variations) : 0;
$attribute_keys = array_keys($attributes);

// 准备变体数据 JSON（WooCommerce JS 需要）
$variations_json = wp_json_encode($available_variations);
$variations_attr = function_exists('wc_esc_json') ? wc_esc_json($variations_json) : _wp_specialchars($variations_json, ENT_QUOTES, 'UTF-8', true);

do_action('woocommerce_before_add_to_cart_form'); 
?>

<form class="variations_form cart" 
      action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" 
      method="post" 
      enctype="multipart/form-data" 
      data-product_id="<?php echo absint($product->get_id()); ?>"
      data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. ?>">
    
    <?php do_action('woocommerce_before_variations_form'); ?>

    <?php if (empty($available_variations) && false !== $available_variations) : ?>
        <!-- 无可用变体时显示 -->
        <p class="stock out-of-stock">
            <?php echo esc_html(apply_filters('woocommerce_out_of_stock_message', __('This product is currently out of stock and unavailable.', 'woocommerce'))); ?>
        </p>
    <?php else : ?>
        
        <!-- 
            注意：默认的 table.variations 通过 CSS 隐藏
            但必须存在，因为 WooCommerce 的 JS 需要它来触发事件
            我们的自定义变体选择器在 single-product.php 中单独显示
        -->
        <table class="variations" cellspacing="0" role="presentation" style="display: none;">
            <tbody>
                <?php foreach ($attributes as $attribute_name => $options) : ?>
                    <tr>
                        <td class="value">
                            <?php
                                // 创建隐藏的 select，用于与 WooCommerce JS 通信
                                wc_dropdown_variation_attribute_options(
                                    array(
                                        'options'   => $options,
                                        'attribute' => $attribute_name,
                                        'product'   => $product,
                                    )
                                );
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php do_action('woocommerce_after_variations_table'); ?>

        <!-- 变体信息和 Add to Cart 按钮容器 -->
        <div class="single_variation_wrap">
            <?php
                /**
                 * Hook: woocommerce_before_single_variation
                 */
                do_action('woocommerce_before_single_variation');

                /**
                 * Hook: woocommerce_single_variation
                 * 
                 * @hooked woocommerce_single_variation - 10 空的变体信息容器
                 * @hooked woocommerce_single_variation_add_to_cart_button - 20 我们的自定义按钮
                 */
                do_action('woocommerce_single_variation');

                /**
                 * Hook: woocommerce_after_single_variation
                 */
                do_action('woocommerce_after_single_variation');
            ?>
        </div>
        
    <?php endif; ?>

    <?php do_action('woocommerce_after_variations_form'); ?>
</form>

<?php
do_action('woocommerce_after_add_to_cart_form');

// 调试信息
if (current_user_can('manage_options') && isset($_GET['debug'])) {
    echo '<div style="background: #f0f0f0; padding: 10px; margin-top: 10px; font-size: 12px; font-family: monospace;">';
    echo '<strong>🔍 变体模板调试信息：</strong><br>';
    echo '✅ 使用自定义 variable.php 模板<br>';
    echo '✅ 保留了 data-product_variations（WC JS 依赖）<br>';
    echo '✅ 保留了隐藏的 select（确保 WC JS 兼容性）<br>';
    echo '✅ 自定义变体选择器在 single-product.php 中<br>';
    echo '📊 变体数量: ' . $available_variations_count . '<br>';
    echo '📦 数据大小: ' . strlen($variations_json) . ' bytes<br>';
    echo '</div>';
}
?>

