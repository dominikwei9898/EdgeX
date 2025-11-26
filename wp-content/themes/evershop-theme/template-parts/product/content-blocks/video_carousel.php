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
        
        <div class="product-videos-carousel-wrapper">
            <!-- 左箭头（移动端） -->
            <button class="carousel-arrow-button carousel-arrow-left" aria-label="上一个视频" type="button" style="display: none;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            
            <div class="product-videos-grid">
                    <?php 
                    $valid_video_count = 0;
                    foreach ($videos as $index => $video) : 
                        $video_url = isset($video['video_url']) ? $video['video_url'] : '';
                        
                        // 如果没有视频URL，跳过
                        if (empty($video_url)) {
                            continue;
                        }
                        
                        // 只支持直接视频文件
                        if (!preg_match('/\.(mp4|webm|ogg|mov)(\?.*)?$/i', $video_url)) {
                            continue;
                        }
                        
                        $valid_video_count++;
                        $video_id = 'video-' . $valid_video_count;
                        
                        // 获取文件扩展名并确定 MIME 类型
                        $path_info = parse_url($video_url, PHP_URL_PATH);
                        $file_ext = strtolower(pathinfo($path_info, PATHINFO_EXTENSION));
                        $mime_type = 'video/mp4'; // 默认
                        
                        switch ($file_ext) {
                            case 'webm':
                                $mime_type = 'video/webm';
                                break;
                            case 'ogg':
                            case 'ogv':
                                $mime_type = 'video/ogg';
                                break;
                            case 'mov':
                                $mime_type = 'video/quicktime';
                                break;
                        }
                    ?>
                    <div class="product-video-item" data-video-url="<?php echo esc_attr($video_url); ?>" data-video-id="<?php echo esc_attr($video_id); ?>">
                        <div class="product-video-container">
                            <!-- 加载动画 -->
                            <div class="video-loader">
                                <div class="spinner"></div>
                            </div>
                            
                            <video 
                                class="product-video lazy-video"
                                playsinline
                                muted
                                preload="none"
                                loop
                                data-video-id="<?php echo esc_attr($video_id); ?>"
                            >
                                <source data-src="<?php echo esc_url($video_url); ?>" type="<?php echo esc_attr($mime_type); ?>">
                                您的浏览器不支持视频播放。
                            </video>
                            
                            <!-- 播放按钮覆盖层 -->
                            <button class="play-button-overlay" aria-label="播放视频" type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none">
                                    <path d="M4 3.532c0-1.554 1.696-2.514 3.029-1.715l14.113 8.468c1.294.777 1.294 2.653 0 3.43L7.029 22.183c-1.333.8-3.029-.16-3.029-1.715V3.532Z" fill="#FFFFFF"/>
                                </svg>
                            </button>
                            
                            <!-- 视频控制按钮 -->
                            <div class="video-controls" style="display: none;">
                                <button class="control-button mute-toggle" aria-label="静音/取消静音" type="button">
                                    <svg class="muted-icon" width="24" height="24" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6.266 1.551a0.667 0.667 0 0 1 0.382 0.583v7.714c0 0.261 -0.161 0.482 -0.381 0.602a0.651 0.651 0 0 1 -0.703 -0.12L2.85 7.918h-1.346a1.269 1.269 0 0 1 -1.286 -1.286v-1.286c0 -0.703 0.562 -1.286 1.286 -1.286h1.346L5.563 1.671a0.651 0.651 0 0 1 0.703 -0.12Zm2.492 2.651 1.105 1.106 1.105 -1.106c0.18 -0.18 0.483 -0.18 0.663 0 0.201 0.201 0.201 0.502 0 0.683L10.526 5.991l1.106 1.104c0.201 0.201 0.201 0.502 0 0.683a0.44 0.44 0 0 1 -0.663 0l-1.106 -1.105 -1.104 1.106c-0.201 0.201 -0.502 0.201 -0.683 0 -0.201 -0.182 -0.201 -0.483 0 -0.684l1.105 -1.105L8.073 4.886c-0.2 -0.18 -0.2 -0.482 0 -0.683 0.182 -0.18 0.483 -0.18 0.684 0Z" fill="#fff"/>
                                    </svg>
                                    <svg class="unmuted-icon" style="display: none;" width="24" height="24" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M6.627 1.55c.238.101.411.342.411.583v7.714c0 .261-.173.482-.411.603a.742.742 0 0 1-.757-.121l-2.921-2.41H1.5c-.779 0-1.385-.563-1.385-1.286V5.347c0-.703.606-1.286 1.385-1.286h1.449l2.921-2.39a.742.742 0 0 1 .757-.121Zm3.721 1.447c.931.723 1.537 1.788 1.537 2.993 0 1.225-.606 2.29-1.537 2.993a.542.542 0 0 1-.735-.06c-.195-.201-.152-.502.065-.683.714-.522 1.168-1.326 1.168-2.25 0-.904-.454-1.708-1.168-2.23a.475.475 0 0 1-.065-.683.565.565 0 0 1 .735-.08ZM9.029 4.503c.476.362.779.884.779 1.487 0 .623-.303 1.145-.779 1.507a.55.55 0 0 1-.736-.081.454.454 0 0 1 .087-.663.966.966 0 0 0 .389-.763.928.928 0 0 0-.389-.743.475.475 0 0 1-.087-.683.57.57 0 0 1 .736-.061Z" fill="#fff"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php 
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        echo '<!-- Total valid videos rendered: ' . $valid_video_count . ' -->';
                    }
                    ?>
            </div>
            
            <!-- 右箭头（移动端） -->
            <button class="carousel-arrow-button carousel-arrow-right" aria-label="下一个视频" type="button" style="display: none;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
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

