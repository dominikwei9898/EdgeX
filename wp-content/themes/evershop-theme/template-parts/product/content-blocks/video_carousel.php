<?php
/**
 * 内容块模板：视频轮播
 * 
 * @var array $block_data
 */

if (!defined('ABSPATH')) {
    exit;
}

$title = isset($block_data['title']) ? $block_data['title'] : '';
$background_color = isset($block_data['background_color']) ? $block_data['background_color'] : '#000000';
$videos = isset($block_data['videos']) ? $block_data['videos'] : array();

// 调试：输出视频数据
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Video Carousel - Total videos: ' . count($videos));
    error_log('Video Carousel Data: ' . print_r($block_data, true));
}

if (empty($videos) || !is_array($videos)) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        echo '<!-- Video Carousel: No videos found or invalid data -->';
    }
    return;
}
?>

<section class="product-videos" style="background-color: <?php echo esc_attr($background_color); ?>;">
    <div class="page-width">
        
        <?php if ($title) : ?>
        <div class="product-videos-header">
            <h2 class="product-videos-title"><?php echo esc_html($title); ?></h2>
        </div>
        <?php endif; ?>
        
        <div class="product-videos-container">
            <button class="video-nav video-nav-prev" aria-label="上一个视频" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </button>
            
            <div class="videos-wrapper">
                <div class="videos-track" data-total="<?php echo count($videos); ?>">
                    <?php 
                    $valid_video_count = 0;
                    foreach ($videos as $index => $video) : 
                        $video_url = isset($video['video_url']) ? $video['video_url'] : '';
                        
                        // 如果没有视频URL，跳过
                        if (empty($video_url)) {
                            if (defined('WP_DEBUG') && WP_DEBUG) {
                                error_log('Video ' . $index . ': Empty URL, skipping');
                            }
                            continue;
                        }
                        
                        // 提取视频ID和平台，自动生成缩略图
                        $video_id = '';
                        $video_platform = '';
                        $thumbnail_url = '';
                        
                        if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                            $video_platform = 'youtube';
                            if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\?\/]+)/', $video_url, $matches)) {
                                $video_id = $matches[1];
                                // YouTube高清缩略图
                                $thumbnail_url = 'https://img.youtube.com/vi/' . $video_id . '/maxresdefault.jpg';
                            }
                        } 
                        // 检查是否是Vimeo链接
                        elseif (strpos($video_url, 'vimeo.com') !== false) {
                            $video_platform = 'vimeo';
                            if (preg_match('/vimeo\.com\/(\d+)/', $video_url, $matches)) {
                                $video_id = $matches[1];
                                // Vimeo缩略图
                                $thumbnail_url = 'https://vumbnail.com/' . $video_id . '.jpg';
                            }
                        }
                        // 检查是否是直接视频文件链接
                        elseif (preg_match('/\.(mp4|webm|ogg|mov)(\?.*)?$/i', $video_url)) {
                            $video_platform = 'direct';
                            $video_id = md5($video_url); // 使用URL的MD5作为唯一标识
                            // 使用占位图或视频URL本身（浏览器会显示视频第一帧）
                            $thumbnail_url = $video_url . '#t=0.1'; // 显示视频0.1秒处的帧
                        }
                        
                        // 如果无法识别任何格式，跳过
                        if (empty($video_platform)) {
                            if (defined('WP_DEBUG') && WP_DEBUG) {
                                error_log('Video ' . $index . ': Unsupported video URL format: ' . $video_url);
                            }
                            continue;
                        }
                        
                        $valid_video_count++;
                        
                        if (defined('WP_DEBUG') && WP_DEBUG) {
                            error_log('Video ' . $index . ': Platform=' . $video_platform . ', ID=' . $video_id . ', Thumbnail=' . $thumbnail_url);
                        }
                    ?>
                    <div class="video-item" 
                         data-video-url="<?php echo esc_attr($video_url); ?>"
                         data-video-id="<?php echo esc_attr($video_id); ?>"
                         data-video-platform="<?php echo esc_attr($video_platform); ?>"
                         data-index="<?php echo esc_attr($valid_video_count); ?>">
                        <div class="video-thumbnail-wrapper">
                            <!-- 缩略图层 -->
                            <div class="video-thumbnail-layer">
                                <?php if ($video_platform === 'direct') : ?>
                                    <!-- 直接视频文件：使用video标签作为缩略图 -->
                                    <video class="video-thumbnail video-thumbnail-direct" preload="metadata" muted playsinline>
                                        <source src="<?php echo esc_url($video_url); ?>#t=0.1" type="video/mp4">
                                    </video>
                                <?php else : ?>
                                    <!-- YouTube/Vimeo：使用背景图片 -->
                                    <div class="video-thumbnail" style="background-image: url('<?php echo esc_url($thumbnail_url); ?>');"></div>
                                <?php endif; ?>
                                <button class="video-play-btn" aria-label="播放视频" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                                        <circle cx="30" cy="30" r="30" fill="rgba(255,255,255,0.9)"/>
                                        <path d="M23 18L23 42L41 30L23 18Z" fill="currentColor"/>
                                    </svg>
                                </button>
                            </div>
                            <!-- 视频播放层（初始隐藏） -->
                            <div class="video-player-layer" style="display: none;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php 
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        echo '<!-- Total valid videos rendered: ' . $valid_video_count . ' -->';
                    }
                    ?>
                </div>
            </div>
            
            <button class="video-nav video-nav-next" aria-label="下一个视频" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
        </div>
        
        <!-- 视频指示器 -->
        <div class="video-dots">
            <?php 
            $dot_index = 0;
            foreach ($videos as $index => $video) : 
                // 跳过没有URL的视频
                $video_url = isset($video['video_url']) ? $video['video_url'] : '';
                if (empty($video_url)) continue;
                
                // 验证是否是有效的YouTube/Vimeo链接或视频文件
                $is_youtube = strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false;
                $is_vimeo = strpos($video_url, 'vimeo.com') !== false;
                $is_video_file = preg_match('/\.(mp4|webm|ogg|mov)(\?.*)?$/i', $video_url);
                if (!$is_youtube && !$is_vimeo && !$is_video_file) continue;
            ?>
            <button class="video-dot <?php echo $dot_index === 0 ? 'active' : ''; ?>" 
                    data-index="<?php echo esc_attr($dot_index); ?>"
                    aria-label="视频 <?php echo $dot_index + 1; ?>"
                    type="button"></button>
            <?php 
                $dot_index++;
            endforeach; ?>
        </div>
        
    </div>
