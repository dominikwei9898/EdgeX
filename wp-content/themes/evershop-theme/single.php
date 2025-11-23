<?php
/**
 * Single Post Template
 * 
 * 单篇文章模板
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
    
    <article id="post-<?php the_ID(); ?>" <?php post_class('single-post'); ?>>
        
        <header class="entry-header">
            
            <h1 class="entry-title">
                <?php the_title(); ?>
            </h1>
            
            <div class="entry-meta">
                <span class="posted-on">
                    <?php echo get_the_date(); ?>
                </span>
                <span class="byline">
                    <?php esc_html_e('by', 'gym-nutrition-theme'); ?> 
                    <span class="author"><?php the_author(); ?></span>
                </span>
                <?php if (has_category()) : ?>
                <span class="categories">
                    <?php the_category(', '); ?>
                </span>
                <?php endif; ?>
            </div>
            
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
        
        <?php if (has_tag()) : ?>
        <footer class="entry-footer">
            <div class="tags">
                <?php the_tags('<span>' . esc_html__('Tags:', 'gym-nutrition-theme') . '</span> ', ', '); ?>
            </div>
        </footer>
        <?php endif; ?>
        
    </article>
    
    <?php
    // 文章导航
    the_post_navigation(array(
        'prev_text' => '<span class="nav-subtitle">' . esc_html__('Previous:', 'gym-nutrition-theme') . '</span> <span class="nav-title">%title</span>',
        'next_text' => '<span class="nav-subtitle">' . esc_html__('Next:', 'gym-nutrition-theme') . '</span> <span class="nav-title">%title</span>',
    ));
    ?>
    
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

