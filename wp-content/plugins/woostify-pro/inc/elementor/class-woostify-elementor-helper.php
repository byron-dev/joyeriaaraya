<?php
/**
 * Elementor Helper
 *
 * @package  Woostify Pro
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Woostify_Elementor_Helper' ) ) {
	/**
	 * Main Class
	 */
	class Woostify_Elementor_Helper {

		/**
		 * Instance
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 *  Initiator
		 */
		public static function init() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Woostify Pro Constructor.
		 */
		public function __construct() {
			add_action( 'elementor/controls/controls_registered', array( $this, 'add_custom_controls' ) );
			add_action( 'wp_ajax_woostify_autocomplete_selected', array( $this, 'woostify_autocomplete_selected' ) );
			add_action( 'wp_ajax_woostify_autocomplete_search', array( $this, 'woostify_autocomplete_search' ) );
		}

		/**
		 * Add custom controls
		 */
		public function add_custom_controls() {
			$controls_manager = \Elementor\Plugin::$instance->controls_manager;

			require_once WOOSTIFY_PRO_PATH . 'inc/elementor/controls/class-woostify-autocomplete-control.php';

			$controls_manager->register_control( 'autocomplete', new Woostify_Autocomplete_Control() );
		}

		/**
		 * Selected
		 */
		public function woostify_autocomplete_selected() {
			check_ajax_referer( 'woostify-autocomplete', 'security_nonce' );

			$query       = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
			$value       = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : '';
			$selected_id = isset( $_POST['selected_id'] ) ? sanitize_text_field( wp_unslash( $_POST['selected_id'] ) ) : '';
			if ( ! in_array( $query, array( 'post_type', 'term' ), true ) || ! current_user_can( 'edit_theme_options' ) || empty( $value ) || ! $selected_id ) {
				wp_send_json_error();
			}

			$selected_id = explode( ',', $selected_id );

			global $wp_taxonomies;

			ob_start();
			foreach ( $selected_id as $k ) {
				if ( 'term' === $query ) {
					$get_term  = get_term( $k );
					$data_name = sprintf( '%1$s: %2$s', $wp_taxonomies[ $get_term->taxonomy ]->label, $get_term->name );
				} else {
					$data_name = get_the_title( $k );
				}
				?>
				<span class="wty-autocomplete-id" data-id="<?php echo esc_attr( $k ); ?>">
					<?php echo esc_html( $data_name ); ?>
					<i class="wty-autocomplete-remove-id eicon-close-circle"></i>
				</span>
				<?php
			}

			wp_send_json_success( ob_get_clean() );
		}

		/**
		 * Searching
		 */
		public function woostify_autocomplete_search() {
			check_ajax_referer( 'woostify-autocomplete', 'security_nonce' );

			$query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
			$value = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : '';
			if ( ! in_array( $query, array( 'post_type', 'term' ), true ) || ! current_user_can( 'edit_theme_options' ) || empty( $value ) ) {
				wp_send_json_error();
			}

			$keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';
			global $wp_taxonomies;

			ob_start();
			if ( 'post_type' === $query ) {
				global $wpdb;
				$sql     = "SELECT ID FROM {$wpdb->prefix}posts WHERE post_type='$value' AND post_status='publish' AND post_title LIKE '%$keyword%'";
				$get_ids = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
				if ( ! empty( $get_ids ) ) {
					foreach ( $get_ids as $k ) {
						?>
						<span class="wty-autocomplete-id" data-id="<?php echo esc_attr( $k['ID'] ); ?>">
							<?php echo wp_kses_post( get_the_title( $k['ID'] ) ); ?>
						</span>
						<?php
					}
				} else {
					?>
					<span class="no-posts-found"><?php esc_html_e( 'Nothing Found', 'woostify-pro' ); ?></span>
					<?php
				}
			} else {
				$args = array(
					'hide_empty' => true,
					'search'     => $keyword,
					'taxonomy'   => $value,
				);
				if ( 'wc_term' === $value ) {
					unset( $args['taxonomy'] );
				}
				$cats = get_terms( $args );

				if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
					foreach ( $cats as $k ) {
						$taxonomy = $k->taxonomy;
						$tax      = $wp_taxonomies[ $taxonomy ];
						if ( 'wc_term' === $value && ! in_array( 'product', $tax->object_type, true ) ) {
							continue;
						}
						?>
						<span class="wty-autocomplete-id" data-id="<?php echo esc_attr( $k->term_id ); ?>">
							<?php echo sprintf( '%1$s: %2$s', esc_html( $tax->label ), esc_html( $k->name ) ); ?>
						</span>
						<?php
					}
				} else {
					?>
					<span class="no-posts-found"><?php esc_html_e( 'Nothing Found', 'woostify-pro' ); ?></span>
					<?php
				}
			}

			$res = ob_get_clean();

			wp_send_json_success( $res );
		}
	}

	Woostify_Elementor_Helper::init();
}
