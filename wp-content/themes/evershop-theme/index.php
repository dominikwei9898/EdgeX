<?php
/**
 * Main Index Template
 * 
 * 主模板文件，用于显示博客文章列表或其他内容
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
    
    <?php if (have_posts()) : ?>
    
        <div class="posts-container">
            
            <?php if (is_home() && !is_front_page()) : ?>
                <header class="page-header">
                    <h1 class="page-title"><?php single_post_title(); ?></h1>
                </header>
            <?php endif; ?>
            
            <div class="posts-grid">
                
                <?php
                while (have_posts()) :
                    the_post();
                ?>
                
                <article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>
                    
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="post-thumbnail">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('large'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <header class="entry-header">
                        <h2 class="entry-title">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h2>
                        
                        <div class="entry-meta">
                            <span class="posted-on">
                                <?php echo get_the_date(); ?>
                            </span>
                            <?php if (!post_password_required() && (comments_open() || get_comments_number())) : ?>
                                <span class="comments-link">
                                    <?php comments_popup_link(__('Leave a comment', 'gym-nutrition-theme')); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </header>
                    
                    <div class="entry-content">
                        <?php the_excerpt(); ?>
                    </div>
                    
                    <footer class="entry-footer">
                        <a href="<?php the_permalink(); ?>" class="read-more">
                            <?php esc_html_e('Read More', 'gym-nutrition-theme'); ?> →
                        </a>
                    </footer>
                    
                </article>
                
                <?php endwhile; ?>
                
            </div>
            
            <!-- Pagination -->
            <div class="pagination">
                <?php
                the_posts_pagination(array(
                    'mid_size'  => 2,
                    'prev_text' => __('← Previous', 'gym-nutrition-theme'),
                    'next_text' => __('Next →', 'gym-nutrition-theme'),
                ));
                ?>
            </div>
            
        </div>
    
    <?php else : ?>
    
        <div class="no-posts">
            <h2><?php esc_html_e('Nothing Found', 'gym-nutrition-theme'); ?></h2>
            <p><?php esc_html_e('It seems we can\'t find what you\'re looking for. Perhaps searching can help.', 'gym-nutrition-theme'); ?></p>
            <?php get_search_form(); ?>
        </div>
    
    <?php endif; ?>
    
</div>

<?php
get_footer();

