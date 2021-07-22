<?php
/**
 * Woostify Ajax Product Search Class
 *
 * @package  Woostify Pro
 */

namespace Woostify\Woocommerce;

defined( 'ABSPATH' ) || exit;


/**
 * Woostify Ajax Product Search Class
 */
class Woostify_Query {

	/**
	 * Key Word
	 *
	 * @var total_product
	 */
	protected $keyword;

	/**
	 * Category ID
	 *
	 * @var category_id
	 */
	protected $category_id;

	/**
	 * Show total Product
	 *
	 * @var total_product
	 */
	protected $total_product;

	/**
	 * Search By SKU
	 *
	 * @var search_by_sku
	 */
	protected $search_by_sku;

	/**
	 * Search By Title
	 *
	 * @var search_by_title
	 */
	protected $search_by_title;

	/**
	 * Search By Title
	 *
	 * @var lang
	 */
	protected $lang;

	/**
	 * Search By Title
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
		$this->keyword         = $args['keyword'];
		$this->category_id     = $args['cat_id'];
		$this->total_product   = $args['total_product'];
		$this->search_by_title = $args['title'];
		$this->search_by_sku   = $args['sku'];
		$this->lang            = $args['lang'];
		$this->remove_stock    = $args['outstock'];
	}

	/**
	 * Get SQL query string.
	 *
	 * @return Query string.
	 */
	protected function query_string() {
		global $wpdb;

		$sql          = "SELECT tproduct.id, tproduct.name, tproduct.type, tproduct.max_price, tproduct.sku, tproduct.image, tproduct.url, tproduct.price, tproduct.html_price FROM {$wpdb->prefix}woostify_product_index as tproduct";
		$cat_id       = $this->category_id;
		$keyword      = $this->keyword;
		$lang         = $this->lang;
		$parse_title  = $this->parse_title( $keyword );
		$parse_sku    = $this->parse_sku( $keyword );
		$sku_valiable = $this->parse_sku_valiable( $keyword );

		if ( $this->remove_stock ) {
			$sql .= " INNER JOIN {$wpdb->prefix}postmeta as meta ON tproduct.id = meta.post_id";
		}

		if ( $cat_id ) {
			$sql .= " INNER JOIN {$wpdb->prefix}woostify_tax_index as ttax ON tproduct.id = ttax.product_id WHERE ttax.cat_id = $cat_id";
			if ( $this->search_by_title && $this->search_by_sku ) {
				$sql .= " AND ( $parse_title OR $parse_sku )";
			} elseif ( ! $this->search_by_title && $this->search_by_sku ) {
				$sql .= " AND ( $parse_sku )";
			} elseif ( $this->search_by_title && ! $this->search_by_sku ) {
				$sql .= " AND $parse_title";
			}
		} else {
			if ( $this->search_by_title && $this->search_by_sku ) {
				$sql .= " WHERE ( $parse_title OR $parse_sku )";
			} elseif ( ! $this->search_by_title && $this->search_by_sku ) {
				$sql .= " WHERE ( $parse_sku )";
			} elseif ( $this->search_by_title && ! $this->search_by_sku ) {
				$sql .= " WHERE $parse_title";
			}
		}

		if ( $lang ) {
			$sql .= " AND tproduct.lang = '$lang'";
		}

		if ( $this->remove_stock ) {
			$sql .= " AND meta.meta_value = 'instock'";
		}

		$sql = apply_filters( 'woostify_ajax_search_product_sql', $sql ); //phpcs:ignore

		if ( $this->search_by_sku ) {
			$sql .= " UNION SELECT tproduct.id, tproduct.name, tproduct.type, tproduct.max_price, tsku.sku, tproduct.image, tproduct.url, tproduct.price, tproduct.html_price FROM {$wpdb->prefix}woostify_product_index as tproduct LEFT JOIN {$wpdb->prefix}woostify_sku_index as tsku ON tproduct.id = tsku.product_id LEFT JOIN {$wpdb->prefix}postmeta as trule ON tproduct.id = trule.post_id WHERE $sku_valiable AND meta_key = '_pricing_rules'";

			if ( $lang ) {
				$sql .= " AND tproduct.lang = '$lang'";
			}
			if ( $this->remove_stock ) {
				$sql .= " AND trule.meta_value = 'instock'";
			}
		}

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
	 * Parse Sku variable.
	 *
	 * @param (string) $keyword | Keyword search product title.
	 * @return (string) sql search sku.
	 */
	protected function parse_sku_valiable( $keyword ) {
		$args = explode( ' ', trim( $keyword ) );
		if ( count( $args ) > 1 ) {
			$sql = "match(tsku.sku) against('$keyword' IN BOOLEAN MODE)";
			return $sql;
		}
		$sql = "tsku.sku LIKE '%$keyword%'";
		return $sql;
	}

	/**
	 * Count Total product.
	 *
	 * @return total product.
	 */
	protected function total_product() {
		global $wpdb;
		$sql            = $this->query_string();
		$total_products = $wpdb->get_results( $sql );  //phpcs:ignore

		return count( $total_products );
	}

	/**
	 * Total product.
	 *
	 * @return Product.
	 */
	protected function product_found() {
		global $wpdb;
		$sql           = $this->query_string();
		$total_product = $this->total_product;
		if ( -1 != $total_product ) { //phpcs:ignore
			$sql .= " LIMIT $total_product";
		}
		$products = $wpdb->get_results( $sql ); //phpcs:ignore

		return $products;
	}

	/**
	 * Total product result.
	 *
	 * @return Product with seting.
	 */
	protected function result_product() {
		$products = $this->product_found();
		if ( ! empty( $products ) ) {
			foreach ( $products as $product ) {
				$name                    = $this->hightlight( $product->name );
				$product->name_hightline = $name;
				$product->sku_hightline  = $product->sku;
				$price_default           = $product->price;
				if ( $this->search_by_sku ) {
					$product->sku_hightline = $this->hightlight( $product->sku );
				}
				if ( $_SESSION['lang'] ) {
					die('23423');
					$product->html_price = $this->price_default_html( $price_default, $product->max_price );
				}
				if ( $this->get_meta_dynamic( $product->id ) ) {
					$price = $this->get_price( $product );
					if ( $price ) {
						$product->html_price = $this->price_html( $price_default, $price );
					}
				}
			}
		}

		return $products;
	}

	/**
	 * Result Data.
	 */
	public function result() {
		$data = array(
			'product_found' => $this->total_product(),
			'products'      => $this->result_product(),
			'not_found'     => $_SESSION['no_product'],
			'product'       => $_SESSION['products'],
		);

		return $data;
	}

	/**
	 * Result Data.
	 */
	public function check_rule() {
		global $wpdb;
		$sql     = "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = '_pricing_rules'"; //phpcs:ignore

		return count( $wpdb->get_results( $sql ) ); //phpcs:ignore
	}

	/**
	 * Result Data.
	 *
	 * @param (int) $product_id | Product Id.
	 */
	public function get_meta_dynamic( $product_id ) {
		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = '_pricing_rules' AND post_id = '$product_id'"; //phpcs:ignore
		$meta_dynamic = $wpdb->get_results( $sql ); //phpcs:ignore
		if ( $meta_dynamic ) {
			return $meta_dynamic[0]->meta_value;
		}

		return false;
	}

	/**
	 * Get price for dynamic price plugin.
	 *
	 * @param (array) $product | Search data.
	 */
	public function get_price( $product ) {

		if ( $_SESSION['wc_dynamic_pricing'] && $this->check_rule() > 0 ) {
			$user  = $_SESSION['user'];
			$roles = $user['roles'];
			if ( empty( $roles ) ) {
				$roles = array(
					'unauthenticated',
				);
			}
			$prices = array();

			foreach ( unserialize( $this->get_meta_dynamic( $product->id ) ) as $key => $rule ) { //phpcs:ignore
				$start = $rule['date_from'];
				$end   = $rule['date_to'];

				if ( 'product' == $rule['collector']['type'] && $this->check_date( $start, $end ) ) { //phpcs:ignore

					$conditions      = $rule['conditions'][1];
					$type            = $conditions['type'];
					$args            = $conditions['args'];
					$default_price   = $product->price;
					$price           = false;
					$product_type    = $product->type;
					$max_price       = $product->max_price;
					$variation_rules = '';
					$parent_id       = $args['memberships'];
					if ( array_key_exists( 'memberships', $args ) ) {
						$parent_id = $args['memberships'];
					}
					if ( 'variable' == $product_type ) { //phpcs:ignore
						$variation_rules = $rule['variation_rules']['args']['type'];
					}
					$check_role = array_intersect( $args['roles'], $roles );
					if ( ( 'roles' == $args['applies_to'] && ! empty( $check_role ) ) || 'everyone' == $args['applies_to'] || in_array($args['applies_to'], $roles) || ( 'membership' == $args['applies_to'] && $this->check_user_role( $parent_id ) ) ) { //phpcs:ignore
						$current_rule = $this->check_item( $rule['rules'] );
						if ( $current_rule ) {
							$price_type   = $current_rule['type'];
							$price_amount = $current_rule['amount'];
							$price        = $this->caculater( $default_price, $price_type, $price_amount, $max_price, $variation_rules );
						}
					}

					$prices[] = $price;
				}
			}
			return max( $prices );
		}

		return false;
	}

	/**
	 * Check member role.
	 *
	 * @param (int) $parent_id | Id member role.
	 */
	public function check_user_role( $parent_id ) {
		if ( empty( $parent_id ) ) {
			return false;
		}
		$user   = $_SESSION['user'];
		$userid = $user['id'];
		global $wpdb;
		$parent_id = implode( ',', $parent_id );
		$sql     = "SELECT * FROM {$wpdb->prefix}posts WHERE post_author = $userid AND post_type ='wc_user_membership' AND post_parent IN ( $parent_id )"; //phpcs:ignore

		$user = $wpdb->get_results( $sql ); //phpcs:ignore

		if ( ! empty( $user ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Caculater price for dynamic price plugin.
	 *
	 * @param (float)  $price | product price.
	 *
	 * @param (string) $type | type caculater  price.
	 *
	 * @param (float)  $amount | discount product price.
	 *
	 * @param (float)  $max_price | max price product variable price.
	 *
	 * @param (string) $variation_rules | rule variable.
	 */
	public function caculater( $price, $type, $amount, $max_price = 0, $variation_rules = '' ) {
		$price_discount = $price;
		switch ( $type ) {
			case 'price_discount':
				if ( $price > $amount ) {
					if ( 0 == $max_price ) { // phpcs:ignore
						$price_discount = $price - $amount;
					} else {
						$min = $price - $amount;
						$max = $max_price - $amount;
						if ( 'variations' == $variation_rules ) { // phpcs:ignore
							$max = $max_price;
						}
						$price_discount = array(
							'min' => $min,
							'max' => $max,
						);
					}
				}
				break;

			case 'percentage_discount':
				if ( 0 != $amount ) { //phpcs:ignore
					if ( 0 == $max_price ) { // phpcs:ignore
						$price_discount = $price - ( $price / $amount );
					} else {
						$min = $price - ( $price / $amount );
						$max = $max_price - ( $max_price / $amount );
						if ( 'variations' == $variation_rules ) { // phpcs:ignore
							$max = $max_price;
						}

						$price_discount = array(
							'min' => $min,
							'max' => $max,
						);
					}
				}
				break;

			case 'fixed_price':
				$price_discount = $amount;
				break;

			default:
				$price_discount = $price;
				break;
		}

		return $price_discount;
	}


	/**
	 * Check rule for 1 product add cart.
	 *
	 * @param (float) $rules | list dynamic rule.
	 */
	public function check_item( $rules ) {
		$item = array();
		foreach ( $rules as $key => $rule ) {
			$item[$key] = $rule['from']; //phpcs:ignore
		}
		$key = array_search( min( $item ), $item ); //phpcs:ignore
		if ( min( $item ) <= 1 ) { //phpcs:ignore
			return $rules[ $key ]; //phpcs:ignore
		}

		return false;
	}

	/**
	 * Check rule for 1 product add cart.
	 *
	 * @param (string) $start | start date.
	 *
	 * @param (string) $end | end date.
	 */
	public function check_date( $start, $end ) {

		if ( empty( $end ) && empty( $start ) ) {
			return true;
		}
		$today = date( 'Y-m-d' ); //phpcs:ignore
		$today = strtotime( $today );
		$start = strtotime( $start );
		$end   = strtotime( $end );

		if ( $start && 0 > ( $today - $start ) ) {
			return false;
		}

		if ( $end && 0 > ( $end - $today ) ) {
			return false;
		}

		return true;
	}


	/**
	 * Price for dynamic.
	 *
	 * @param (float) $price | product price.
	 *
	 * @param (float) $max_price | product discount.
	 */
	public function price_default_html( $price, $max_price = 0 ) {
		if ( 0 != $max_price ) { //phpcs:ignore
			$html = '<del><span class="woocommerce-Price-amount amount">
				<bdi>
					<span class="woocommerce-Price-currencySymbol">' . $_SESSION['currency'][ $this->lang ] . '</span>'
					. esc_html( $price ) .
				'</bdi>
			</span></del>
			<ins><span class="woocommerce-Price-amount amount">
				<bdi>
					<span class="woocommerce-Price-currencySymbol">' . $_SESSION['currency'][ $this->lang ] . '</span>'
					. esc_html( $max_price ) .
				'</bdi>
			</span></ins>';
		} else {

			$html = '<span class="woocommerce-Price-amount amount">
				<bdi>
					<span class="woocommerce-Price-currencySymbol">' . $_SESSION['currency'][ $this->lang ] . '</span>'
					. esc_html( $price ) .
				'</bdi>
			</span>';

		}

		return $html;
	}


	/**
	 * Price for dynamic.
	 *
	 * @param (float) $price | product price.
	 *
	 * @param (float) $price_discount | product discount.
	 */
	public function price_html( $price, $price_discount ) {
		$symbol = $this->get_curency_symbol();
		var_dump( $symbol );
		die();
		if ( is_string( $price_discount ) ) {
			$html = '<del><span class="woocommerce-Price-amount amount">
				<bdi>
					<span class="woocommerce-Price-currencySymbol">' . $symbol . '</span>'
					. esc_html( $price ) .
				'</bdi>
			</span></del>
			<ins><span class="woocommerce-Price-amount amount">
				<bdi>
					<span class="woocommerce-Price-currencySymbol">' . $symbol . '</span>'
					. esc_html( $price_discount ) .
				'</bdi>
			</span></ins>';
		} else {
			if ( $price_discount['min'] != $price_discount['max'] ) { //phpcs:ignore
				$html = '<span class="woocommerce-Price-amount amount">
					<bdi>
						<span class="woocommerce-Price-currencySymbol">' . $symbol . '</span>'
						. esc_html( $price_discount['min'] ) .
					'</bdi>
				</span>'
				. ' - ' .
				'<span class="woocommerce-Price-amount amount">
					<bdi>
						<span class="woocommerce-Price-currencySymbol">' . $symbol . '</span>'
						. esc_html( $price_discount['max'] ) .
					'</bdi>
				</span>';
			} else {
				$html = '<span class="woocommerce-Price-amount amount">
					<bdi>
						<span class="woocommerce-Price-currencySymbol">' . $symbol . '</span>'
						. esc_html( $price_discount['max'] ) .
					'</bdi>
				</span>';
			}
		}

		return $html;
	}

	/**
	 * Get price default when use dynamic.
	 *
	 * @param (int) $product_id | product id.
	 */
	public function get_price_default( $product_id ) {
		global $wpdb;
		$sql   = "SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = $product_id AND meta_key ='_regular_price'"; //phpcs:ignore
		$price = $wpdb->get_results( $sql ); //phpcs:ignore

		if ( ! empty( $price ) ) {
			return $price[0]->meta_value;
		}
		return 0;
	}

	/**
	 * Get curency symbol.
	 */
	public function get_curency_symbol() {
		if ( is_string( $_SESSION['currency'] ) ) {
			return $_SESSION['currency'];
		}

		return $_SESSION['currency'][ $this->lang ];
	}

	/**
	 * Get price default when use dynamic.
	 *
	 * @param (int) $string | product id.
	 */
	public function hightlight( $string ) {
		$str     = html_entity_decode( trim( $string ) );
		$keyword = wp_specialchars_decode( trim( $this->keyword ) );
		$args    = explode( ' ', $keyword );

		if ( count( $args ) > 1 ) {
			foreach ( $args as $key ) {
				$position = stripos( $string, $key );
				$length   = strlen( $key );
				$text     = substr( $string, $position, $length );
				$name     = str_ireplace( $key, '<span class="aps-highlight">' . $text . '</span>', $str );
				$str      = $name;
			}

			return $name;
		}

		$length   = strlen( $keyword );
		$position = stripos( $string, $keyword );
		$key      = substr( $string, $position, $length );
		$name     = str_ireplace( $keyword, '<span class="aps-highlight">' . $key . '</span>', $str );

		return $name;
	}

	/**
	 * Get price default when use dynamic.
	 */
	public function wpml_option() {
		global $wpdb;
		$sql   = "SELECT * FROM {$wpdb->prefix}options WHERE option_name = '_wcml_settings' AND autoload ='yes'"; //phpcs:ignore
		$options = $wpdb->get_results( $sql ); //phpcs:ignore
		if ( $options ) {
			$options = $options[0]->option_value;
			$option  = unserialize( $options ); //phpcs:ignore
			return $option;
		}

		return false;

	}
}
