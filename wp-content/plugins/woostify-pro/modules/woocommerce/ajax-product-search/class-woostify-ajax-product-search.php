<?php
/**
 * Woostify Ajax Product Search Class
 *
 * @package  Woostify Pro
 */

defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'Woostify_Ajax_Product_Search' ) ) :

	/**
	 * Woostify Ajax Product Search Class
	 */
	class Woostify_Ajax_Product_Search {

		/**
		 * Instance Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Total Product Reindex
		 *
		 * @var total_product
		 */
		protected $total_product;

		/**
		 * Last update time Reindex
		 *
		 * @var update_time
		 */
		protected $update_time;

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
			$this->define_constants();

			$woocommerce_helper = Woostify_Woocommerce_Helper::init();

			$this->includes();

			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
			add_filter( 'woostify_options_admin_submenu_label', array( $this, 'woostify_options_admin_submenu_label' ) );

			// For mobile search form.
			add_action( 'woostify_site_search_end', array( $this, 'add_search_results' ) );

			// For dialog search form.
			add_action( 'woostify_dialog_search_content_end', array( $this, 'add_search_results' ) );

			// Save settings.
			add_action( 'wp_ajax_woostify_save_ajax_search_product_options', array( $woocommerce_helper, 'save_options' ) );

			// Ajax for front end.
			add_action( 'wp_ajax_ajax_product_search', array( $this, 'ajax_product_search' ) );
			add_action( 'wp_ajax_nopriv_ajax_product_search', array( $this, 'ajax_product_search' ) );

			// Add Setting url.
			add_action( 'admin_menu', array( $this, 'add_setting_url' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );

			add_action( 'woostify-search', array( $this, 'ajax_product_search' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_style' ) );

			// Ajax reindex.
			add_action( 'wp_ajax_index_data', array( $this, 'index_data' ) );
			add_action( 'wp_ajax_nopriv_index_data', array( $this, 'index_data' ) );

			add_action( 'delete_post', array( $this, 'delete_product' ), 10 );
			add_action( 'wp_trash_post', array( $this, 'delete_product' ) );
			add_action( 'untrash_post', array( $this, 'untrash_post' ) );
			add_action( 'init', array( $this, 'session_user' ) );
			add_action( 'admin_notices', array( $this, 'admin_notice_index' ) );
			add_action( 'updated_post_meta', array( $this, 'product_meta_save' ), 10, 4 );
			add_action( 'post_updated', array( $this, 'update_table' ), 10, 3 );
			add_action( 'transition_post_status', array( $this, 'status_transitions' ), 10, 3 );
		}

		/**
		 *  Include function
		 */
		public function includes() {
			require_once WOOSTIFY_PRO_PATH . 'modules/woocommerce/ajax-product-search/includes/helper.php';
			require_once WOOSTIFY_PRO_PATH . 'modules/woocommerce/ajax-product-search/includes/class-query.php';
			require_once WOOSTIFY_PRO_PATH . 'modules/woocommerce/ajax-product-search/includes/class-woocommerce.php';
			require_once WOOSTIFY_PRO_PATH . 'modules/woocommerce/ajax-product-search/includes/class-products-render.php';
		}


		/**
		 *  Add admin Style
		 */
		public function load_admin_style() {
			wp_enqueue_script(
				'woostify_reindex',
				WOOSTIFY_PRO_MODULES_URI . 'woocommerce/ajax-product-search/js/reindex' . woostify_suffix() . '.js',
				array( 'jquery', 'suggest' ),
				WOOSTIFY_PRO_VERSION,
				true
			);

			wp_enqueue_style(
				'woostify-search-admin',
				WOOSTIFY_PRO_MODULES_URI . 'woocommerce/ajax-product-search/css/admin.css',
				array(),
				WOOSTIFY_PRO_VERSION
			);
			$admin_vars = array(
				'url'   => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'woostify_nonce' ),
			);

			wp_localize_script(
				'woostify_reindex',
				'admin',
				$admin_vars
			);
		}

		/**
		 * Define constant
		 */
		public function define_constants() {
			if ( ! defined( 'WOOSTIFY_AJAX_PRODUCT_SEARCH' ) ) {
				define( 'WOOSTIFY_AJAX_PRODUCT_SEARCH', WOOSTIFY_PRO_VERSION );
			}
		}

		/**
		 * Adds search results.
		 */
		public function add_search_results() {
			$total_product = (int) get_option( 'woostify_ajax_search_product_total', '-1' );
			?>
					<div class="search-results-wrapper">
						<div class="ajax-search-results"></div>
						<?php if ( -1 != $total_product ) : //phpcs:ignore ?>
							<div class="total-result">
								<div class="total-result-wrapper">
								</div>
							</div>
						<?php endif ?>
					</div>
			<?php
		}

		/**
		 * Sets up.
		 */
		public function scripts() {
			$options       = Woostify_Pro::get_instance()->woostify_pro_options();
			$addon_options = $this->get_options();

			// Style.
			wp_enqueue_style(
				'woostify-ajax-product-search',
				WOOSTIFY_PRO_MODULES_URI . 'woocommerce/ajax-product-search/css/style.css',
				array(),
				WOOSTIFY_PRO_VERSION
			);

			$styles = '
				.aps-highlight {
					color: ' . esc_attr( $addon_options['highlight_color'] ) . ';
				}
			';

			wp_add_inline_style( 'woostify-ajax-product-search', $styles );

			/**
			 * Script
			 */
			wp_enqueue_script(
				'woostify-ajax-product-search',
				WOOSTIFY_PRO_MODULES_URI . 'woocommerce/ajax-product-search/js/script' . woostify_suffix() . '.js',
				array( 'jquery' ),
				WOOSTIFY_PRO_VERSION,
				true
			);

			$data = array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'ajax_error' => __( 'Sorry, something went wrong. Please refresh this page and try again!', 'woostify-pro' ),
				'ajax_nonce' => wp_create_nonce( 'ajax_product_search' ),
				'url'        => WOOSTIFY_PRO_MODULES_URI . 'woocommerce/ajax-product-search/includes/search.php',
			);

			$term = get_terms( 'product_cat' );

			if ( '1' === $addon_options['filter'] && $term ) {
				$select  = '<div class="ajax-category-filter-box">';
				$select .= '<select class="ajax-product-search-category-filter">';
				$select .= '<option value="">' . esc_html__( 'All', 'woostify-pro' ) . '</option>';
				foreach ( $term as $k ) {
					$select .= '<option value="' . esc_attr( $k->term_id ) . '">' . esc_html( $k->name ) . '</option>';
				}
				$select .= '</select>';
				$select .= '</div>';

				$data['select'] = $select;
			}

			wp_localize_script(
				'woostify-ajax-product-search',
				'woostify_ajax_product_search_data',
				$data
			);
		}

		/**
		 * Update First submenu for Welcome screen.
		 */
		public function woostify_options_admin_submenu_label() {
			return true;
		}

		/**
		 * Highlight keyword
		 *
		 * @param      string $str     The string.
		 * @param      string $keyword The keyword.
		 *
		 * @return     string  Highlight string
		 */
		public function highlight_keyword( $str, $keyword ) {
			$str     = html_entity_decode( trim( $str ) );
			$keyword = wp_specialchars_decode( trim( $keyword ) );

			return str_ireplace( $keyword, '<span class="aps-highlight">' . $keyword . '</span>', $str );
		}

		/**
		 * Strip all ' ', '-', '_' character
		 *
		 * @param      string $str The string.
		 */
		public function strip_all_char( $str ) {
			$str = strtolower( $str );
			$str = str_replace( ' ', '', $str );
			$str = str_replace( '-', '', $str );
			$str = str_replace( '_', '', $str );

			return $str;
		}


		/**
		 * Ajax search product
		 */
		public function ajax_product_search() {
			check_ajax_referer( 'ajax_product_search', 'ajax_nonce', false );
			$addon_options = $this->get_options();

			$response = array();

			if ( ! isset( $_POST['ajax_product_search_keyword'] ) ) {
				wp_send_json_error();
			}
			$keyword  = sanitize_text_field( wp_unslash( $_POST['ajax_product_search_keyword'] ) );
			$cat_id   = isset( $_POST['cat_id'] ) ? sanitize_text_field( wp_unslash( $_POST['cat_id'] ) ) : array();
			$products = array();

			$args = array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => $addon_options['total_product'],
			);

			// Query by category id.
			if ( ! empty( $cat_id ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $cat_id,
				);
			}

			// Exclude out of stock products.
			if ( $addon_options['out_stock'] ) {
				$args['meta_query'][] = array(
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => 'NOT IN',
				);
			}

			// Get product visibility only.
			$product_visibility_term_ids = wc_get_product_visibility_term_ids();
			$args['tax_query'][]         = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => array( $product_visibility_term_ids['exclude-from-catalog'] ),
				'operator' => 'NOT IN',
			);

			// Get product sku.
			$sku_products = array();
			if ( $addon_options['search_by_sku'] ) {
				// Query SKU.
				$args['meta_query'][] = array(
					'key'     => '_sku',
					'value'   => $keyword,
					'compare' => 'like',
				);

				$sku_products = ! empty( get_posts( $args ) ) ? wp_list_pluck( get_posts( $args ), 'ID' ) : array();

				// For special product sku.
				$pid = wc_get_product_id_by_sku( $keyword );
				if ( $pid && ! in_array( $pid, $sku_products, true ) ) {
					array_push( $sku_products, $pid );
				}
			}

			// Search by title.
			$search_by_title = array();
			if ( $addon_options['search_by_title'] ) {
				global $wpdb;
				$sql     = "SELECT ID FROM {$wpdb->prefix}posts WHERE post_type = 'product' AND post_status = 'publish' AND post_title LIKE '%$keyword%'";
				$results = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore

				if ( ! empty( $results ) ) {
					$search_by_title = wp_list_pluck( $results, 'ID' );
				}
			} else {
				// Get product by keyword.
				$args['s'] = $keyword;
				$products  = ! empty( get_posts( $args ) ) ? wp_list_pluck( get_posts( $args ), 'ID' ) : array();
			}

			// List product id.
			$list_id = array_unique( array_merge( $products, $search_by_title, $sku_products ) );
			ob_start();
			?>
			<div class="ajax-product-search-results">
				<?php

				if ( ! empty( $list_id ) ) {
					foreach ( $list_id as $k ) {
						$product   = wc_get_product( $k );
						$image     = wp_get_attachment_image_src( get_post_thumbnail_id( $k ), 'thumbnail' );
						$image_src = $image ? $image[0] : wc_placeholder_img_src();
						$title     = get_the_title( $k );
						$price     = $product ? $product->get_price_html() : '';
						$sku       = $product ? $product->get_sku() : '';
						$in_title  = false !== strpos( $this->strip_all_char( $title ), $this->strip_all_char( $keyword ) );
						$highlight = $addon_options['search_by_title'] && $in_title ? $this->highlight_keyword( $title, $keyword ) : $title;
						?>
						<div class="aps-item">
							<a class="aps-link" href="<?php echo esc_url( $product->url ); ?>"></a>
							<img class="aps-thumbnail" src="<?php echo esc_url( $product->image ); ?>" alt="<?php echo esc_attr( $product->name ); ?>">
							<div class="asp-content">
								<h4 class="aps-title"><?php echo esc_html( $product->name ); ?></h4>
								<div class="aps-price"><?php echo esc_html( $product->price_html ); ?></div>
							</div>
						</div>
						<?php
					}
					wp_reset_postdata();
				} else {
					?>
					<div class="aps-no-posts-found">
						<?php esc_html_e( 'No products found!', 'woostify-pro' ); ?>
					</div>
				<?php } ?>
			</div>
			<?php

			$res['size']    = count( $list_id );
			$res['result']  = sprintf( /* translators: product */ _n( '%s product', '%s products', $res['size'], 'woostify-pro' ), $res['size'] );
			$res['content'] = ob_get_clean();

			wp_send_json_success( $res );

		}

		/**
		 * Add submenu
		 *
		 * @see  add_submenu_page()
		 */
		public function add_setting_url() {
			$sub_menu = add_submenu_page( 'woostify-welcome', 'Settings', __( 'Ajax Product Search', 'woostify-pro' ), 'manage_options', 'ajax-search-product-settings', array( $this, 'add_settings_page' ) );
		}

		/**
		 * Register settings
		 */
		public function register_settings() {
			register_setting( 'ajax-search-product-settings', 'woostify_ajax_search_product_category_filter' );
			register_setting( 'ajax-search-product-settings', 'woostify_ajax_search_product_remove_out_stock_product' );
			register_setting( 'ajax-search-product-settings', 'woostify_ajax_search_product_total' );
			register_setting( 'ajax-search-product-settings', 'woostify_ajax_search_product_by_title' );
			register_setting( 'ajax-search-product-settings', 'woostify_ajax_search_product_by_sku' );
			register_setting( 'ajax-search-product-settings', 'woostify_ajax_search_product_highlight_color' );
		}

		/**
		 * Get addon option
		 */
		public function get_options() {
			$options                    = array();
			$options['filter']          = get_option( 'woostify_ajax_search_product_category_filter', '0' );
			$options['out_stock']       = get_option( 'woostify_ajax_search_product_remove_out_stock_product', '0' );
			$options['total_product']   = get_option( 'woostify_ajax_search_product_total', '-1' );
			$options['search_by_title'] = get_option( 'woostify_ajax_search_product_by_title', '1' );
			$options['search_by_sku']   = get_option( 'woostify_ajax_search_product_by_sku', '1' );
			$options['highlight_color'] = get_option( 'woostify_ajax_search_product_highlight_color', '#ff0000' );

			return $options;
		}

		/**
		 * Add Settings page
		 */
		public function add_settings_page() {
			$options = $this->get_options();
			$index   = new Woostify_Index_Table();
			?>

			<div class="woostify-options-wrap woostify-featured-setting woostify-ajax-search-product-setting" data-id="ajax-search-product" data-nonce="<?php echo esc_attr( wp_create_nonce( 'woostify-ajax-search-product-setting-nonce' ) ); ?>">

				<?php Woostify_Admin::get_instance()->woostify_welcome_screen_header(); ?>

				<div class="woostify-settings-box">
					<div class="woostify-welcome-container">
						<div class="woostify-settings-content">
							<h4 class="woostify-settings-section-title"><?php esc_html_e( 'Ajax Product Search', 'woostify-pro' ); ?></h4>

							<div class="woostify-settings-section-content">
								<table class="form-table">
									<tr>
										<th><?php esc_html_e( 'Filter', 'woostify-pro' ); ?>:</th>
										<td>
											<label for="woostify_ajax_search_product_category_filter">
												<input name="woostify_ajax_search_product_category_filter" type="checkbox" id="woostify_ajax_search_product_category_filter" <?php checked( $options['filter'], '1' ); ?> value="<?php echo esc_attr( $options['filter'] ); ?>">
												<?php esc_html_e( 'Display category filter', 'woostify-pro' ); ?>
											</label>
										</td>
									</tr>

									<tr>
										<th><?php esc_html_e( 'Out stock product', 'woostify-pro' ); ?>:</th>
										<td>
											<label for="woostify_ajax_search_product_remove_out_stock_product">
												<input name="woostify_ajax_search_product_remove_out_stock_product" type="checkbox" id="woostify_ajax_search_product_remove_out_stock_product" <?php checked( $options['out_stock'], '1' ); ?> value="<?php echo esc_attr( $options['out_stock'] ); ?>">
												<?php esc_html_e( 'Remove Out of stock products in search results', 'woostify-pro' ); ?>
											</label>
										</td>
									</tr>

									<tr>
										<th><?php esc_html_e( 'Limit result', 'woostify-pro' ); ?>:</th>
										<td>
											<label for="woostify_ajax_search_product_total">
												<input name="woostify_ajax_search_product_total" type="number" id="woostify_ajax_search_product_total" value="<?php echo esc_attr( $options['total_product'] ); ?>">
											</label>
											<p class="woostify-setting-description"><?php esc_html_e( 'Enter -1 to show all the products', 'woostify-pro' ); ?></p>
										</td>
									</tr>

									<tr>
										<th><?php esc_html_e( 'Search by', 'woostify-pro' ); ?>:</th>
										<td class="must-choose-one-option">
											<p>
												<label for="woostify_ajax_search_product_by_title">
													<input name="woostify_ajax_search_product_by_title" type="checkbox" id="woostify_ajax_search_product_by_title" <?php checked( $options['search_by_title'], '1' ); ?> value="<?php echo esc_attr( $options['search_by_title'] ); ?>">
													<?php esc_html_e( 'Product title', 'woostify-pro' ); ?>
												</label>
											</p>

											<p>
												<label for="woostify_ajax_search_product_by_sku">
													<input name="woostify_ajax_search_product_by_sku" type="checkbox" id="woostify_ajax_search_product_by_sku" <?php checked( $options['search_by_sku'], '1' ); ?> value="<?php echo esc_attr( $options['search_by_sku'] ); ?>">
													<?php esc_html_e( 'Product sku', 'woostify-pro' ); ?>
												</label>
											</p>
										</td>
									</tr>

									<tr>
										<th><?php esc_html_e( 'Highlight color', 'woostify-pro' ); ?>:</th>
										<td>
											<label for="woostify_ajax_search_product_highlight_color">
												<input class="woostify-admin-color-picker" name="woostify_ajax_search_product_highlight_color" type="text" id="woostify_ajax_search_product_highlight_color" value="<?php echo esc_attr( $options['highlight_color'] ); ?>">
											</label>
										</td>
									</tr>

									<tr>
										<th><?php esc_html_e( 'Index Data', 'woostify-pro' ); ?>:</th>
										<td>
											<div class="index-data">
												<button class="button button-primary btn-index-data"><?php esc_html_e( 'Index data', 'woostify-pro' ); ?></button>
												<span class="progress"></span>
											</div>

										</td>
									</tr>
									<?php if ( $index->total_product() && $index->last_index() ) : ?>
										<tr>
											<th><?php esc_html_e( 'Last Update', 'woostify-pro' ); ?>:</th>
											<td>
												<span class="last-index"> <?php echo esc_html( $index->last_index() ); ?></span>
											</td>
										</tr>

										<tr>
											<th><?php esc_html_e( 'Total Product Index', 'woostify-pro' ); ?>:</th>
											<td>
												<span class="index-total-product"><?php echo esc_html( $index->total_product() ); ?></span>
											</td>
										</tr>
									<?php endif ?>
								</table>
							</div>

							<div class="woostify-settings-section-footer">
								<span class="save-options button button-primary"><?php esc_html_e( 'Save', 'woostify-pro' ); ?></span>
								<span class="spinner"></span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Ajax index data
		 */
		public function index_data() {
			check_ajax_referer( 'woostify_nonce' );
			global $wpdb;
			$table_name = $wpdb->prefix . 'woostify_product_index';
			$table_tax  = $wpdb->prefix . 'woostify_tax_index';
			$table_sku  = $wpdb->prefix . 'woostify_sku_index';
			// drop the table from the database.
			$wpdb->query( "DROP TABLE IF EXISTS $table_name" ); // phpcs:ignore
			$wpdb->query( "DROP TABLE IF EXISTS $table_tax" ); // phpcs:ignore
			$wpdb->query( "DROP TABLE IF EXISTS $table_sku" ); // phpcs:ignore
			$index = new Woostify_Index_Table();
			$index->create_table();
			$index->create_table_tax();
			$index->sku_table();
			$index->install_data();
			$results['message']       = __( 'Index data Complete', 'woostify' );
			$results['total_product'] = $index->total_product();
			$results['time']          = $index->last_index();

			wp_send_json_success( $results );
			wp_die();
		}

		/**
		 * Update Index tabel
		 *
		 * @param (int|string) $post_id | Post Id.
		 * @param (object)     $post | Post.
		 * @param (boolean)    $update     | True or False.
		 */
		public function update_table( $post_id, $post, $update ) {
			// If an old book is being updated, exit.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			global $wpdb;
			$table_name = $wpdb->prefix . 'woostify_product_index';
			$table_tax  = $wpdb->prefix . 'woostify_tax_index';
			$table_sku  = $wpdb->prefix . 'woostify_sku_index';
			$lang       = get_locale();
			if ( 'product' == get_post_type( $post_id ) ) { // phpcs:ignore
				$product = wc_get_product( $post_id );
				$terms   = get_the_terms( $post_id, 'product_cat' );
				$status  = 'enable';
				if ( isset( $_POST['_visibility'] ) ) { // phpcs:ignore
					$visibility = $_POST['_visibility']; // phpcs:ignore
					if ( 'catalog' == $visibility || 'hidden' == $visibility ) { // phpcs:ignore
						$status = 'disable';
					}

					$wpdb->update( // phpcs:ignore
						$table_name,
						array(
							'status' => $status,
						),
						array(
							'id' => $post_id,
						)
					);
				}

				if ( isset( $_POST['tax_input'] ) ) { // phpcs:ignore
					$product_cat = $_POST['tax_input']['product_cat']; // phpcs:ignore
					if ( ! empty( $terms ) ) {
						foreach ( $terms as $term ) {
							$term_id = $term->term_id;
							if ( $update ) {
								$sql = "SELECT id FROM $table_tax WHERE product_id = $post_id AND cat_id = $term_id";
								$id  = $wpdb->get_var( $sql ); //phpcs:ignore
								if ( $id ) {
									$wpdb->delete( // phpcs:ignore
										$table_tax,
										array(
											'id' => $id,
										)
									);
								}
							}
						}
					}

					foreach ( $product_cat as $cat_id ) {
						$time       = current_time( 'mysql' );
						$parentcats = get_ancestors( $cat_id, 'product_cat' );
						if ( ! empty( $parentcats ) ) {
							foreach ( $parentcats as $cat ) {
								$wpdb->insert( //phpcs:ignore
									$table_tax,
									array(
										'cat_id'       => $cat,
										'product_id'   => $post_id,
										'lang'         => $lang,
										'created_date' => current_time( 'mysql' ),
									)
								);
							}
						}
						$query = "INSERT INTO $table_tax ( cat_id, product_id, lang, created_date ) VALUES ( '$cat_id', '$post_id', '$lang', '$time' )";
						$wpdb->query( $query ); //phpcs:ignore
					}
				}
			}
		}

		/**
		 * Product meta save
		 *
		 * @param (int)    $meta_id | Meta Id.
		 * @param (int)    $post_id | Post Id.
		 * @param (string) $meta_key | True or False.
		 * @param (mixed)  $meta_value | Value of meta key.
		 */
		public function product_meta_save( $meta_id, $post_id, $meta_key, $meta_value ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'woostify_product_index';
			$table_tax  = $wpdb->prefix . 'woostify_tax_index';
			$table_sku  = $wpdb->prefix . 'woostify_sku_index';
			$lang       = get_locale();
			$time       = current_time( 'mysql' );

			if ( 'product' == get_post_type( $post_id ) ) { // phpcs:ignore

				if ( $meta_key == '_regular_price' ) { // phpcs:ignore

					update_post_meta( $post_id, '_regular_price', $meta_value );

					$product = wc_get_product( $post_id );
					$product = wc_get_product( $post_id );
					$product->set_regular_price( $meta_value );
					$product->set_price( $meta_value );
					$product->save();
					$wpdb->update( // phpcs:ignore
						$table_name,
						array(
							'html_price' => $product->get_price_html(),
							'price'      => $meta_value,
						),
						array(
							'id' => $post_id,
						)
					);
				}

				if ( $meta_key == '_sale_price' ) { // phpcs:ignore
					update_post_meta( $post_id, '_sale_price', $meta_value );
					$product = wc_get_product( $post_id );
					$product->set_sale_price( $meta_value );
					$product->set_price( $meta_value );
					$product->save();
					$wpdb->update( // phpcs:ignore
						$table_name,
						array(
							'html_price' => $product->get_price_html(),
							'price'      => $product->get_price(),
						),
						array(
							'id' => $post_id,
						)
					);
				}

				if ( '_sku' == $meta_key ) { // phpcs:ignore
					$wpdb->update( // phpcs:ignore
						$table_name,
						array(
							'id'  => $post_id,
							'sku' => $meta_value,
						),
						array(
							'id' => $post_id,
						)
					);
				}

				$product = wc_get_product( $post_id );

				if ( 'variable' == $product->get_type() ) { //phpcs:ignore
					$sku_array = array();
					foreach ( $product->get_visible_children( false ) as $child_id ) {
						$variation = wc_get_product( $child_id );
						if ( $variation && $variation->get_sku() ) {
							$sku_array[] = $variation->get_sku();
							$sql         = "SELECT id FROM $table_sku WHERE product_id = $post_id AND SKU = {$variation->get_sku()}";
							$id          = $wpdb->get_var( $sql ); //phpcs:ignore
							if ( $id ) {
								$wpdb->delete( // phpcs:ignore
									$table_sku,
									array(
										'id' => $id,
									)
								);
							}

							$query = "INSERT INTO $table_sku ( sku, product_id, lang, created_date ) VALUES ( '{$variation->get_sku()}', '$post_id', '$lang', '$time' )";
							$wpdb->query( $query ); //phpcs:ignore
						}
					}

					$list_sku = implode( ',', $sku_array );
					$wpdb->update( // phpcs:ignore
						$table_name,
						array(
							'html_price'     => $product->get_price_html(),
							'price'          => $product->get_price(),
							'sku_variations' => $list_sku,
						),
						array(
							'id' => $post_id,
						)
					);
				}
			}
		}

		/**
		 * Update Table when delete product.
		 *
		 * @param (string|int) $post_id }| Post Id.
		 */
		public function delete_product( $post_id ) {
			// If an old book is being updated, exit.
			global $wpdb;
			$table_name = $wpdb->prefix . 'woostify_product_index';
			if ( 'product' == get_post_type( $post_id ) ) { //phpcs:ignore
				$wpdb->delete( // phpcs:ignore
					$table_name,
					array(
						'id' => $post_id,
					)
				);
			}
		}

		/**
		 * Update table index when untrash post.
		 *
		 * @param (string|int) $post_id }| Post Id.
		 */
		public function untrash_post( $post_id ) {
			// If an old book is being updated, exit.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}
			global $wpdb;
			$lang       = get_locale();
			$time       = current_time( 'mysql' );
			$index      = new Woostify_Index_Table();
			$table_name = $wpdb->prefix . $index::DB_NAME;
			$table_tax  = $wpdb->prefix . $index::DB_TAX_NAME;
			$table_sku  = $wpdb->prefix . $index::DB_SKU_INDEX;
			if ( 'product' == get_post_type( $post_id ) ) { //phpcs:ignore
				$index->create_product( $post_id, $table_name, $table_tax, $table_sku );
			}
		}

		/**
		 * Action Customer search template.
		 *
		 * @param (string) $search_template | Custom template search.
		 */
		public function search_result_template( $search_template ) {

			if ( 'product' == get_query_var( 'post_type' ) && is_search() ) { // phpcs:ignore
				$search_template = WOOSTIFY_PRO_MODULES_PATH . 'woocommerce/ajax-product-search/templates/search.php';
			}

			return $search_template;
		}

		/**
		 * Get Currencies.
		 */
		public function get_currencies() {
			global $woocommerce_wpml;
			return $woocommerce_wpml->multi_currency->get_currencies( 'include_default = true' );
		}

		/**
		 * Get Lisst  Currencies WPML.
		 */
		public function get_list_currency() {
			$currencies    = $this->get_currencies();
			$list_currency = array();
			foreach ( $currencies as $currency => $data ) {
				$code = '';
				$lang = $data['languages'];
				foreach ( $lang as $key => $value ) {
					if ( $value ) {
						$code = $key;
					}
				}
				$list_currency[ $code ] = $currency;
			}

			return $list_currency;
		}

		/**
		 * Session User.
		 */
		public function session_user() {
			if ( ! session_id() ) {
				session_start();
			}
			global $woocommerce_wpml;
			$user                           = wp_get_current_user();
			$_SESSION['currency']           = get_woocommerce_currency_symbol();
			$_SESSION['user']               = array(
				'id'      => $user->ID,
				'roles'   => $user->roles,
				'caps'    => $user->caps,
				'allcaps' => $user->allcaps,
			);
			$_SESSION['lang']               = false;
			$_SESSION['wc_dynamic_pricing'] = false;
			$_SESSION['no_product']         = __( 'No products found!', 'woostify-pro' );
			$_SESSION['product']            = __( 'Product', 'woostify-pro' );
			$_SESSION['products']           = __( 'Products', 'woostify-pro' );
			if ( defined( 'ICL_SITEPRESS_VERSION' ) && defined( 'ICL_LANGUAGE_CODE' ) && $woocommerce_wpml && $woocommerce_wpml->multi_currency ) {
				$_SESSION['lang']     = true;
				$_SESSION['currency'] = array();

				foreach ( $this->get_list_currency() as $code => $currency ) {
					$_SESSION['currency'][ $code ] = get_woocommerce_currency_symbol( $currency );
				}

			}
			if ( class_exists( 'WC_Dynamic_Pricing' ) ) {
				$_SESSION['wc_dynamic_pricing'] = true;
			}

			session_write_close();
		}

		/**
		 * Notice index table.
		 */
		public function admin_notice_index() {
			global $wpdb;
			$table_name = $wpdb->prefix . 'woostify_product_index';
			if( ! $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) ) { // phpcs:ignore

				?>
					<div class="notice notice-error message woostify-index-notice woostify-notice">
						<div class="notice-content-wrapper">
							<div class="notice-logo">
								<img src="<?php echo esc_url( WOOSTIFY_PRO_URI . 'assets/images/logo.png' ); ?>" alt="<?php echo esc_attr( 'Woostify' ); ?>">
							</div>
							<div class="notice-content">

								<h2 class="notice-head"><?php echo esc_html__( 'Important Setup!', 'woostify-pro' ); ?></h2>

								<span class="notice-indexer">
									<?php echo esc_html__( 'Woostify Ajax Product Search requires setup to work, please go to Ajax Product Search page and click ', 'woostify-pro' ); ?>
									<?php echo '<strong>' . esc_html__( 'Index Data button.', 'woostify-pro' ) . '</strong>'; ?>
								</span>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=ajax-search-product-settings' ) ); ?>">
									<?php echo esc_html__( 'Index Now', 'woostify-pro' ); ?>
								</a>

								<span class="btn admin-btn btn-close-notice notice-dismiss">
								</span>

							</div>
						</div>
					</div>
				<?php
			}
		}

		/**
		 * Notice index table.
		 *
		 * @param (string) $new_status }| New Status.
		 * @param (string) $old_status }| Old Status.
		 * @param (object) $post }| Post Object.
		 */
		public function status_transitions( $new_status, $old_status, $post ) {
			global $wpdb;
			$index      = new Woostify_Index_Table();
			$table_name = $wpdb->prefix . $index::DB_NAME;
			$table_tax  = $wpdb->prefix . $index::DB_TAX_NAME;
			$table_sku  = $wpdb->prefix . $index::DB_SKU_INDEX;
			$post_id    = $post->ID;

			if ( 'pending' == $new_status || 'pending' == $new_status ) { // phpcs:ignore
				$this->delete_product( $post_id );
			}

			if ( 'publish' == $new_status ) { //phpcs:ignore

				if ( 'product' == get_post_type( $post_id ) ) { //phpcs:ignore
					$index->create_product( $post_id, $table_name, $table_tax, $table_sku );
				}
			}

		}

	}

	Woostify_Ajax_Product_Search::get_instance();

endif;
