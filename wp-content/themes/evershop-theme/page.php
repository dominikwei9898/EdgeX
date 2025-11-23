<?php
/**
 * Page Template
 * 
 * 页面模板
 * 
 * @package Gym_Nutrition_Theme
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="page-width">
    
    <?php while (have_posts()) : the_post(); ?>
    
    <article id="post-<?php the_ID(); ?>" <?php post_class('single-page'); ?>>
        
        <header class="entry-header">
            <h1 class="entry-title">
                <?php the_title(); ?>
            </h1>
        </header>
        
        <?php if (has_post_thumbnail()) : ?>
            <div class="post-thumbnail">
                <?php the_post_thumbnail('large'); ?>
            </div>
        <?php endif; ?>
        
        <div class="entry-content">
            <?php
            the_content();
            
            wp_link_pages(array(
                'before' => '<div class="page-links">' . esc_html__('Pages:', 'gym-nutrition-theme'),
                'after'  => '</div>',
            ));
            ?>
        </div>
        
    </article>
    
    <?php
    // 评论
    if (comments_open() || get_comments_number()) :
        comments_template();
    endif;
    ?>
    
    <?php endwhile; ?>
    
</div>

<?php
get_footer();

