<?php
/**
 * 内容块模板：客户评价
 * 

 */

if (!defined('ABSPATH')) {
    exit;
}

$title = isset($block_data['title']) ? $block_data['title'] : '';
$show_images_desktop = isset($block_data['show_shared_images_desktop']) ? $block_data['show_shared_images_desktop'] : 'no';
$testimonials = isset($block_data['testimonials']) ? $block_data['testimonials'] : array();

if (empty($testimonials)) {
    return;
}

$slider_id = 'testimonials-' . uniqid();
?>

<section class="product-testimonials-section">
    
    <?php if ($title) : ?>
    <h3 class="testimonials-heading"><?php echo esc_html($title); ?></h3>
    <?php endif; ?>
        
    <div class="testimonials-carousel" id="<?php echo esc_attr($slider_id); ?>">
        <button class="carousel-button carousel-button-prev" aria-label="Previous">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </button>
        
        <div class="testimonials-container">
            <div class="testimonials-track">
                <?php foreach ($testimonials as $index => $testimonial) : 
                    $avatar = isset($testimonial['avatar']) ? $testimonial['avatar'] : '';
                    $name = isset($testimonial['name']) ? $testimonial['name'] : 'Customer';
                    $testimonial_title = isset($testimonial['title']) ? $testimonial['title'] : '';
                    $content = isset($testimonial['content']) ? $testimonial['content'] : '';
                    $rating = isset($testimonial['rating']) ? intval($testimonial['rating']) : 5;
                    $shared_image = isset($testimonial['shared_image']) ? $testimonial['shared_image'] : '';
                    
                    // 获取头像 URL
                    $avatar_url = '';
                    if (is_numeric($avatar)) {
                        $avatar_url = wp_get_attachment_image_url($avatar, 'thumbnail');
                    } elseif (is_array($avatar) && isset($avatar['url'])) {
                        $avatar_url = $avatar['url'];
                    }
                    
                    // 获取分享图片 URL
                    $shared_image_url = '';
                    if (is_numeric($shared_image)) {
                        $shared_image_url = wp_get_attachment_image_url($shared_image, 'large');
                    } elseif (is_array($shared_image) && isset($shared_image['url'])) {
                        $shared_image_url = $shared_image['url'];
                    }
                ?>
                <div class="testimonial-slide">
                    <div class="testimonial-card">
                        <!-- 客户分享图片 -->
                        <?php if ($shared_image_url) : ?>
                        <div class="testimonial-shared-image <?php echo $show_images_desktop === 'yes' ? 'show-desktop' : 'hide-desktop'; ?>">
                            <img src="<?php echo esc_url($shared_image_url); ?>" alt="Customer shared photo" loading="lazy">
                        </div>
                        <?php endif; ?>
                        
                        <!-- 星级评分 (保留 WordPress 原有逻辑和样式) -->
                        <div class="testimonial-rating">
                            <?php for ($i = 0; $i < 5; $i++) : ?>
                            <svg class="star <?php echo $i < $rating ? 'filled' : ''; ?>" width="20" height="20" viewBox="0 0 20 20">
                                <path d="M10 0L12.9389 6.56434L20 7.60081L15 12.4357L16.1803 19.3992L10 16.0557L3.81966 19.3992L5 12.4357L0 7.60081L7.06107 6.56434L10 0Z" fill="currentColor"/>
                            </svg>
                            <?php endfor; ?>
                        </div>
                        
                        <?php if ($testimonial_title) : ?>
                        <h4 class="testimonial-title"><?php echo esc_html($testimonial_title); ?></h4>
                        <?php endif; ?>
                        
                        <p class="testimonial-comment"><?php echo esc_html($content); ?></p>
                        
                        <div class="testimonial-reviewer">
                            <?php if ($avatar_url) : ?>
                            <img src="<?php echo esc_url($avatar_url); ?>" 
                                 alt="<?php echo esc_attr($name); ?>" 
                                 class="testimonial-avatar"
                                 loading="lazy">
                            <?php else : ?>
                            <div class="testimonial-avatar-placeholder">
                                <?php echo esc_html(strtoupper(mb_substr($name, 0, 1))); ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="testimonial-info">
                                <div class="testimonial-name"><?php echo esc_html($name); ?></div>
                                <div class="testimonial-verified">Verified Customer</div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <button class="carousel-button carousel-button-next" aria-label="Next">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>
        
        <!-- 指示器 -->
        <div class="carousel-indicators">
            <?php foreach ($testimonials as $index => $testimonial) : ?>
            <button class="indicator <?php echo $index === 0 ? 'active' : ''; ?>" 
                    data-index="<?php echo esc_attr($index); ?>"
                    aria-label="Go to slide <?php echo esc_attr($index + 1); ?>"></button>
            <?php endforeach; ?>
        </div>
    </div>
    
</section>

