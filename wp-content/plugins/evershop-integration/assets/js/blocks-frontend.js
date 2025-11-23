/**
 * EverShop Blocks - Frontend Script
 * Initialize Swiper carousels for Videos and Testimonials blocks
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        
        // Initialize Product Videos Carousels
        const videoBlocks = document.querySelectorAll('.evershop-product-videos .videos-grid.swiper');
        videoBlocks.forEach(function(element) {
            new Swiper(element, {
                slidesPerView: 1,
                spaceBetween: 20,
                navigation: {
                    nextEl: element.querySelector('.swiper-button-next'),
                    prevEl: element.querySelector('.swiper-button-prev'),
                },
                pagination: {
                    el: element.querySelector('.swiper-pagination'),
                    clickable: true,
                },
                breakpoints: {
                    768: {
                        slidesPerView: 2,
                    },
                    1024: {
                        slidesPerView: 3,
                    },
                },
            });
        });

        // Initialize Testimonials Carousels
        const testimonialBlocks = document.querySelectorAll('.evershop-testimonials .testimonials-slider.swiper');
        testimonialBlocks.forEach(function(element) {
            const parentBlock = element.closest('.evershop-testimonials');
            const autoplay = parentBlock.getAttribute('data-autoplay') === 'true';
            const delay = parseInt(parentBlock.getAttribute('data-delay')) || 5000;

            new Swiper(element, {
                slidesPerView: 1,
                spaceBetween: 30,
                loop: true,
                autoplay: autoplay ? {
                    delay: delay,
                    disableOnInteraction: false,
                } : false,
                navigation: {
                    nextEl: element.querySelector('.swiper-button-next'),
                    prevEl: element.querySelector('.swiper-button-prev'),
                },
                pagination: {
                    el: element.querySelector('.swiper-pagination'),
                    clickable: true,
                },
                breakpoints: {
                    768: {
                        slidesPerView: 2,
                    },
                    1024: {
                        slidesPerView: 3,
                    },
                },
            });
        });
    });
})();

