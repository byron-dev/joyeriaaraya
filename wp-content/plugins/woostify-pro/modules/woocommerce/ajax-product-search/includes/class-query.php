<?php
/**
 * Woostify Query Product Search Class
 *
 * @package  Woostify Pro
 */

namespace Woostify\Woocommerce;

use WP_Query;

/**
 * Woostify Product Query
 */
class Query extends WP_Query {

	/**
	 * Search with SKU
	 *
	 * @var search_by_sku
	 */
	public $search_by_sku;

	/**
	 * Search with title
	 *
	 * @var search_by_title
	 */
	public $search_by_title;

	/**
	 * Product Show Seacch Box
	 *
	 * @var product_per_page
	 */
	public $product_per_page;

	/**
	 * Search in category id
	 *
	 * @var category_id
	 */
	public $category_id;

	/**
	 * Keyword.
	 *
	 * @var keyword
	 */
	public $keyword;

	/**
	 * Remove Stock
	 *
	 * @var remove_stock
	 */
	protected $remove_stock;

	/**
	 * Constructor.
	 *
	 * @param (array) $args | Search data.
	 */
	public function __construct( $args ) {
		$this->keyword          = $args['keyword'];
		$this->category_id      = $args['cat_id'];
		$this->limit            = $args['product_per_page'];
		$this->posts_per_page   = $args['product_per_page'];
		$this->product_per_page = $args['product_per_page'];
		$this->search_by_title  = $args['search_by_title'];
		$this->search_by_sku    = $args['search_by_sku'];
		$this->paged            = $args['paged'];
		$this->orderby          = $args['orderby'];
		$this->remove_stock     = $args['outstock'];
		$this->posts            = $this->get_posts();
		$this->found_posts      = $this->products_found();
		$this->max_num_pages    = $this->max_num_pages();
	}

	/**
	 * Constructor.
	 *
	 * @return List Product.
	 */
	public function get_posts() {
		global $wpdb;
		$sql   = $this->query_string();
		$limit = $this->limit;
		$paged = $this->paged;
		$start = ( $paged - 1 ) * $limit;
		if ( -1 != $limit ) { //phpcs:ignore
			$sql .= " LIMIT $start,$limit";
		}
		$products         = $wpdb->get_results( $sql ); //phpcs:ignore
		$this->posts      = $products;
		$this->post_count = count( $this->posts );

		return $products;
	}

	/**
	 * Get SQL query string.
	 *
	 * @return Query string.
	 */
	public function query_string() {
		global $wpdb;
		$sql         = "SELECT * FROM {$wpdb->prefix}posts as p INNER JOIN {$wpdb->prefix}woostify_product_index as tproduct ON p.ID = tproduct.id";
		$cat_id      = $this->category_id;
		$keyword     = $this->keyword;
		$parse_title = $this->parse_title( $keyword );
		$parse_sku   = $this->parse_sku( $keyword );

		if ( $this->remove_stock ) {
			$sql .= " INNER JOIN {$wpdb->prefix}postmeta as meta ON p.ID = meta.post_id";
		}

		if ( $cat_id ) {
			$sql .= " INNER JOIN {$wpdb->prefix}woostify_tax_index as ttax ON tproduct.id = ttax.product_id";
		}

		$sql .= " WHERE p.post_type = 'product'";

		$sql .= " AND tproduct.status = 'enable'";

		if ( $cat_id ) {
			$sql .= " AND ttax.cat_id = $cat_id";
		}

		if ( $this->search_by_title && $this->search_by_sku ) {
			$sql .= " AND ( $parse_title OR $parse_sku )";
		} elseif ( ! $this->search_by_title && $this->search_by_sku ) {
			$sql .= " AND ( $parse_sku )";
		} elseif ( $this->search_by_title && ! $this->search_by_sku ) {
			$sql .= " AND $parse_title";
		}

		if ( $this->remove_stock ) {
			$sql .= " AND meta.meta_value = 'instock' AND meta.meta_key = '_stock_status'";
		}

		$sql .= $this->orderby();

		return $sql;
	}

	/**
	 * Parse Title.
	 *
	 * @param (string) $keyword | Keyword search product title.
	 * @return (string) sql search title.
	 */
	protected function parse_title( $keyword ) {
		$args = explode( ' ', trim( $keyword ) );
		if ( count( $args ) > 1 ) {
			$keyword = '+' . $keyword;
			$sql     = "match(tproduct.name) against('$keyword' IN BOOLEAN MODE)";
			return $sql;
		}
		$sql = "tproduct.name LIKE '%$keyword%'";
		return $sql;
	}

	/**
	 * Parse Sku.
	 *
	 * @param (string) $keyword | Keyword search product title.
	 * @return (string) sql search sku.
	 */
	protected function parse_sku( $keyword ) {
		$args = explode( ' ', trim( $keyword ) );
		if ( count( $args ) > 1 ) {
			$sql = "match(tproduct.sku) against('$keyword' IN BOOLEAN MODE) OR match(tproduct.sku_variations) against('$keyword' IN BOOLEAN MODE)";
			return $sql;
		}
		$sql = "tproduct.sku LIKE '%$keyword%' OR tproduct.sku_variations LIKE '%$keyword%'";
		return $sql;
	}
	/**
	 * Rewrite parse search.
	 *
	 * @param (null) $q | Rewrite parse search.
	 * @return List Product.
	 */
	public function parse_search( &$q ) {

		return '';
	}

	/**
	 * Get total product found.
	 *
	 * @return Number product.
	 */
	public function products_found() {
		global $post, $wpdb;
		$sql      = $this->query_string();
		$products = $wpdb->get_results( $sql ); //phpcs:ignore

		return $wpdb->num_rows;
	}

	/**
	 * Get max num pages.
	 *
	 * @return Max number page.
	 */
	public function max_num_pages() {
		$max_num_pages = 0;

		if ( $this->limit != -1 || $this->limmit != 0 ) { // phpcs:ignore
			$max_num_pages = ceil( $this->products_found() / $this->limit );
		}

		return $max_num_pages;
	}

	/**
	 * Get order by sql.
	 *
	 * @return Order by ASC|DESC.
	 */
	public function orderby() {
		$filter = $this->orderby;
		$sql    = '';

		switch ( $filter ) {
			case 'price':
				$sql = ' ORDER BY tproduct.price ASC';
				break;
			case 'price-desc':
				$sql = ' ORDER BY tproduct.price DESC';
				break;
			case 'date':
				$sql = ' ORDER BY p.created_date DESC';
				break;
			case 'rating':
				$sql = ' ORDER BY tproduct.average_rating DESC';
				break;
			case 'popularity':
				$sql = ' ORDER BY tproduct.total_sales DESC';
				break;
			default:
				$sql = '';
				break;
		}

		return $sql;
	}

}
