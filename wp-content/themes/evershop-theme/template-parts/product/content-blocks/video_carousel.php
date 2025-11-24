<?php
/**
 * 内容块模板：视频轮播
 * 
 * 对应 Jay Cutler 网站的视频轮播组件
 * 
 * 可用变量: $block_data
 */

if (!defined('ABSPATH')) {
    exit;
}

$title = isset($block_data['title']) ? $block_data['title'] : '';
$background_color = isset($block_data['background_color']) ? $block_data['background_color'] : '#000000';
$videos = isset($block_data['videos']) ? $block_data['videos'] : array();

if (empty($videos)) {
    return;
}
?>

<section class="component-video-carousel" 
         style="background-color: <?php echo esc_attr($background_color); ?>;">
    
    <?php if ($title) : ?>
    <h2 class="video-carousel-title"><?php echo esc_html($title); ?></h2>
    <?php endif; ?>
        
        <div class="video-carousel-container">
            <button class="video-carousel-prev" aria-label="Previous">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            
            <div class="video-carousel-track-wrapper">
                <div class="video-carousel-track">
                    <?php foreach ($videos as $index => $video) : 
                        $video_url = isset($video['video_url']) ? $video['video_url'] : '';
                        $video_file = isset($video['video_file']) ? $video['video_file'] : null;
                        $thumbnail = isset($video['thumbnail']) ? $video['thumbnail'] : null;
                        $video_title = isset($video['title']) ? $video['title'] : '';
                        
                        // 获取缩略图 URL
                        $thumbnail_url = '';
                        if (is_array($thumbnail)) {
                            $thumbnail_url = isset($thumbnail['url']) ? $thumbnail['url'] : '';
                        } elseif (is_numeric($thumbnail)) {
                            $thumbnail_url = wp_get_attachment_image_url($thumbnail, 'large');
                        }
                        
                        // 获取视频文件 URL
                        $video_file_url = '';
                        if (is_array($video_file)) {
                            $video_file_url = isset($video_file['url']) ? $video_file['url'] : '';
                        } elseif (is_numeric($video_file)) {
                            $video_file_url = wp_get_attachment_url($video_file);
                        }
                        
                        $final_video_url = $video_url ? $video_url : $video_file_url;
                    ?>
                    <div class="video-carousel-item" 
                         data-video-url="<?php echo esc_attr($final_video_url); ?>"
                         data-index="<?php echo esc_attr($index); ?>">
                        
                        <?php if ($thumbnail_url) : ?>
                        <div class="video-thumbnail">
                            <img src="<?php echo esc_url($thumbnail_url); ?>" 
                                 alt="<?php echo esc_attr($video_title); ?>"
                                 loading="lazy">
                            <button class="video-play-button" aria-label="Play video">
                                <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
                                    <circle cx="32" cy="32" r="32" fill="rgba(255,255,255,0.9)"/>
                                    <path d="M26 20L44 32L26 44V20Z" fill="currentColor"/>
                                </svg>
                            </button>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($video_title) : ?>
                        <h3 class="video-item-title"><?php echo esc_html($video_title); ?></h3>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button class="video-carousel-next" aria-label="Next">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>
        
        <!-- 视频轮播指示器 -->
        <div class="video-carousel-dots">
            <?php foreach ($videos as $index => $video) : ?>
            <button class="carousel-dot <?php echo $index === 0 ? 'active' : ''; ?>" 
                    data-index="<?php echo esc_attr($index); ?>"
                    aria-label="Go to video <?php echo esc_attr($index + 1); ?>"></button>
            <?php endforeach; ?>
        </div>
    
</section>

<!-- 视频模态框 -->
<div id="video-modal" class="video-modal" style="display: none;">
    <div class="video-modal-overlay"></div>
    <div class="video-modal-content">
        <button class="video-modal-close" aria-label="Close">&times;</button>
        <div class="video-modal-player" id="video-player"></div>
    </div>
</div>

<style>
.component-video-carousel {
    padding: 80px 0;
    color: #fff;
}

.video-carousel-title {
    text-align: center;
    font-size: 42px;
    font-weight: 700;
    margin-bottom: 48px;
    text-transform: uppercase;
}

.video-carousel-container {
    position: relative;
    display: flex;
    align-items: center;
    gap: 24px;
}

.video-carousel-track-wrapper {
    flex: 1;
    overflow: hidden;
}

.video-carousel-track {
    display: flex;
    gap: 24px;
    transition: transform 0.5s ease;
}