<style>
/* 基础布局 - 移植自 ProductTestimonials.scss */
.product-testimonials-section {
    width: 100%;
    padding: 3rem 1.25rem;
    background: #1a1a1a; /* Evershop 深色背景 */
    margin: 0;
    box-sizing: border-box;
}

.testimonials-heading {
    text-align: center;
    margin-bottom: 2rem;
    color: #ffffff;
    font-size: 2rem;
    font-weight: 900;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.testimonials-carousel {
    position: relative;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 60px;
    box-sizing: border-box;
}

/* 导航按钮 */
.carousel-button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: #ffffff;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 2;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    color: #000000;
    padding: 0;
}

.carousel-button:hover {
    background: #f5f5f5;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    transform: translateY(-50%) scale(1.05);
}

.carousel-button:active {
    transform: translateY(-50%) scale(0.95);
}

.carousel-button-prev { left: 0; }
.carousel-button-next { right: 0; }

/* 轨道容器 */
.testimonials-container {
    overflow: hidden;
    width: 100%;
}

.testimonials-track {
    display: flex;
    gap: 20px;
    transition: transform 0.5s ease-in-out;
    width: 100%;
    /* 添加 touch-action 以优化触摸体验 */
    touch-action: pan-y; 
}

.testimonial-slide {
    /* 默认桌面端样式 (3列) */
    min-width: calc((100% - 40px) / 3);
    max-width: calc((100% - 40px) / 3);
    flex-shrink: 0;
    box-sizing: border-box;
}

/* 卡片样式 - 移植自 Evershop */
.testimonial-card {
    background: #2a2a2a; /* Jay Cutler 风格 */
    border: none;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    height: 100%;
    min-height: 280px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-sizing: border-box;
}

.testimonial-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
}

/* 星级评分 - 保留 WordPress 结构但适配新背景 */
.testimonial-rating {
    display: flex;
    gap: 0.25rem;
    margin-bottom: 15px;
}

.testimonial-rating .star {
    color: #4a4a4a; /* 未选中颜色 */
}

.testimonial-rating .star.filled {
    color: #ffd700; /* 选中颜色 */
}

/* 分享图片样式 */
.testimonial-shared-image {
    width: 100%;
    margin-bottom: 15px;
    border-radius: 4px;
    overflow: hidden;
    /* 使用宽高比防止布局抖动，1:1正方形显示效果最好，类似 Instagram */
    aspect-ratio: 1 / 1;
    background: #1a1a1a;
}

.testimonial-shared-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
}

/* 图片悬停微效果 */
.testimonial-card:hover .testimonial-shared-image img {
    transform: scale(1.05);
}

/* 桌面端图片显示逻辑 */
@media (min-width: 769px) {
    .testimonial-shared-image.hide-desktop {
        display: none;
    }
}

/* 内容样式 */
.testimonial-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 15px;
    line-height: 1.4;
    margin-top: 0;
}

.testimonial-comment {
    font-size: 1rem;
    line-height: 1.625;
    color: #d1d1d1;
    margin: 0 0 20px 0;
    flex-grow: 1;
}

.testimonial-comment::before { content: '"'; }
.testimonial-comment::after { content: '"'; }

/* 评论者信息 */
.testimonial-reviewer {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: auto;
    padding-top: 15px;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.testimonial-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: none;
    flex-shrink: 0;
}

.testimonial-avatar-placeholder {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: 600;
    flex-shrink: 0;
}

.testimonial-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.testimonial-name {
    font-size: 1rem;
    font-weight: 700;
    color: #ffffff;
    line-height: 1.3;
}

.testimonial-verified {
    font-size: 0.875rem;
    color: #999999;
    line-height: 1.3;
}

/* 指示器 */
.carousel-indicators {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
}

.indicator {
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
    background: #4a4a4a;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    padding: 0;
}

.indicator:hover {
    background: #6a6a6a;
}

.indicator.active {
    background: #ffffff;
    width: 2rem;
    border-radius: 0.375rem;
}

/* 响应式布局 - 对应 JS 逻辑 */
@media (max-width: 1024px) {
    .testimonial-slide {
        /* 平板: 2列 */
        min-width: calc((100% - 20px) / 2);
        max-width: calc((100% - 20px) / 2);
    }
}

@media (max-width: 768px) {
    .testimonials-carousel {
        padding: 0 20px; /* 移动端减小内边距 */
    }
    
    .testimonial-slide {
        /* 移动端: 1列 */
        min-width: 100%;
        max-width: 100%;
    }
    
    .carousel-button {
        /* 移动端显示按钮 */
        display: flex;
        width: 32px;
        height: 32px;
        background: rgba(255,255,255,0.9);
    }

    /* 调整按钮位置以免遮挡内容，或者使其稍微小一点 */
    .carousel-button-prev { left: -10px; }
    .carousel-button-next { right: -10px; }
    
    .testimonials-heading {
        font-size: 1.5rem;
    }
}
</style>

