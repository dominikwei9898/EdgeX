<?php
/**
 * 内容块模板：自定义 HTML
 * 
 * 允许管理员插入自定义 HTML/CSS 代码
 * 
 * 可用变量: $block_data
 */

if (!defined('ABSPATH')) {
    exit;
}

$html_content = isset($block_data['html_content']) ? $block_data['html_content'] : '';
$css_content = isset($block_data['css_content']) ? $block_data['css_content'] : '';
$background_color = isset($block_data['background_color']) ? $block_data['background_color'] : '#ffffff';

if (empty($html_content)) {
    return;
}
?>

<section class="component-custom-html component-custom-liquid-<?php echo uniqid(); ?>" 
         style="background: <?php echo esc_attr($background_color); ?>;">
    
    <!-- 自定义 HTML 内容 -->
    <div class="custom-html-content">
        <?php 
        // 允许管理员的 HTML（已在后台过滤）
        // 使用 wp_kses_post 进行基本的 XSS 保护
        echo wp_kses_post($html_content); 
        ?>
    </div>
    
</section>

<?php if ($css_content) : ?>
<style>
<?php 
// 输出自定义 CSS
// 注意：CSS 已在后台进行基本验证
echo strip_tags($css_content); 
?>
</style>
<?php endif; ?>

<style>
.component-custom-html {
    padding: 60px 0;
}

.custom-html-content {
    /* 基础样式，可通过自定义 CSS 覆盖 */
}

/* 为自定义 HTML 提供常用的样式类 */
.custom-html-content h2 {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 20px;
}

.custom-html-content h3 {
    font-size: 28px;
    font-weight: 600;
    margin-bottom: 16px;
}

.custom-html-content p {
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 16px;
}

.custom-html-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
}

.custom-html-content .button {
    display: inline-block;
    padding: 14px 32px;
    background-color: #2271b1;
    color: #fff;
    text-decoration: none;
    border-radius: 4px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.custom-html-content .button:hover {
    background-color: #135e96;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .component-custom-html {
        padding: 40px 0;
    }
    
    .custom-html-content h2 {
        font-size: 28px;
    }
    
    .custom-html-content h3 {
        font-size: 22px;
    }
}
</style>