</section>

<style>
/* ===== Product Videos Container ===== */
.product-videos {
    width: 100%;
    padding: 80px 0;
    overflow: hidden;
}

.product-videos .page-width {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 40px;
}

/* ===== Header ===== */
.product-videos-header {
    text-align: center;
    margin-bottom: 48px;
}

.product-videos-title {
    font-size: 42px;
    font-weight: 700;
    color: #fff;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-family: "DDCHardware", sans-serif;
}

/* ===== Videos Container ===== */
.product-videos-container {
    position: relative;
    display: flex;
    align-items: center;
    gap: 20px;
}

.videos-wrapper {
    flex: 1;
    overflow: hidden;
    position: relative;
}

.videos-track {
    display: flex;
    gap: 24px;
    justify-content: center;
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: transform;
}

/* ===== Video Item ===== */
.video-item {
    flex: 0 0 calc(33.333% - 16px);
    min-width: 280px;
    cursor: pointer;
}

.video-thumbnail-wrapper {
    position: relative;
    width: 100%;
    padding-bottom: 177.78%;
    border-radius: 12px;
    overflow: hidden;
    background: #000;
}

.video-thumbnail-layer {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    transition: opacity 0.3s ease;
}

.video-thumbnail-layer.hidden {
    opacity: 0;
    pointer-events: none;
}

.video-thumbnail {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    transition: transform 0.3s ease;
}

/* 直接视频文件缩略图 */
.video-thumbnail-direct {
    width: 100%;
    height: 100%;
    object-fit: cover;
    background: #000;
}

.video-item:hover .video-thumbnail {
    transform: scale(1.05);
}

.video-item:hover .video-thumbnail-direct {
    transform: scale(1.05);
}

/* 视频播放层 */
.video-player-layer {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.video-player-layer iframe,
.video-player-layer video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
}

.video-play-btn {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #000;
    transition: all 0.3s ease;
    padding: 0;
    line-height: 0;
    z-index: 2;
}

.video-play-btn:hover {
    transform: translate(-50%, -50%) scale(1.1);
}

.video-play-btn svg {
    filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.3));
}

/* ===== Navigation Buttons ===== */
.video-nav {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.video-nav:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: scale(1.05);
}

.video-nav:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.video-nav svg {
    width: 24px;
    height: 24px;
}

/* ===== Video Dots ===== */
.video-dots {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 40px;
}

.video-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.3);
    cursor: pointer;
    transition: all 0.3s ease;
    padding: 0;
}

.video-dot:hover {
    background: rgba(255, 255, 255, 0.5);
}

.video-dot.active {
    background: #fff;
    width: 32px;
    border-radius: 5px;
}