/* ===== Videos Carousel Wrapper ===== */
.product-videos-carousel-wrapper {
    position: relative;
    width: 100%;
}

/* ===== Videos Grid ===== */
.product-videos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    overflow-x: hidden;
    scroll-behavior: smooth;
}

/* ===== Video Item ===== */
.product-video-item {
    width: 100%;
}

.product-video-container {
    position: relative;
    width: 100%;
    padding-bottom: 177.78%; /* 9:16 比例 */
    background: #000;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
}

.product-video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* 播放按钮覆盖层 */
.play-button-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    z-index: 2;
    transition: all 0.3s ease;
}

.play-button-overlay:hover {
    transform: translate(-50%, -50%) scale(1.1);
}

.play-button-overlay svg {
    filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.5));
}

/* 视频控制按钮 */
.video-controls {
    position: absolute;
    bottom: 16px;
    right: 16px;
    z-index: 3;
}

/* 加载动画 */
.video-loader {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1;
    pointer-events: none;
    display: none;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.control-button {
    background: rgba(0, 0, 0, 0.6);
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.control-button:hover {
    background: rgba(0, 0, 0, 0.8);
    transform: scale(1.05);
}

/* 箭头按钮（仅移动端） */
.carousel-arrow-button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.9);
    color: #000;
    cursor: pointer;
    z-index: 10;
    transition: all 0.3s ease;
    display: none;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.carousel-arrow-button:hover {
    background: #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.carousel-arrow-left {
    left: 10px;
}

.carousel-arrow-right {
    right: 10px;
}

/* ===== Responsive Design ===== */
@media (max-width: 1024px) {
    .product-videos-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
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
    
    /* 移动端：改为横向滚动 */
    .product-videos-grid {
        display: flex;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        gap: 12px;
        padding: 0 20px;
        margin: 0 -20px;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE 10+ */
    }
    
    .product-videos-grid::-webkit-scrollbar {
        display: none; /* Chrome/Safari/Opera */
    }
    
    .product-video-item {
        flex: 0 0 85%;
        scroll-snap-align: center;
    }
    
    /* 移动端显示箭头（由JS控制） */
    .carousel-arrow-button {
        display: flex;
    }
    
    .video-controls {
        bottom: 12px;
        right: 12px;
    }
    
    .control-button {
        width: 36px;
        height: 36px;
    }
}

@media (max-width: 480px) {
    .product-video-item {
        flex: 0 0 90%;
    }
    
    .play-button-overlay svg {
        width: 40px;
        height: 40px;
    }
    
    .carousel-arrow-button {
        width: 36px;
        height: 36px;
    }
}
</style>

<script>
(function() {
    const grid = document.querySelector('.product-videos-grid');
    const items = document.querySelectorAll('.product-video-item');
    const leftArrow = document.querySelector('.carousel-arrow-left');
    const rightArrow = document.querySelector('.carousel-arrow-right');
    
    if (!grid || items.length === 0) {
        return;
    }
    
    // 懒加载视频观察器
    const videoObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const video = entry.target;
                const source = video.querySelector('source');
                
                if (source && source.dataset.src && !source.src) {
                    source.src = source.dataset.src;
                    video.load(); // 触发加载
                    
                    // 为了显示第一帧，我们需要 preload="metadata" 生效
                    // 但 load() 之后，浏览器会自动处理
                }
                
                // 只要开始处理了，就停止观察
                observer.unobserve(video);
            }
        });
    }, {
        rootMargin: '200px 0px', // 提前 200px 加载
        threshold: 0.01
    });

    // 检查滚动位置，显示/隐藏箭头
    function checkScrollPosition() {
        // 只在移动端（< 768px）显示箭头
        const isMobile = window.innerWidth < 768;
        
        if (!isMobile) {
            // 桌面端始终隐藏箭头
            if (leftArrow) leftArrow.style.display = 'none';
            if (rightArrow) rightArrow.style.display = 'none';
            return;
        }
        
        const { scrollLeft, scrollWidth, clientWidth } = grid;
        const canScroll = scrollWidth > clientWidth;
        
        // 移动端根据滚动位置显示箭头
        if (leftArrow) {
            leftArrow.style.display = (canScroll && scrollLeft > 0) ? 'flex' : 'none';
        }
        if (rightArrow) {
            rightArrow.style.display = (canScroll && scrollLeft < scrollWidth - clientWidth - 10) ? 'flex' : 'none';
        }
    }
    
    // 向左滚动
    function scrollLeft() {
        const scrollAmount = grid.clientWidth * 0.8;
        grid.scrollBy({
            left: -scrollAmount,
            behavior: 'smooth'
        });
    }
    
    // 向右滚动
    function scrollRight() {
        const scrollAmount = grid.clientWidth * 0.8;
        grid.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
    }
    
    // 绑定箭头按钮事件
    if (leftArrow) {
        leftArrow.addEventListener('click', scrollLeft);
    }
    if (rightArrow) {
        rightArrow.addEventListener('click', scrollRight);
    }
    
    // 监听滚动和窗口大小变化
    grid.addEventListener('scroll', checkScrollPosition);
    window.addEventListener('resize', checkScrollPosition);
    
    // 处理每个视频
    items.forEach(item => {
        const video = item.querySelector('.product-video');
        const playBtn = item.querySelector('.play-button-overlay');
        const controls = item.querySelector('.video-controls');
        const muteBtn = item.querySelector('.mute-toggle');
        const mutedIcon = item.querySelector('.muted-icon');
        const unmutedIcon = item.querySelector('.unmuted-icon');
        const loader = item.querySelector('.video-loader');
        
        if (!video || !playBtn) return;
        
        // 注册懒加载
        if (video.classList.contains('lazy-video')) {
            videoObserver.observe(video);
        }
        
        let isPlaying = false;
        let isMuted = true;
        
        // 视频状态监听，用于显示/隐藏 Loading
        video.addEventListener('loadstart', () => {
            // 开始加载时，如果用户已经点击了播放（或者正在尝试加载metadata），可以显示loader
            // 但通常 metadata 加载很快，只在 waiting 时显示更好
        });
        
        video.addEventListener('waiting', () => {
            if (loader) loader.style.display = 'block';
        });
        
        video.addEventListener('canplay', () => {
            if (loader) loader.style.display = 'none';
        });
        
        video.addEventListener('playing', () => {
            if (loader) loader.style.display = 'none';
            isPlaying = true;
            if (playBtn) playBtn.style.display = 'none';
            if (controls) controls.style.display = 'block';
        });
        
        // 播放/暂停处理
        function handlePlayPause(e) {
            if (e) e.stopPropagation();
            
            // 如果视频还没有 src (比如 IntersectionObserver 还没触发，虽然不太可能)，强制加载
            const source = video.querySelector('source');
            if (source && !source.src && source.dataset.src) {
                source.src = source.dataset.src;
                video.load();
            }
            
            if (video.paused) {
                // 显示 loading
                if (video.readyState < 3 && loader) { // 3 = HAVE_FUTURE_DATA
                     loader.style.display = 'block';
                }
                
                const playPromise = video.play();
                if (playPromise !== undefined) {
                    playPromise.catch(error => {
                        console.error('Auto-play was prevented:', error);
                        if (loader) loader.style.display = 'none';
                        // 播放失败通常是需要用户交互（已满足）或者资源错误
                    });
                }
            } else {
                video.pause();
            }
        }
        
        // 静音/取消静音处理
        function handleMuteToggle(e) {
            e.stopPropagation();
            isMuted = !isMuted;
            video.muted = isMuted;
            
            if (mutedIcon && unmutedIcon) {
                mutedIcon.style.display = isMuted ? 'block' : 'none';
                unmutedIcon.style.display = isMuted ? 'none' : 'block';
            }
        }
        
        // 视频暂停事件
        video.addEventListener('pause', () => {
            isPlaying = false;
            if (playBtn) playBtn.style.display = 'block';
            if (controls) controls.style.display = 'none';
            if (loader) loader.style.display = 'none';
        });
        
        // 绑定点击事件
        if (playBtn) {
            playBtn.addEventListener('click', handlePlayPause);
        }
        
        item.querySelector('.product-video-container').addEventListener('click', handlePlayPause);
        
        if (muteBtn) {
            muteBtn.addEventListener('click', handleMuteToggle);
        }
    });
    
    // 初始化检查
    checkScrollPosition();
})();
</script>
