<?php
/**
 * The Template for displaying product search results
 * 
 * 产品搜索结果页面模板
 * 当用户通过搜索框搜索产品时（post_type=product），会自动使用此模板
 *
 * @package Evershop_Theme
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

?>

<!-- Search Product Template Loaded -->
<div class="page-width" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem; min-height: 300px;">

<?php
/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );
?>

<header class="woocommerce-products-header search-results-header" style="margin-bottom: 2rem; padding-top: 2rem;">
    <h1 class="woocommerce-products-header__title page-title" style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
        <?php esc_html_e( 'Search Results', 'gym-nutrition-theme' ); ?>
    </h1>
    <?php if ( get_search_query() ) : ?>
        <p class="search-query-info" style="color: #666; font-size: 1rem;">
            <?php
            printf(
                /* translators: %s: search query */
                esc_html__( 'Showing results for: "%s"', 'gym-nutrition-theme' ),
                '<strong>' . esc_html( get_search_query() ) . '</strong>'
            );
            ?>
        </p>
    <?php endif; ?>
</header>

<?php

if ( woocommerce_product_loop() ) {

	/**
	 * Hook: woocommerce_before_shop_loop.
	 *
	 * @hooked woocommerce_output_all_notices - 10
	 * @hooked woocommerce_result_count - 20
	 * @hooked woocommerce_catalog_ordering - 30
	 */
	do_action( 'woocommerce_before_shop_loop' );

	woocommerce_product_loop_start();

	if ( wc_get_loop_prop( 'total' ) ) {
		while ( have_posts() ) {
			the_post();

			/**
			 * Hook: woocommerce_shop_loop.
			 */
			do_action( 'woocommerce_shop_loop' );

			wc_get_template_part( 'content', 'product' );
		}
	}

	woocommerce_product_loop_end();

	/**
	 * Hook: woocommerce_after_shop_loop.
	 *
	 * @hooked woocommerce_pagination - 10
	 */
	do_action( 'woocommerce_after_shop_loop' );
	
} else {
	/**
	 * Hook: woocommerce_no_products_found.
	 *
	 * @hooked wc_no_products_found - 10
	 */
	?>
	<div class="no-search-results" style="text-align: center; padding: 3rem 0;">
		<h2 style="font-size: 1.5rem; margin-bottom: 1rem;">
			<?php esc_html_e( 'No products found', 'gym-nutrition-theme' ); ?>
		</h2>
		<p style="color: #666; margin-bottom: 1.5rem;">
			<?php
			printf(
				/* translators: %s: search query */
				esc_html__( 'Sorry, no products match your search for "%s". Please try again with different keywords.', 'gym-nutrition-theme' ),
				'<strong>' . esc_html( get_search_query() ) . '</strong>'
			);
			?>
		</p>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="button" style="display: inline-block; padding: 0.75rem 2rem; background: #d4772b; color: white; text-decoration: none; border-radius: 0.25rem;">
			<?php esc_html_e( 'Continue Shopping', 'gym-nutrition-theme' ); ?>
		</a>
	</div>
	<?php
}

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action( 'woocommerce_sidebar' );

?>

</div><!-- .page-width -->

<?php get_footer( 'shop' );