/* ===== Responsive Design ===== */
@media (max-width: 1024px) {
    .video-item {
        flex: 0 0 calc(50% - 12px);
        min-width: 240px;
    }
    
    .product-videos-title {
        font-size: 36px;
    }
}

@media (max-width: 768px) {
    .product-videos {
        padding: 60px 0;
    }
    
    .product-videos .page-width {
        padding: 0 20px;
    }
    
    .product-videos-title {
        font-size: 28px;
        margin-bottom: 32px;
    }
    
    .video-item {
        flex: 0 0 100%;
        min-width: 100%;
    }
    
    .video-nav {
        width: 40px;
        height: 40px;
    }
    
    .video-nav svg {
        width: 20px;
        height: 20px;
    }
}

@media (max-width: 480px) {
    .product-videos-container {
        gap: 12px;
    }
    
    .video-nav {
        width: 36px;
        height: 36px;
    }
    
    .video-play-btn svg {
        width: 48px;
        height: 48px;
    }
}
</style>

<script>
(function() {
    const track = document.querySelector('.videos-track');
    const items = document.querySelectorAll('.video-item');
    const prevBtn = document.querySelector('.video-nav-prev');
    const nextBtn = document.querySelector('.video-nav-next');
    const dots = document.querySelectorAll('.video-dot');
    
    if (!track || items.length === 0) {
        console.log('No videos found');
        return;
    }
    
    let currentIndex = 0;
    
    // 计算每屏显示的视频数量
    function getItemsPerView() {
        const width = window.innerWidth;
        if (width > 1024) return 3;
        if (width > 768) return 2;
        return 1;
    }
    
    let itemsPerView = getItemsPerView();
    const maxIndex = Math.max(0, items.length - itemsPerView);
    
    // 更新轮播位置
    function updateCarousel() {
        const offset = -(currentIndex * (100 / itemsPerView));
        track.style.transform = `translateX(${offset}%)`;
        
        // 更新导航按钮状态
        if (prevBtn) prevBtn.disabled = currentIndex === 0;
        if (nextBtn) nextBtn.disabled = currentIndex >= maxIndex;
        
        // 更新指示器
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentIndex);
        });
    }
    
    // 上一个
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            currentIndex = Math.max(0, currentIndex - 1);
            updateCarousel();
        });
    }
    
    // 下一个
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            currentIndex = Math.min(maxIndex, currentIndex + 1);
            updateCarousel();
        });
    }
    
    // 点击指示器
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentIndex = index;
            updateCarousel();
        });
    });
    
    // 播放视频 - 在原位置播放
    items.forEach(item => {
        const playBtn = item.querySelector('.video-play-btn');
        const thumbnailLayer = item.querySelector('.video-thumbnail-layer');
        const playerLayer = item.querySelector('.video-player-layer');
        
        if (!playBtn || !thumbnailLayer || !playerLayer) return;
        
        const playVideo = (e) => {
            if (e) e.stopPropagation();
            
            const videoUrl = item.dataset.videoUrl;
            const videoId = item.dataset.videoId;
            const platform = item.dataset.videoPlatform;
            
            if (!videoUrl) return;
            
            // 生成播放器HTML
            let playerHtml = '';
            
            if (platform === 'youtube' && videoId) {
                playerHtml = `<iframe src="https://www.youtube.com/embed/${videoId}?autoplay=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
            } else if (platform === 'vimeo' && videoId) {
                playerHtml = `<iframe src="https://player.vimeo.com/video/${videoId}?autoplay=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>`;
            } else if (platform === 'direct') {
                // 检测视频类型
                let videoType = 'video/mp4';
                if (videoUrl.toLowerCase().includes('.webm')) {
                    videoType = 'video/webm';
                } else if (videoUrl.toLowerCase().includes('.ogg')) {
                    videoType = 'video/ogg';
                }
                playerHtml = `<video controls autoplay playsinline><source src="${videoUrl}" type="${videoType}">您的浏览器不支持视频播放。</video>`;
            }
            
            // 显示播放器，隐藏缩略图
            if (playerHtml) {
                playerLayer.innerHTML = playerHtml;
                playerLayer.style.display = 'block';
                thumbnailLayer.classList.add('hidden');
            }
        };
        
        playBtn.addEventListener('click', playVideo);
        item.addEventListener('click', playVideo);
    });
    
    // 响应式处理
    window.addEventListener('resize', () => {
        const newItemsPerView = getItemsPerView();
        if (newItemsPerView !== itemsPerView) {
            itemsPerView = newItemsPerView;
            currentIndex = 0;
            updateCarousel();
        }
    });
    
    // 初始化
    updateCarousel();
})();
</script>
