<?php
/**
 * Template: Key Benefits Content Block
 * 
 * 关键优势模块 - 2列布局，支持图标图片和背景装饰
 */

if (!defined('ABSPATH')) exit;

// 提取字段
$title = isset($block_data['title']) ? $block_data['title'] : '';
$background_color = isset($block_data['background_color']) ? $block_data['background_color'] : '#f5f5f5';
$top_bg_image = isset($block_data['top_background_image']) ? $block_data['top_background_image'] : '';
$bottom_bg_image = isset($block_data['bottom_background_image']) ? $block_data['bottom_background_image'] : '';
$benefits = isset($block_data['benefits']) ? $block_data['benefits'] : array();

if (empty($benefits)) {
    return;
}

// 获取背景图片 URL
$top_bg_url = '';
if ($top_bg_image) {
    $top_bg_url = is_numeric($top_bg_image) ? wp_get_attachment_image_url($top_bg_image, 'full') : $top_bg_image;
}

$bottom_bg_url = '';
if ($bottom_bg_image) {
    $bottom_bg_url = is_numeric($bottom_bg_image) ? wp_get_attachment_image_url($bottom_bg_image, 'full') : $bottom_bg_image;
}
?>

<section class="edgex-key-benefits" style="background-color: <?php echo esc_attr($background_color); ?>;">
    
    <!-- 顶部背景装饰 -->
    <?php if ($top_bg_url) : ?>
    <div class="key-benefit-top" style="background-image: url('<?php echo esc_url($top_bg_url); ?>');"></div>
    <?php endif; ?>
    
    <!-- 底部背景装饰 -->
    <?php if ($bottom_bg_url) : ?>
    <div class="key-benefit-bottom" style="background-image: url('<?php echo esc_url($bottom_bg_url); ?>');"></div>
    <?php endif; ?>
    
    <div class="key-benefits-content">
        <?php if ($title) : ?>
        <h2 class="key-benefits-title"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>
        
        <div class="benefits-grid">
            <?php foreach ($benefits as $benefit) : 
                $icon = isset($benefit['icon']) ? $benefit['icon'] : '';
                $benefit_title = isset($benefit['title']) ? $benefit['title'] : '';
                $description = isset($benefit['description']) ? $benefit['description'] : '';
                
                // 获取图标图片 URL
                $icon_url = '';
                if ($icon) {
                    $icon_url = is_numeric($icon) ? wp_get_attachment_image_url($icon, 'full') : $icon;
                }
            ?>
            <div class="benefit-item">
                <?php if ($icon_url) : ?>
                <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($benefit_title); ?>" class="benefit-icon">
                <?php endif; ?>
                
                <span class="black-divider">&nbsp;</span>
                
                <?php if ($benefit_title) : ?>
                <h3 class="benefit-title"><?php echo esc_html($benefit_title); ?></h3>
                <?php endif; ?>
                
                <?php if ($description) : ?>
                <p class="benefit-description"><?php echo esc_html($description); ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
</section>

<style>
/* ===== Key Benefits 模块样式 ===== */
.edgex-key-benefits {
    position: relative;
    width: 100%;
    padding: 80px 40px;
    overflow: visible;
}

/* 背景装饰层 */
.key-benefit-top,
.key-benefit-bottom {
    position: absolute;
    width: 331px;
    height: 331px;
    background-size: contain;
    background-repeat: no-repeat;
    z-index: 0;
    pointer-events: none;
    opacity: 0.5;
}

.key-benefit-top {
    top: 0;
    right: 0;
    background-position: top right;
}

.key-benefit-bottom {
    left: 0;
    bottom: 0;
    background-position: bottom left;
}

/* 内容区域 */
.key-benefits-content {
    position: relative;
    z-index: 1;
    max-width: 1200px;
    margin: 0 auto;
}

/* 标题 */
.key-benefits-title {
    text-align: center;
    font-size: 42px;
    font-weight: 700;
    margin-bottom: 60px;
    text-transform: uppercase;
    color: #000;
    font-family: "DDCHardware";

    
}

/* 2列网格布局 */
.benefits-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 50px 40px;
}

/* 单个优势项 - 纵向布局 */
.benefit-item {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0;
    padding: 30px 20px;
    transition: all 0.3s ease;
}

/* 图标样式 */
.benefit-icon {
    width: 100px;
    height: 100px;
    object-fit: contain;
    margin-bottom: 20px;
}

/* 黑色分隔线 */
.black-divider {
    display: block;
    width: 80px;
    height: 2px;
    background-color: black;
    margin-bottom: 20px;
}

.benefit-title {
    font-size: 20px;
    font-weight: 700;
    margin: 0 0 15px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #000;
    line-height: 1.3;
}

.benefit-description {
    font-size: 16px;
    line-height: 1.6;
    color: #666;
    margin: 0;
}

/* ===== 响应式设计 ===== */
@media (max-width: 1024px) {
    .edgex-key-benefits {
        padding: 60px 30px;
    }
    
    .benefits-grid {
        gap: 40px 30px;
    }
    
    .key-benefit-top,
    .key-benefit-bottom {
        width: calc(331px / 3);
        height: calc(331px / 3);
    }
}

@media (max-width: 768px) {
    .edgex-key-benefits {
        padding: 50px 20px;
    }
    
    .key-benefits-title {
        font-size: 32px;
        margin-bottom: 40px;
    }
    
    /* 移动端单列布局 */
    .benefits-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .benefit-item {
        padding: 25px;
    }
    
    .benefit-icon {
        width: 80px;
        height: 80px;
    }
    
    .benefit-title {
        font-size: 18px;
    }
    
    .benefit-description {
        font-size: 14px;
    }
    
    .key-benefit-top,
    .key-benefit-bottom {
        width: calc(331px / 4);
        height: calc(331px / 4);
        opacity: 0.2;
    }
}

@media (max-width: 480px) {
    .edgex-key-benefits {
        padding: 40px 15px;
    }
    
    .key-benefits-title {
        font-size: 28px;
        margin-bottom: 30px;
        letter-spacing: 1px;
    }
    
    .benefits-grid {
        gap: 25px;
    }
    
    .benefit-item {
        padding: 20px;
    }
}
</style>
