<?php
/**
 * Template: Image Module Content Block
 * 
 * 图文模块 - 支持单图/多图+文字，全宽布局，无page-width限制
 */

if (!defined('ABSPATH')) exit;

// 提取字段
$title = isset($block_data['title']) ? $block_data['title'] : '';
$images = isset($block_data['images']) ? $block_data['images'] : array();
$image_position = isset($block_data['image_position']) ? $block_data['image_position'] : 'full';
$content_title = isset($block_data['content_title']) ? $block_data['content_title'] : '';
$content_subtitle = isset($block_data['content_subtitle']) ? $block_data['content_subtitle'] : '';
$content_text = isset($block_data['content_text']) ? $block_data['content_text'] : '';
$button_text = isset($block_data['button_text']) ? $block_data['button_text'] : '';
$button_action = isset($block_data['button_action']) ? $block_data['button_action'] : 'none';
$button_link = isset($block_data['button_link']) ? $block_data['button_link'] : '';
$scroll_target = isset($block_data['scroll_target']) ? $block_data['scroll_target'] : '';
$background_color = isset($block_data['background_color']) ? $block_data['background_color'] : '#ffffff';
$title_color = isset($block_data['title_color']) ? $block_data['title_color'] : '#000000';
$content_color = isset($block_data['content_color']) ? $block_data['content_color'] : '#000000';
$text_shadow = isset($block_data['text_shadow']) ? $block_data['text_shadow'] : 'none';
$button_bg_color = isset($block_data['button_bg_color']) ? $block_data['button_bg_color'] : '#000000';
$button_text_color = isset($block_data['button_text_color']) ? $block_data['button_text_color'] : '#ffffff';

// 文字阴影样式
$title_shadow_style = '';
$content_shadow_style = '';
if ($text_shadow === 'light') {
    $title_shadow_style = 'text-shadow: 0 2px 10px rgba(255, 255, 255, 0.5);';
    $content_shadow_style = 'text-shadow: 0 1px 5px rgba(255, 255, 255, 0.5);';
} elseif ($text_shadow === 'dark') {
    $title_shadow_style = 'text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);';
    $content_shadow_style = 'text-shadow: 0 1px 5px rgba(0, 0, 0, 0.5);';
}

// 确保 images 是数组
if (!is_array($images)) {
    $images = array();
}

// 判断图片数量（多图模式）
$image_count = count($images);
$has_gallery = $image_count > 1;
$has_content = !empty($content_title) || !empty($content_subtitle) || !empty($content_text) || !empty($button_text);
$has_images = $image_count > 0;
$content_only = !$has_images && $has_content;
$gallery_only = $has_gallery && !$has_content && $image_position === 'full';

?>

