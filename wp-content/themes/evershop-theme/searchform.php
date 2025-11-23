<?php
/**
 * Search Form Template
 * 
 * 搜索表单模板
 * 
 * @package EverShop_Theme
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>" style="display: flex; gap: 0.5rem; width: 100%;">
    <label style="flex: 1;">
        <span class="screen-reader-text" style="position: absolute; left: -9999px;"><?php esc_html_e('Search for:', 'evershop-theme'); ?></span>
        <input type="search" 
               class="search-field" 
               placeholder="<?php esc_attr_e('Search...', 'evershop-theme'); ?>" 
               value="<?php echo get_search_query(); ?>" 
               name="s" 
               style="width: 100%; padding: 0.625rem 0.75rem; border: 1px solid var(--formFieldBorder); border-radius: 0.375rem;" />
    </label>
    <button type="submit" 
            class="search-submit" 
            style="padding: 0.625rem 1.25rem; background: var(--interactive); color: #fff; border: none; border-radius: 0.375rem; cursor: pointer;">
        <?php esc_html_e('Search', 'evershop-theme'); ?>
    </button>
</form>

