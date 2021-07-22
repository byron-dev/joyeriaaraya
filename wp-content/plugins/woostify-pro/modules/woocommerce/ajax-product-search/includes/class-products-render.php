<?php
/**
 * Woostify Products Renderer
 *
 * @package Woostify Pro
 */

namespace Woostify\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * This class describes a woostify products renderer.
 */
class Products_Render extends \WC_Shortcode_Products {
	/**
	 * Settings
	 *
	 * @var $settings
	 */
	private $settings = array();

	/**
	 * Filter
	 *
	 * @var $is_added_product_filter
	 */
	private $is_added_product_filter = false;

	/**
	 * Shortcode type.
	 *
	 * @since 3.2.0
	 * @var   string
	 */
	protected $type = 'custom';

	/**
	 * Search data.
	 *
	 * @since 3.2.0
	 * @var   string
	 */
	protected $keyword;

	/**
	 * Constructs a new instance.
	 *
	 * @param      array  $settings The settings.
	 * @param      string $data     The type.
	 */
	public function __construct( $settings = array(), $data ) {
		$this->settings   = $settings;
		$this->data       = $data;
		$this->attributes = $this->parse_attributes(
			array(
				'columns'  => $settings['col'],
				'paginate' => $settings['pro_pagi'],
				'cache'    => false,
			)
		);
		$this->query_args = $this->parse_query_args();
	}