<section class="edgex-image-module <?php echo $has_gallery ? 'gallery-mode' : 'single-mode'; ?> <?php echo $image_position === 'full' ? 'full-width-mode' : ''; ?> <?php echo $content_only ? 'content-only-mode' : ''; ?> <?php echo $gallery_only ? 'gallery-only-mode' : ''; ?>" 
         style="background-color: <?php echo esc_attr($background_color); ?>;">
    
    <div class="image-module-wrapper image-position-<?php echo esc_attr($image_position); ?>">
        
        <?php if ($has_images) : ?>
        <!-- 图片区域 -->
        <div class="image-section">
            <?php if ($image_count === 1) : 
                // 单图模式
                $first_image = $images[0];
                $img = isset($first_image['image']) ? $first_image['image'] : '';
                $mobile_img = isset($first_image['mobile_image']) ? $first_image['mobile_image'] : '';
                $alt_text = isset($first_image['alt_text']) ? $first_image['alt_text'] : 'Product Image';
                
                if ($img) :
                    $image_url = is_numeric($img) ? wp_get_attachment_image_url($img, 'full') : $img;
                    $mobile_image_url = $mobile_img ? (is_numeric($mobile_img) ? wp_get_attachment_image_url($mobile_img, 'full') : $mobile_img) : '';
                    $image_srcset = is_numeric($img) ? wp_get_attachment_image_srcset($img, 'full') : '';
                    $mobile_srcset = is_numeric($mobile_img) ? wp_get_attachment_image_srcset($mobile_img, 'full') : '';
            ?>
                <picture>
                    <?php if ($mobile_image_url) : ?>
                        <source media="(max-width: 768px)" 
                                srcset="<?php echo esc_attr($mobile_srcset ? $mobile_srcset : $mobile_image_url); ?>">
                    <?php endif; ?>
                    <img src="<?php echo esc_url($image_url); ?>" 
                         <?php if ($image_srcset) : ?>srcset="<?php echo esc_attr($image_srcset); ?>"<?php endif; ?>
                         alt="<?php echo esc_attr($alt_text); ?>" 
                         class="main-image">
                </picture>
                <?php endif; ?>
                
            <?php elseif ($has_gallery) : 
                // 多图模式（横向画廊）
            ?>
                <div class="product-gallery">
                    <?php foreach ($images as $item) : 
                        $item_image = isset($item['image']) ? $item['image'] : '';
                        $item_mobile = isset($item['mobile_image']) ? $item['mobile_image'] : '';
                        $alt_text = isset($item['alt_text']) ? $item['alt_text'] : 'Product Image';
                        
                        if (!$item_image) continue;
                        
                        $item_image_url = is_numeric($item_image) ? wp_get_attachment_image_url($item_image, 'full') : $item_image;
                        $item_mobile_url = $item_mobile ? (is_numeric($item_mobile) ? wp_get_attachment_image_url($item_mobile, 'full') : $item_mobile) : '';
                        $item_srcset = is_numeric($item_image) ? wp_get_attachment_image_srcset($item_image, 'full') : '';
                        $item_mobile_srcset = is_numeric($item_mobile) ? wp_get_attachment_image_srcset($item_mobile, 'full') : '';
                    ?>
                        <div class="gallery-item">
                            <picture>
                                <?php if ($item_mobile_url) : ?>
                                    <source media="(max-width: 768px)" 
                                            srcset="<?php echo esc_attr($item_mobile_srcset ? $item_mobile_srcset : $item_mobile_url); ?>">
                                <?php endif; ?>
                                <img src="<?php echo esc_url($item_image_url); ?>" 
                                     <?php if ($item_srcset) : ?>srcset="<?php echo esc_attr($item_srcset); ?>"<?php endif; ?>
                                     alt="<?php echo esc_attr($alt_text); ?>" 
                                     class="gallery-image">
                            </picture>
                </div>
            <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- 文字内容区域 -->
        <?php if ($has_content) : ?>
        <div class="content-section">
            <div class="content-inner">
                <?php if ($content_title) : ?>
                    <h2 class="content-title" style="color: <?php echo esc_attr($title_color); ?>; <?php echo $title_shadow_style; ?>"><?php echo nl2br(esc_html($content_title)); ?></h2>
                <?php endif; ?>
                
                <?php if ($content_subtitle) : ?>
                    <h4 class="content-subtitle" style="color: <?php echo esc_attr($title_color); ?>; <?php echo $title_shadow_style; ?>"><?php echo nl2br(esc_html($content_subtitle)); ?></h4>
                <?php endif; ?>
                
                <?php if ($content_text) : ?>
                    <p class="content-text" style="color: <?php echo esc_attr($content_color); ?>; <?php echo $content_shadow_style; ?>"><?php echo nl2br(esc_html($content_text)); ?></p>
                <?php endif; ?>
                
                <?php if ($button_text) : ?>
                    <?php if ($button_action === 'scroll' && $scroll_target) : ?>
                        <button class="cta-button" 
                                data-scroll-target="<?php echo esc_attr($scroll_target); ?>"
                                style="background-color: <?php echo esc_attr($button_bg_color); ?>; color: <?php echo esc_attr($button_text_color); ?>;">
                            <?php echo esc_html($button_text); ?>
                        </button>
                    <?php elseif ($button_action === 'link' && $button_link) : ?>
                        <a href="<?php echo esc_url($button_link); ?>" 
                           class="cta-button"
                           style="background-color: <?php echo esc_attr($button_bg_color); ?>; color: <?php echo esc_attr($button_text_color); ?>;">
                            <?php echo esc_html($button_text); ?>
                        </a>
                    <?php else : ?>
                        <span class="cta-button" style="background-color: <?php echo esc_attr($button_bg_color); ?>; color: <?php echo esc_attr($button_text_color); ?>;">
                            <?php echo esc_html($button_text); ?>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</section>

