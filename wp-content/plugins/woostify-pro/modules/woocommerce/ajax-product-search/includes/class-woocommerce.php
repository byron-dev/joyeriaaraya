<?php
/**
 * Woostify Ajax Product Search Class
 *
 * @package  Woostify Pro
 */

namespace Woostify\Woocommerce;

defined( 'ABSPATH' ) || exit;


/**
 * Wocommerce hook
 */
class Woocommerce {

	/**
	 * Instance Variable
	 *
	 * @var instance
	 */
	private static $instance;


	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->hooks_add();
	}


	/**
	 * Function add custom woocommerce hook
	 *
	 * @var instance
	 */
	public function hooks_add() {

		add_action( 'woocommerce_before_shop_loop', array( $this, 'wc_price_notice' ), 10 );
		add_action( 'woostify_before_shop_loop', array( $this, 'wc_price_notice' ), 10 );
		add_action( 'woostify_before_shop_loop', array( $this, 'result_count' ), 20 );
		add_action( 'woostify_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

		add_action( 'woostify_after_shop_loop', array( $this, 'pagination' ) );
	}

	/**
	 * Output the result count text (Showing x - x of x results).
	 */
	public function result_count() {

		if ( ! wc_get_loop_prop( 'is_paginated' ) ) {
			return;
		}
		$total = ( array_key_exists( 'woo_query', $GLOBALS ) ) ? $GLOBALS['woo_query']->found_posts : wc_get_loop_prop( 'total' );
		$args  = array(
			'total'    => $total,
			'per_page' => wc_get_loop_prop( 'per_page' ),
			'current'  => wc_get_loop_prop( 'current_page' ),
		);

		wc_get_template( 'loop/result-count.php', $args );
	}

	/**
	 * WC Price Notice.
	 */
	public function wc_price_notice() {
		if ( ! empty( WC()->session ) ) { // phpcs:ignore
			?>
				<div class="woocommerce-notices-wrapper">
					<?php wc_print_notices(); ?>
				</div>
			<?php
		} else {
			return null;
		}
	}

	/**
	 * Output the pagination.
	 */
	public function pagination() {
		if ( ! wc_get_loop_prop( 'is_paginated' ) ) {
			return;
		}
		$max_num_pages = ( array_key_exists( 'woo_query', $GLOBALS ) ) ? $GLOBALS['woo_query']->max_num_pages : wc_get_loop_prop( 'total_pages' );
		$args          = array(
			'total'   => $max_num_pages,
			'current' => wc_get_loop_prop( 'current_page' ),
			'base'    => esc_url_raw( add_query_arg( 'page', '%#%', false ) ),
			'format'  => '?paged=%#%',
		);

		if ( ! wc_get_loop_prop( 'is_shortcode' ) ) {
			$args['format'] = '';
			$args['base']   = esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
		}

		wc_get_template( 'loop/pagination.php', $args );
	}


}

\Woostify\Woocommerce\Woocommerce::get_instance();

