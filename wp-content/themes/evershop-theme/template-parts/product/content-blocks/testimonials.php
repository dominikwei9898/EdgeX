<?php
/**
 * 内容块模板：客户评价
 * 
 * 对应 Jay Cutler 网站的 testimonials 区域
 * 
 * 可用变量: $block_data
 */

if (!defined('ABSPATH')) {
    exit;
}

$title = isset($block_data['title']) ? $block_data['title'] : '';
$testimonials = isset($block_data['testimonials']) ? $block_data['testimonials'] : array();

if (empty($testimonials)) {
    return;
}

$slider_id = 'testimonials-' . uniqid();
?>

<section class="component-testimonials product-testimonials">
    
    <?php if ($title) : ?>
    <h2 class="testimonials-title"><?php echo esc_html($title); ?></h2>
    <?php endif; ?>
        
        <div class="testimonials-slider" id="<?php echo esc_attr($slider_id); ?>">
            <button class="slider-prev" aria-label="Previous">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            
            <div class="testimonials-track-wrapper">
                <div class="testimonials-track">
                    <?php foreach ($testimonials as $testimonial) : 
                        $avatar = isset($testimonial['avatar']) ? $testimonial['avatar'] : '';
                        $name = isset($testimonial['name']) ? $testimonial['name'] : '';
                        $testimonial_title = isset($testimonial['title']) ? $testimonial['title'] : '';
                        $content = isset($testimonial['content']) ? $testimonial['content'] : '';
                        $rating = isset($testimonial['rating']) ? intval($testimonial['rating']) : 5;
                        
                        // 获取头像 URL
                        $avatar_url = '';
                        if (is_numeric($avatar)) {
                            $avatar_url = wp_get_attachment_image_url($avatar, 'thumbnail');
                        } elseif (is_array($avatar) && isset($avatar['url'])) {
                            $avatar_url = $avatar['url'];
                        }
                    ?>
                    <div class="testimonial-card">
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
                        
                        <?php if ($content) : ?>
                        <p class="testimonial-content"><?php echo esc_html($content); ?></p>
                        <?php endif; ?>
                        
                        <div class="testimonial-author">
                            <?php if ($avatar_url) : ?>
                            <img src="<?php echo esc_url($avatar_url); ?>" 
                                 alt="<?php echo esc_attr($name); ?>" 
                                 class="testimonial-avatar"
                                 loading="lazy">
                            <?php endif; ?>
                            
                            <div class="testimonial-author-info">
                                <div class="testimonial-name"><?php echo esc_html($name); ?></div>
                                <div class="testimonial-verified">Verified Customer</div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button class="slider-next" aria-label="Next">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>
        
        <!-- 指示器 -->
        <div class="testimonials-dots">
            <?php foreach ($testimonials as $index => $testimonial) : ?>
            <button class="carousel-dot <?php echo $index === 0 ? 'active' : ''; ?>" 
                    data-index="<?php echo esc_attr($index); ?>"
                    aria-label="Go to testimonial <?php echo esc_attr($index + 1); ?>"></button>
            <?php endforeach; ?>
        </div>
    
</section>

<style>
.component-testimonials {
    padding: 80px 0;
    background: #f9f9f9;
}

.testimonials-title {
    text-align: center;
    font-size: 42px;
    font-weight: 700;
    margin-bottom: 60px;
    text-transform: uppercase;
}

.testimonials-slider {
    position: relative;
    display: flex;
    align-items: center;
    gap: 24px;
}

.testimonials-track-wrapper {
    flex: 1;
    overflow: hidden;
}

.testimonials-track {
    display: flex;
    gap: 24px;
    transition: transform 0.5s ease;
}

.testimonial-card {
    flex: 0 0 calc(33.333% - 16px);
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.testimonial-rating {
    display: flex;
    gap: 4px;
    margin-bottom: 16px;
}

.testimonial-rating .star {
    color: #ddd;
}

.testimonial-rating .star.filled {
    color: #ffc107;
}

.testimonial-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 12px;
}

.testimonial-content {
    font-size: 15px;
    line-height: 1.6;
    color: #666;
    margin-bottom: 20px;
}

.testimonial-author {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.testimonial-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.testimonial-name {
    font-weight: 600;
    font-size: 14px;
}

.testimonial-verified {
    font-size: 12px;
    color: #46b450;
}

.slider-prev,
.slider-next {
    background: #fff;
    border: 2px solid #ddd;
    border-radius: 50%;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #333;
    transition: all 0.3s ease;
}

.slider-prev:hover,
.slider-next:hover {
    background: #2271b1;
    border-color: #2271b1;
    color: #fff;
    transform: scale(1.1);
}

.testimonials-dots {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 32px;
}

.carousel-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #ddd;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.carousel-dot.active {
    background: #2271b1;
    width: 32px;
    border-radius: 6px;
}

@media (max-width: 1024px) {
    .testimonial-card {
        flex: 0 0 calc(50% - 12px);
    }
}

@media (max-width: 768px) {
    .component-testimonials {
        padding: 60px 0;
    }
    
    .testimonials-title {
        font-size: 32px;
        margin-bottom: 40px;
    }
    
    .testimonial-card {
        flex: 0 0 100%;
    }
    
    .slider-prev,
    .slider-next {
        display: none;
    }
}
</style>

<script>
(function() {
    const slider = document.getElementById('<?php echo esc_js($slider_id); ?>');
    if (!slider) return;
    
    const track = slider.querySelector('.testimonials-track');
    const cards = slider.querySelectorAll('.testimonial-card');
    const prevBtn = slider.querySelector('.slider-prev');
    const nextBtn = slider.querySelector('.slider-next');
    const dots = slider.querySelectorAll('.carousel-dot');
    
    let currentIndex = 0;
    const cardsPerView = window.innerWidth > 1024 ? 3 : (window.innerWidth > 768 ? 2 : 1);
    const maxIndex = Math.max(0, cards.length - cardsPerView);
    
    function updateSlider() {
        const offset = -(currentIndex * (100 / cardsPerView));
        track.style.transform = `translateX(${offset}%)`;
        
        // 更新指示器
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentIndex);
        });
    }
    
    prevBtn.addEventListener('click', () => {
        currentIndex = Math.max(0, currentIndex - 1);
        updateSlider();
    });
    
    nextBtn.addEventListener('click', () => {
        currentIndex = Math.min(maxIndex, currentIndex + 1);
        updateSlider();
    });
    
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentIndex = index;
            updateSlider();
        });
    });
})();
</script>