<script>
(function() {
    const sliderId = '<?php echo esc_js($slider_id); ?>';
    const slider = document.getElementById(sliderId);
    if (!slider) return;
    
    const track = slider.querySelector('.testimonials-track');
    const slides = slider.querySelectorAll('.testimonial-slide');
    const prevBtn = slider.querySelector('.carousel-button-prev');
    const nextBtn = slider.querySelector('.carousel-button-next');
    const dots = slider.querySelectorAll('.indicator');
    
    let currentIndex = 0;
    let slidesPerView = 3;
    let isAutoPlaying = true;
    let autoPlayInterval;
    
    // 触摸滑动变量
    let touchStartX = 0;
    let touchEndX = 0;
    
    // 响应式计算 slidesPerView
    function updateSlidesPerView() {
        if (window.innerWidth <= 768) {
            slidesPerView = 1;
        } else if (window.innerWidth <= 1024) {
            slidesPerView = 2;
        } else {
            slidesPerView = 3;
        }
        
        // 重新计算并更新位置
        updateSlider();
    }
    
    // 核心滑动逻辑 - 移植自 Evershop
    function updateSlider() {
        const maxIndex = Math.max(0, slides.length - slidesPerView);
        
        // 确保索引不越界
        if (currentIndex > maxIndex) currentIndex = 0;
        if (currentIndex < 0) currentIndex = maxIndex;
        
        // 计算 transform 值
        // 公式: -currentIndex * (100% + 20px) / slidesPerView
        // 20px 是 gap 大小
        const gap = 20;
        
        // 使用 JS 计算而不是 calc() 字符串，以便更精确控制
        track.style.transform = `translateX(calc(-${currentIndex} * (100% + ${gap}px) / ${slidesPerView}))`;
        
        // 更新指示器
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentIndex);
            // 如果点数多于最大索引，隐藏多余的点（因为无法滚动到那里）
            if (index > maxIndex) {
                dot.style.display = 'none';
            } else {
                dot.style.display = 'block';
            }
        });
    }
    
    // 导航控制
    function goToNext() {
        const maxIndex = Math.max(0, slides.length - slidesPerView);
        if (currentIndex >= maxIndex) {
            currentIndex = 0;
        } else {
            currentIndex++;
        }
        updateSlider();
    }
    
    function goToPrev() {
        const maxIndex = Math.max(0, slides.length - slidesPerView);
        if (currentIndex <= 0) {
            currentIndex = maxIndex;
        } else {
            currentIndex--;
        }
        updateSlider();
    }
    
    function goToSlide(index) {
        currentIndex = index;
        updateSlider();
    }
    
    // 触摸滑动处理
    track.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
        // 触摸开始时暂停自动播放
        isAutoPlaying = false;
        clearInterval(autoPlayInterval);
    }, {passive: true});
    
    track.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
        // 触摸结束后，如果原本是自动播放状态，则尝试恢复（或保持暂停，取决于需求）
        // 这里选择：如果用户手动交互了，就停止自动播放，避免打扰用户阅读
        // 如果希望恢复，可以取消注释下一行
        // if (isAutoPlaying) startAutoPlay();
    }, {passive: true});
    
    function handleSwipe() {
        const threshold = 50; // 滑动阈值 px
        const swipeDistance = touchEndX - touchStartX;
        
        if (Math.abs(swipeDistance) > threshold) {
            if (swipeDistance < 0) {
                // 向左滑 -> 下一张
                goToNext();
            } else {
                // 向右滑 -> 上一张
                goToPrev();
            }
        }
    }
    
    // 事件监听
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            isAutoPlaying = false;
            goToPrev();
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            isAutoPlaying = false;
            goToNext();
        });
    }
    
    dots.forEach(dot => {
        dot.addEventListener('click', function() {
            isAutoPlaying = false;
            const index = parseInt(this.getAttribute('data-index'));
            goToSlide(index);
        });
    });
    
    // 自动播放
    function startAutoPlay() {
        // 清除旧的定时器，防止叠加
        if (autoPlayInterval) clearInterval(autoPlayInterval);
        
        autoPlayInterval = setInterval(() => {
            if (isAutoPlaying && slides.length > slidesPerView) {
                goToNext();
                // 保持自动播放，除非被点击中断
            }
        }, 5000);
    }
    
    // 视口变化监听
    window.addEventListener('resize', updateSlidesPerView);
    
    // 初始化
    updateSlidesPerView();
    startAutoPlay();
    
    // 鼠标悬停暂停
    slider.addEventListener('mouseenter', () => {
        // 仅仅暂停，不改变 isAutoPlaying 状态，移出后继续
        clearInterval(autoPlayInterval);
    });
    
    slider.addEventListener('mouseleave', () => {
        if (isAutoPlaying) startAutoPlay();
    });
})();
</script>