<style>
/* ===== 图文模块通用样式 ===== */
.edgex-image-module {
    width: 100%;
    padding: 40px 0;
}

.image-module-wrapper {
    display: flex;
    align-items: center;
    gap: 40px;
}

/* ===== 纯文案模式 ===== */
.content-only-mode {
    display: block;
    padding: 60px 20px;
}

.content-only-mode .image-module-wrapper {
    justify-content: center;
    max-width: 800px;
    margin: 0 auto;
}

.content-only-mode .content-section {
    position: static !important;
    max-width: 100%;
    width: 100%;
    padding: 0;
    text-align: center;
}

.content-only-mode .content-inner {
    align-items: center;
}

.content-only-mode .image-section::after {
    display: none;
}

/* 图片左侧布局 */
.image-position-left .image-module-wrapper {
    flex-direction: row;
}

/* 图片右侧布局 */
.image-position-right .image-module-wrapper {
    flex-direction: row-reverse;
}

/* 图片全宽布局 */
.image-position-full .image-module-wrapper {
    flex-direction: column;
    gap: 0;
}

.full-width-mode .image-section {
    width: 100%;
    max-width: 100%;
}

.full-width-mode .content-section {
    position: absolute;
    bottom: 40px;
    left: 0;
    right: 0;
    text-align: center;
    z-index: 10;
    padding: 20px;
}

.full-width-mode.edgex-image-module {
    position: relative;
    padding: 0;
}


.full-width-mode .image-section {
    position: relative;
}

