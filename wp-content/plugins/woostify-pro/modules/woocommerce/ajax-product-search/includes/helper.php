<?php
/**
 * Woostify Ajax Product function
 *
 * @package  Woostify Pro
 */

/**
 * Get product result
 *
 * @param (int) $product_per_page | Product Per Page.
 */
function woostify_get_product_result( $product_per_page = null ) {
	$s               = get_query_var( 's' );
	$search_by_sku   = get_option( 'woostify_ajax_search_product_by_sku', '1' );
	$cat_id          = '';
	$search_by_title = get_option( 'woostify_ajax_search_product_by_title', '1' );
	$orderby         = get_query_var( 'orderby' );
	$paged           = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
	$out_stock       = get_option( 'woostify_ajax_search_product_remove_out_stock_product', '0' );

	if ( array_key_exists( 'cat_id', $_GET ) ) { // phpcs:ignore
		$cat_id = (int) $_GET['cat_id']; // phpcs:ignore
	}
	if ( empty( $product_per_page ) ) {
		$product_per_page = woostify_products_per_page();
	}

	$args = array(
		'product_per_page' => $product_per_page,
		'keyword'          => $s,
		'search_by_sku'    => $search_by_sku,
		'search_by_title'  => $search_by_title,
		'cat_id'           => $cat_id,
		'paged'            => $paged,
		'orderby'          => $orderby,
		'outstock'         => $out_stock,
	);

	$products             = new \Woostify\Woocommerce\Query( $args );
	$GLOBALS['woo_query'] = $products;

	return $products;
}