	/**
	 * Loop over found products.
	 *
	 * @since  3.2.0
	 * @return string
	 */
	protected function product_loop() {
		$columns  = absint( $this->attributes['columns'] );
		$classes  = $this->get_wrapper_classes( $columns );
		$products = $this->get_query_results();

		ob_start();

		if ( $products && $products->ids ) {
			// Prime caches to reduce future queries.
			if ( is_callable( '_prime_post_caches' ) ) {
				_prime_post_caches( $products->ids );
			}

			// Setup the loop.
			wc_setup_loop(
				array(
					'columns'      => $columns,
					'name'         => $this->type,
					'is_shortcode' => true,
					'is_search'    => false,
					'is_paginated' => wc_string_to_bool( $this->attributes['paginate'] ),
					'total'        => $products->total,
					'total_pages'  => $products->total_pages,
					'per_page'     => $products->per_page,
					'current_page' => $products->current_page,
				)
			);

			$original_post = $GLOBALS['post'];

			do_action( "woocommerce_shortcode_before_{$this->type}_loop", $this->attributes );

			// Fire standard shop loop hooks when paginating results so we can show result counts and so on.
			?>
			<div class="woostify-sorting">
				<?php do_action( 'woostify_before_shop_loop' ); ?>
			</div>
			<?php

			woocommerce_product_loop_start();

			if ( wc_get_loop_prop( 'total' ) ) {
				foreach ( $products->ids as $product_id ) {
					$GLOBALS['post'] = get_post( $product_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					setup_postdata( $GLOBALS['post'] );

					// Set custom product visibility when quering hidden products.
					add_action( 'woocommerce_product_is_visible', array( $this, 'set_product_as_visible' ) );

					// Render product template.
					wc_get_template_part( 'content', 'product' );

					// Restore product visibility.
					remove_action( 'woocommerce_product_is_visible', array( $this, 'set_product_as_visible' ) );
				}
			}

			$GLOBALS['post'] = $original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			woocommerce_product_loop_end();

			// Fire standard shop loop hooks when paginating results so we can show result counts and so on.
			if ( wc_string_to_bool( $this->attributes['paginate'] ) ) {
				do_action( 'woostify_after_shop_loop' );
			}

			do_action( "woocommerce_shortcode_after_{$this->type}_loop", $this->attributes );

			wp_reset_postdata();
			wc_reset_loop();
		} else {
			do_action( "woocommerce_shortcode_{$this->type}_loop_no_results", $this->attributes );
		}

		return '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">' . ob_get_clean() . '</div>';
	}


	/**
	 * Run the query and return an array of data, including queried ids and pagination information.
	 *
	 * @since  3.3.0
	 * @return object Object with the following props; ids, per_page, found_posts, max_num_pages, current_page
	 */
	protected function get_query_results() {
		$transient_name    = $this->get_transient_name();
		$transient_version = \WC_Cache_Helper::get_transient_version( 'product_query' );
		$cache             = wc_string_to_bool( $this->attributes['cache'] ) === true;
		$transient_value   = $cache ? get_transient( $transient_name ) : false;

		if ( isset( $transient_value['value'], $transient_value['version'] ) && $transient_value['version'] === $transient_version ) {
			$results = $transient_value['value'];
		} else {
			$query     = new Query( $this->query_args );
			$paginated = ! $query->get( 'no_found_rows' );
			$list_id   = array_column( $query->posts, 'ID' );

			$results = (object) array(
				'ids'          => wp_parse_id_list( $list_id ),
				'total'        => $paginated ? (int) $query->found_posts : count( $query->posts ),
				'total_pages'  => $paginated ? (int) $query->max_num_pages : 1,
				'per_page'     => (int) $query->posts_per_page,
				'current_page' => $paginated ? (int) max( 1, $query->paged ) : 1,
			);

			if ( $cache ) {
				$transient_value = array(
					'version' => $transient_version,
					'value'   => $results,
				);
				set_transient( $transient_name, $transient_value, DAY_IN_SECONDS * 30 );
			}
		}

		// Remove ordering query arguments which may have been added by get_catalog_ordering_args.
		WC()->query->remove_ordering_args();

		/**
		 * Filter shortcode products query results.
		 *
		 * @since 4.0.0
		 * @param stdClass $results Query results.
		 * @param WC_Shortcode_Products $this WC_Shortcode_Products instance.
		 */
		return apply_filters( 'woocommerce_shortcode_products_query_results', $results, $this );
	}

	/**
	 * Parse query
	 */
	protected function parse_query_args() {
		$settings = &$this->settings;

		$query_args = array(
			'post_type'   => 'product',
			'post_status' => 'publish',
			'cache'       => false,
			'orderby'     => $settings['order_by'],
			'order'       => $settings['order'],
		);

		$query_args = array_merge( $query_args, $this->data );

		// Query latest product.
		if ( 'latest' === $settings['source'] ) {
			$ordering_args         = WC()->query->get_catalog_ordering_args( $query_args['orderby'], $query_args['order'] );
			$query_args['orderby'] = $ordering_args['orderby'];
			$query_args['order']   = $ordering_args['order'];
		}

		$query_args['meta_query'] = WC()->query->get_meta_query(); // phpcs:ignore
		$query_args['tax_query']  = array(); // phpcs:ignore

		$front_page = is_front_page();
		if ( 'yes' === $settings['pro_pagi'] && 'yes' === $settings['ordering'] && ! $front_page ) {
			$ordering_args = WC()->query->get_catalog_ordering_args();
		} else {
			$ordering_args = WC()->query->get_catalog_ordering_args( $query_args['orderby'], $query_args['order'] );
		}

		$query_args['orderby'] = $ordering_args['orderby'];
		$query_args['order']   = $ordering_args['order'];
		if ( $ordering_args['meta_key'] ) {
			$query_args['meta_key'] = $ordering_args['meta_key']; // phpcs:ignore
		}

		// Visibility.
		$this->set_visibility_query_args( $query_args );

		// Set specific types query args.
		if ( method_exists( $this, "set_{$this->type}_query_args" ) ) {
			$this->{"set_{$this->type}_query_args"}( $query_args );
		}

		// Manual selection.
		$this->manual_select_query_args( $query_args );

		// Remove default notices.
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_output_all_notices', 10 );

		// Pagination.
		if ( 'yes' === $settings['pro_pagi'] ) {
			$page = isset( $_GET['page'] ) ? intval( wp_unslash( $_GET['page'] ) ) : 1; // phpcs:ignore

			if ( 1 < $page ) {
				$query_args['paged'] = $page;
			}

			// Ordering.
			if ( 'yes' === $settings['ordering'] ) {
				add_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
			} else {
				remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
			}

			// Result count.
			if ( 'yes' === $settings['result_count'] ) {
				add_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
			} else {
				remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
			}
		}

		// Products columns.
		add_filter( 'woostify_product_catalog_columns', array( $this, 'set_product_columns' ) );

		// Fallback to the widget's default settings in case settings was left empty.
		$columns                        = empty( $settings['col'] ) ? 4 : $settings['col'];
		$query_args['product_per_page'] = $settings['count'];

		// Update orderby.
		if ( isset( $query_args['orderby'] ) && in_array( $query_args['orderby'], array( 'rating', 'popularity', 'price' ), true ) ) {
			switch ( $query_args['orderby'] ) {
				case 'rating':
					$query_args['meta_key'] = '_wc_average_rating'; // phpcs:ignore
					$query_args['order']    = 'DESC';
					break;
				case 'popularity':
					$query_args['meta_key'] = 'total_sales'; // phpcs:ignore
					$query_args['order']    = 'DESC';
					break;
				default:
					$query_args['meta_key'] = '_price'; // phpcs:ignore
					break;
			}

			$query_args['orderby'] = 'meta_value_num';
		}

		$query_args = apply_filters( 'woocommerce_shortcode_products_query', $query_args, $this->attributes, $this->type );

		// Always query only IDs.
		$query_args['fields'] = 'ids';

		return $query_args;
	}

	/**
	 * Product columns
	 *
	 * @param string $class The product class.
	 */
	public function set_product_columns( $class ) {
		$settings = &$this->settings;
		$classs[] = 'products';
		$classs[] = 'columns-' . $settings['col'];
		$classs[] = 'tablet-columns-' . $settings['col_tablet'];
		$classs[] = 'mobile-columns-' . $settings['col_mobile'];

		return esc_attr( implode( ' ', $classs ) );
	}

	/**
	 * Manual selection
	 *
	 * @param      array $query_args  The query arguments.
	 */
	protected function manual_select_query_args( &$query_args ) {
		$settings = &$this->settings;
		if ( 'current_query' === $settings['source'] ) {
			return;
		}

		switch ( $settings['source'] ) {
			case 'sale':
				parent::set_sale_products_query_args( $query_args );
				$post__in = wc_get_product_ids_on_sale();
				if ( ! empty( $post__in ) ) {
					$query_args['post__in'] = $post__in;
					remove_action( 'pre_get_posts', array( wc()->query, 'product_query' ) );
				}
				break;
			case 'featured':
				$product_visibility_term_ids = wc_get_product_visibility_term_ids();
				if ( ! empty( $product_visibility_term_ids['featured'] ) ) {
					$query_args['tax_query'][] = array(
						'taxonomy' => 'product_visibility',
						'field'    => 'term_taxonomy_id',
						'terms'    => array( $product_visibility_term_ids['featured'] ),
					);
				}
				break;
			case 'select':
				if ( ! empty( $settings['include_products'] ) ) {
					$query_args['post__in'] = $settings['include_products'];
				}
				break;
		}

		// Not apply for 'select' query select product.
		if ( in_array( $settings['source'], array( 'sale', 'featured', 'latest' ), true ) ) {
			// Products.
			if ( ! empty( $settings['exclude_products'] ) ) {
				$query_args['post__not_in'] = empty( $query_args['post__in'] ) ? $settings['exclude_products'] : array_diff( $query_args['post__in'], $settings['exclude_products'] );
			}

			// Terms.
			global $wp_taxonomies;
			$in_term_id = empty( $settings['include_terms'] ) ? array() : $settings['include_terms'];
			$ex_term_id = empty( $settings['exclude_terms'] ) ? array() : $settings['exclude_terms'];

			$include_term_ids = array_diff( $in_term_id, $ex_term_id );
			$exclude_term_ids = empty( $in_term_id ) && ! empty( $ex_term_id ) ? $ex_term_id : array();

			if ( ! empty( $include_term_ids ) ) {
				foreach ( $include_term_ids as $in ) {
					$in_term = get_term( $in );

					$query_args['tax_query'][] = array(
						'taxonomy' => $wp_taxonomies[ $in_term->taxonomy ]->name,
						'field'    => 'term_id',
						'terms'    => $in,
					);
				}
			} elseif ( ! empty( $exclude_term_ids ) ) {
				foreach ( $exclude_term_ids as $ex ) {
					$ex_term = get_term( $ex );

					$query_args['tax_query'][] = array(
						'taxonomy' => $wp_taxonomies[ $ex_term->taxonomy ]->name,
						'field'    => 'term_id',
						'terms'    => $ex,
						'operator' => 'NOT IN',
					);
				}
			}
		}
	}
}