.full-width-mode .cta-button {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

/* ===== 图片区域 ===== */
.image-section {
    flex: 1;
    min-width: 0;
    max-width: 50%;
    overflow: hidden;
}

/* 单图/多图左右布局时，确保图片区域不超出容器 */
.image-position-left .image-section,
.image-position-right .image-section {
    max-width: 50%;
    min-height: 500px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* picture元素自适应宽度 */
.image-position-left .image-section picture,
.image-position-right .image-section picture {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.main-image {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 8px;
}

/* 左/右布局时的单图 - 适配高度和宽度 */
.image-position-left .main-image,
.image-position-right .main-image {
    width: 100%;
    height: auto;
    min-height: 500px;
    max-height: 600px;
    object-fit: contain;
    max-width: 100%;
}

.full-width-mode .main-image {
    border-radius: 0;
    width: 100%;
    min-height: 400px;
    object-fit: cover;
}

.full-width-mode .gallery-image {
    border-radius: 0;
    object-fit: cover;
}

/* 确保图片高质量渲染 */
picture img,
.main-image,
.gallery-image {
    image-rendering: -webkit-optimize-contrast;
    image-rendering: high-quality;
    -webkit-backface-visibility: hidden;
    -moz-backface-visibility: hidden;
    backface-visibility: hidden;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* ===== 多图画廊 ===== */
.product-gallery {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    width: 100%;
}

/* 左/右布局模式下的画廊 - 水平居中排列（参考 Jay 网站） */
.image-position-left .product-gallery,
.image-position-right .product-gallery {
    flex: 1;
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: flex-start;
    gap: 2rem;
    position: relative;
    flex-wrap: nowrap;
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: thin;
    padding: 20px 0;
}

.full-width-mode .product-gallery {
    padding: 0;
    gap: 0;
    flex-direction: row;
    flex-wrap: nowrap;
    justify-content: center;
    overflow-x: visible;
    overflow-y: visible;
    max-height: none;
    width: 100%;
}

.full-width-mode .gallery-item {
    flex: 1 1 0;
    width: 0;
    min-width: 0;
    max-width: none;
}

.full-width-mode .gallery-image {
    width: 100%;
    height: auto;
    object-fit: cover;
}

/* 全宽模式下只有多图（无文字内容）的样式 - Featured In 样式 */
.gallery-only-mode {
    padding: 33px 15% !important;
}

.gallery-only-mode .image-module-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
}

.gallery-only-mode .product-gallery {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 33px 3%;
    width: 100%;
    padding: 0;
    max-width: 1400px;
    margin: 0 auto;
}

.gallery-only-mode .gallery-item {
    flex: 0 1 calc((100% - 9%) / 4);
    max-width: 200px;
    text-align: center;
    display: flex;
    justify-content: center;
    align-items: center;
}

.gallery-only-mode .gallery-item picture {
    display: flex;
    justify-content: center;
    align-items: center;
}

.gallery-only-mode .gallery-image {
    width: auto;
    max-width: 100%;
    height: auto;
    object-fit: contain;
    border-radius: 0;
}

/* 左/右布局时的gallery-item - 适配高度和宽度 */
.image-position-left .gallery-item,
.image-position-right .gallery-item {
    flex: 1 1 auto;
    width: auto;
    min-width: 200px;
    max-width: 400px;
    min-height: 500px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* gallery-item内的picture元素自适应 */
.image-position-left .gallery-item picture,
.image-position-right .gallery-item picture {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-gallery::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.product-gallery::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 3px;
}

.product-gallery::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.3);
    border-radius: 3px;
}

.gallery-item {
    flex: 0 0 auto;
    width: 300px;
}

.gallery-image {
    width: 100%;
    height: auto;
    max-width: 100%;
    display: block;
    border-radius: 8px;
    transition: transform 0.3s ease;
    object-fit: cover;
}

.image-position-left .gallery-image,
.image-position-right .gallery-image {
    width: 100%;
    height: auto;
    min-height: 500px;
    max-height: 600px;
    max-width: 100%;
    object-fit: contain;
    border-radius: 8px;
}

.gallery-image:hover {
    transform: scale(1.02);
}

/* ===== 文字内容区域 ===== */
.content-section {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 0;
    max-width: 50%;
    padding: 0 40px;
}

.image-position-left .content-section,
.image-position-right .content-section {
    max-width: 50%;
}

.content-inner {
    width: 100%;
    max-width: 100%;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0;
}

/* 文案标题样式 - H2 */
.content-title {
    font-size: 26px;
    font-weight: bold;
    margin: 0 0 10px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    line-height: 1.3;
    color: #000;
    font-family: DDCHardware, 'Arial Black', 'Arial Bold', Arial, sans-serif;
}

/* 文案副标题样式 - H4 */
.content-subtitle {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 15px 0;
    letter-spacing: 0.3px;
    line-height: 1.4;
    color: #000;
    font-family: DDCHardware, 'Arial Black', 'Arial Bold', Arial, sans-serif;
}

/* 分隔线样式 */
.content-divider {
    display: block;
    width: 80px;
    height: 2px;
    background-color: #000;
    margin-bottom: 20px;
}

/* 文案内容样式 */
.content-text {
    font-size: 16px;
    line-height: 1.7;
    margin: 0 0 20px 0;
    color: #000;
}

/* 全宽模式下文案样式调整 */
.full-width-mode .content-inner {
    align-items: center;
    text-align: center;
}

.full-width-mode .content-text {
    max-width: 600px;
}

.cta-button {
    display: inline-block;
    padding: 16px 40px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: 1px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.cta-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    opacity: 0.9;
}

/* ===== 响应式设计 ===== */
@media (max-width: 1024px) {
    .image-module-wrapper {
        gap: 30px;
    }
    
    .gallery-item {
        width: 250px;
    }
}

@media (max-width: 768px) {
    .edgex-image-module {
        padding: 30px 0;
    }
    
    .edgex-image-module:not(.full-width-mode) {
        padding: 30px 20px;
    }
    
    .image-module-wrapper {
        flex-direction: column !important;
        gap: 20px;
    }
    
    /* 移动端下图片和内容区域占满宽度 */
    .image-section,
    .content-section,
    .image-position-left .image-section,
    .image-position-right .image-section,
    .image-position-left .content-section,
    .image-position-right .content-section {
        max-width: 100%;
        width: 100%;
        min-height: auto;
    }
    
    .content-section {
        padding: 0 20px;
    }
    
    /* 移动端画廊 - 不换行，均匀分布，弹性自适应 */
    .image-position-left .product-gallery,
    .image-position-right .product-gallery {
        flex-direction: row;
        flex-wrap: nowrap;           /* ✅ 不换行 */
        overflow-x: auto;            /* ✅ 超出时横向滚动 */
        overflow-y: hidden;
        max-height: none;
        min-height: auto;
        justify-content: space-between;  /* ✅ 均匀分布 */
        gap: 15px;
        width: 100%;
    }
    
    /* 移动端gallery-item - 弹性自适应容器宽度 */
    .gallery-item,
    .image-position-left .gallery-item,
    .image-position-right .gallery-item {
        flex: 1 1 auto;              /* ✅ 弹性自适应 */
        min-width: 120px;            /* ✅ 最小宽度 */
        max-width: 50%;              /* ✅ 最大宽度不超过50% */
        width: auto;
        min-height: auto;
    }
    
    /* 移动端单图 - 占满容器宽度 */
    .image-position-left .main-image,
    .image-position-right .main-image {
        width: 100%;
        height: auto;
        min-height: auto;
        max-height: none;
        object-fit: contain;
    }
    
    /* 移动端gallery图片 - 自适应容器宽度 */
    .image-position-left .gallery-image,
    .image-position-right .gallery-image {
        width: 100%;                 /* ✅ 填满gallery-item */
        height: auto;
        min-height: 200px;           /* ✅ 保持最小高度 */
        max-height: 300px;
        max-width: 100%;
        object-fit: contain;
    }
    
    .cta-button {
        width: 100%;
        text-align: center;
        padding: 14px 30px;
        font-size: 14px;
    }
    
    /* 移动端文案样式 */
    .content-inner {
        align-items: center;
        text-align: center;
        padding: 20px 0;
    }
    
    .content-title {
        font-size: 20px;
    }
    
    .content-subtitle {
        font-size: 16px;
    }
    
    .content-text {
        font-size: 14px;
    }
    
    /* 移动端gallery-only模式调整 */
    .gallery-only-mode {
        padding: 20px 5% !important;
    }
    
    .gallery-only-mode .product-gallery {
        gap: 20px 3%;
    }
    
    .gallery-only-mode .gallery-item {
        flex: 0 1 calc((100% - 3%) / 2);
        max-width: none;
    }
    
    .content-divider {
        width: 60px;
    }
    
    /* 移动端纯文案模式 */
    .content-only-mode {
        padding: 40px 20px;
    }
    
    .content-only-mode .content-section {
        padding: 0;
    }
    
    /* 移动端全宽多图模式 - 保持不换行 */
    .full-width-mode .product-gallery {
        flex-wrap: nowrap;
    }
    
    .full-width-mode .gallery-item {
        flex: 1 1 0;
        min-width: 0;
    }
    
    .full-width-mode .main-image {
        min-height: 300px;
    }
    
    .full-width-mode .content-section {
        bottom: 20px;
        padding: 0 20px;
    }
}

@media (max-width: 480px) {
    .product-gallery {
        gap: 10px;
    }
    
    /* 小屏幕下gallery-item也保持弹性自适应 */
    .image-position-left .gallery-item,
    .image-position-right .gallery-item {
        flex: 1 1 auto;
        min-width: 100px;
        max-width: 45%;
    }
    
    .image-position-left .gallery-image,
    .image-position-right .gallery-image {
        min-height: 150px;
        max-height: 250px;
    }
    
    /* 全宽多图模式 - 小屏幕保持不换行，自适应宽度 */
    .full-width-mode .product-gallery {
        flex-wrap: nowrap;
    }
    
    .full-width-mode .gallery-item {
        flex: 1 1 0;
        width: 0;
        min-width: 0;
    }
    
    .full-width-mode .content-section:not(.content-only-mode .content-section) {
        position: relative;
        bottom: auto;
        padding: 20px;
        background: rgba(0, 0, 0, 0.5);
    }
    
    .full-width-mode .image-section::after {
        display: none;
    }
    
    /* 小屏幕纯文案模式 */
    .content-only-mode {
        padding: 30px 15px;
    }
}
</style>

<script>
// 滚动按钮功能
document.querySelectorAll('.cta-button[data-scroll-target]').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.dataset.scrollTarget);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>