.video-carousel-item {
    flex: 0 0 calc(33.333% - 16px);
    cursor: pointer;
}

.video-thumbnail {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    aspect-ratio: 9/16;
}

.video-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.video-carousel-item:hover .video-thumbnail img {
    transform: scale(1.05);
}

.video-play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #000;
    transition: all 0.3s ease;
}

.video-play-button:hover {
    transform: translate(-50%, -50%) scale(1.1);
}

.video-item-title {
    margin-top: 16px;
    font-size: 16px;
    font-weight: 600;
    text-align: center;
}

.video-carousel-prev,
.video-carousel-next {
    background: rgba(255,255,255,0.2);
    border: none;
    border-radius: 50%;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #fff;
    transition: all 0.3s ease;
}

.video-carousel-prev:hover,
.video-carousel-next:hover {
    background: rgba(255,255,255,0.3);
    transform: scale(1.1);
}

.video-carousel-dots {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 32px;
}

.carousel-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.carousel-dot.active {
    background: #fff;
    width: 32px;
    border-radius: 6px;
}

/* 视频模态框样式 */
.video-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
}

.video-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.9);
}

.video-modal-content {
    position: relative;
    width: 90%;
    max-width: 1200px;
    margin: 5% auto;
    z-index: 10000;
}

.video-modal-close {
    position: absolute;
    top: -40px;
    right: 0;
    background: none;
    border: none;
    color: #fff;
    font-size: 40px;
    cursor: pointer;
    line-height: 1;
}

.video-modal-player {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    background: #000;
}

.video-modal-player iframe,
.video-modal-player video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

@media (max-width: 1024px) {
    .video-carousel-item {
        flex: 0 0 calc(50% - 12px);
    }
}

@media (max-width: 768px) {
    .video-carousel-item {
        flex: 0 0 100%;
    }
    
    .video-carousel-title {
        font-size: 32px;
    }
}
</style>

<script>
(function() {
    const carousel = document.querySelector('.video-carousel-track');
    const items = document.querySelectorAll('.video-carousel-item');
    const prevBtn = document.querySelector('.video-carousel-prev');
    const nextBtn = document.querySelector('.video-carousel-next');
    const dots = document.querySelectorAll('.carousel-dot');
    const modal = document.getElementById('video-modal');
    const modalClose = document.querySelector('.video-modal-close');
    const modalPlayer = document.getElementById('video-player');
    
    let currentIndex = 0;
    const itemsPerView = window.innerWidth > 1024 ? 3 : (window.innerWidth > 768 ? 2 : 1);
    const maxIndex = Math.max(0, items.length - itemsPerView);
    
    function updateCarousel() {
        const offset = -(currentIndex * (100 / itemsPerView));
        carousel.style.transform = `translateX(${offset}%)`;
        
        // 更新指示器
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentIndex);
        });
    }
    
    prevBtn.addEventListener('click', () => {
        currentIndex = Math.max(0, currentIndex - 1);
        updateCarousel();
    });
    
    nextBtn.addEventListener('click', () => {
        currentIndex = Math.min(maxIndex, currentIndex + 1);
        updateCarousel();
    });
    
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentIndex = index;
            updateCarousel();
        });
    });
    
    // 视频播放功能
    items.forEach(item => {
        item.addEventListener('click', () => {
            const videoUrl = item.dataset.videoUrl;
            if (!videoUrl) return;
            
            // 解析视频 URL
            let embedUrl = '';
            if (videoUrl.includes('youtube.com') || videoUrl.includes('youtu.be')) {
                const videoId = videoUrl.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/)[1];
                embedUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
            } else if (videoUrl.includes('vimeo.com')) {
                const videoId = videoUrl.match(/vimeo\.com\/(\d+)/)[1];
                embedUrl = `https://player.vimeo.com/video/${videoId}?autoplay=1`;
            } else {
                // 直接视频文件
                modalPlayer.innerHTML = `<video controls autoplay><source src="${videoUrl}" type="video/mp4"></video>`;
                modal.style.display = 'block';
                return;
            }
            
            modalPlayer.innerHTML = `<iframe src="${embedUrl}" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>`;
            modal.style.display = 'block';
        });
    });
    
    // 关闭模态框
    modalClose.addEventListener('click', () => {
        modal.style.display = 'none';
        modalPlayer.innerHTML = '';
    });
    
    modal.querySelector('.video-modal-overlay').addEventListener('click', () => {
        modal.style.display = 'none';
        modalPlayer.innerHTML = '';
    });
})();
</script>

