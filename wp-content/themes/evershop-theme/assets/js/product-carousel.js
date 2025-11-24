/**
 * Product Carousel - Videos & Testimonials Slider
 * Vanilla JavaScript implementation
 */

(function() {
    'use strict';

    /**
     * Testimonials Carousel
     */
    class TestimonialsCarousel {
        constructor(container) {
            this.container = container;
            this.track = container.querySelector('.testimonials-track');
            this.cards = container.querySelectorAll('.testimonial-card');
            this.prevBtn = container.querySelector('.slider-prev');
            this.nextBtn = container.querySelector('.slider-next');
            
            if (!this.track || this.cards.length === 0) return;
            
            this.currentIndex = 0;
            this.cardWidth = 0;
            this.visibleCards = 1;
            this.totalCards = this.cards.length;
            this.autoplayInterval = null;
            this.autoplayDelay = 5000; // 5 seconds
            
            this.init();
        }
        
        init() {
            this.calculateDimensions();
            this.setupEventListeners();
            this.updateButtons();
            this.startAutoplay();
            
            // Recalculate on window resize
            window.addEventListener('resize', () => {
                this.calculateDimensions();
                this.goToSlide(this.currentIndex, false);
            });
        }
        
        calculateDimensions() {
            // Calculate how many cards are visible based on viewport
            const containerWidth = this.container.offsetWidth;
            
            if (window.innerWidth >= 1024) {
                this.visibleCards = 3; // Desktop: 3 cards
            } else if (window.innerWidth >= 768) {
                this.visibleCards = 2; // Tablet: 2 cards
            } else {
                this.visibleCards = 1; // Mobile: 1 card
            }
            
            // Calculate card width including gap
            this.cardWidth = containerWidth / this.visibleCards;
            
            // Set track width
            this.track.style.width = `${this.totalCards * this.cardWidth}px`;
            
            // Set each card width
            this.cards.forEach(card => {
                card.style.width = `${this.cardWidth - 20}px`; // -20px for gap
            });
        }
        
        setupEventListeners() {
            if (this.prevBtn) {
                this.prevBtn.addEventListener('click', () => {
                    this.prev();
                    this.resetAutoplay();
                });
            }
            
            if (this.nextBtn) {
                this.nextBtn.addEventListener('click', () => {
                    this.next();
                    this.resetAutoplay();
                });
            }
            
            // Pause autoplay on hover
            this.container.addEventListener('mouseenter', () => this.stopAutoplay());
            this.container.addEventListener('mouseleave', () => this.startAutoplay());
            
            // Touch/swipe support
            let touchStartX = 0;
            let touchEndX = 0;
            
            this.track.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
                this.stopAutoplay();
            });
            
            this.track.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                this.handleSwipe(touchStartX, touchEndX);
                this.startAutoplay();
            });
        }
        
        handleSwipe(startX, endX) {
            const diff = startX - endX;
            const threshold = 50; // minimum swipe distance
            
            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    this.next();
                } else {
                    this.prev();
                }
            }
        }
        
        goToSlide(index, animate = true) {
            // Clamp index
            const maxIndex = Math.max(0, this.totalCards - this.visibleCards);
            this.currentIndex = Math.max(0, Math.min(index, maxIndex));
            
            const offset = -(this.currentIndex * this.cardWidth);
            
            if (animate) {
                this.track.style.transition = 'transform 0.5s ease-in-out';
            } else {
                this.track.style.transition = 'none';
            }
            
            this.track.style.transform = `translateX(${offset}px)`;
            
            this.updateButtons();
            this.updateDots();
        }
        
        next() {
            if (this.currentIndex < this.totalCards - this.visibleCards) {
                this.goToSlide(this.currentIndex + 1);
            } else {
                // Loop back to start
                this.goToSlide(0);
            }
        }
        
        prev() {
            if (this.currentIndex > 0) {
                this.goToSlide(this.currentIndex - 1);
            } else {
                // Loop to end
                this.goToSlide(this.totalCards - this.visibleCards);
            }
        }
        
        updateButtons() {
            if (!this.prevBtn || !this.nextBtn) return;
            
            // Always enable buttons for looping
            this.prevBtn.disabled = false;
            this.nextBtn.disabled = false;
            
            // Or disable at edges (non-looping):
            // this.prevBtn.disabled = this.currentIndex === 0;
            // this.nextBtn.disabled = this.currentIndex >= this.totalCards - this.visibleCards;
        }
        
        updateDots() {
            // If you add dot indicators, update them here
        }
        
        startAutoplay() {
            if (this.totalCards <= this.visibleCards) return; // Don't autoplay if all cards visible
            
            this.autoplayInterval = setInterval(() => {
                this.next();
            }, this.autoplayDelay);
        }
        
        stopAutoplay() {
            if (this.autoplayInterval) {
                clearInterval(this.autoplayInterval);
                this.autoplayInterval = null;
            }
        }
        
        resetAutoplay() {
            this.stopAutoplay();
            this.startAutoplay();
        }
    }
    
    /**
     * Video Gallery
     */
    class VideoGallery {
        constructor(container) {
            this.container = container;
            this.videos = container.querySelectorAll('.video-item');
            this.modal = null;
            
            if (this.videos.length === 0) return;
            
            this.init();
        }
        
        init() {
            this.createModal();
            this.setupVideoClicks();
        }
        

        setupVideoClicks() {
            this.videos.forEach(video => {
                const playBtn = video.querySelector('.video-play-button');
                if (playBtn) {
                    playBtn.addEventListener('click', () => {
                        const videoUrl = video.dataset.videoUrl;
                        if (videoUrl) {
                            this.openModal(videoUrl);
                        }
                    });
                }
            });
        }
        
        getEmbedUrl(url) {
            // YouTube
            const youtubeMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/);
            if (youtubeMatch) {
                return `https://www.youtube.com/embed/${youtubeMatch[1]}?autoplay=1`;
            }
            
            // Vimeo
            const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
            if (vimeoMatch) {
                return `https://player.vimeo.com/video/${vimeoMatch[1]}?autoplay=1`;
            }
            
            return null; // Not a recognized video platform
        }
    }
    
    /**
     * Initialize when DOM is ready
     */
    function init() {
        // Initialize testimonials carousels
        const testimonialContainers = document.querySelectorAll('.product-testimonials');
        testimonialContainers.forEach(container => {
            new TestimonialsCarousel(container);
        });
        
        // Initialize video galleries
        const videoContainers = document.querySelectorAll('.product-videos');
        videoContainers.forEach(container => {
            new VideoGallery(container);
        });
    }
    
    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();

