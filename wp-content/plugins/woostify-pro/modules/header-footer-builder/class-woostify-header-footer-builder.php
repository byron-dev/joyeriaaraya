<?php
/**
 * Header and Footer builder
 *
 * @package Woostify Pro
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Woostify_Header_Footer_Builder' ) ) {
	/**
	 * Class for woostify Header Footer builder.
	 */
	class Woostify_Header_Footer_Builder {
		/**
		 * Instance Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Meta Option
		 *
		 * @var $meta_option
		 */
		private static $meta_option;

		/**
		 * Post ID
		 *
		 * @var int
		 */
		public $post_id;

		/**
		 * Post type
		 *
		 * @var String
		 */
		public $post_type;

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
			add_action( 'init', array( $this, 'init_action' ), 0 );
			add_action( 'wp', array( $this, 'wp_action' ) );
			add_action( 'admin_menu', array( $this, 'add_header_footer_builder_admin_menu' ), 5 );
			add_action( 'load-post.php', array( $this, 'header_footer_builder_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'header_footer_builder_metabox' ) );
			add_action( 'save_post', array( $this, 'save_post_meta' ), 10, 3 );
			add_filter( 'template_include', array( $this, 'single_template' ) );

			add_action( 'wp_ajax_woostify_pro_select_template', array( $this, 'header_footer_builder_select_template' ) );
			add_action( 'wp_ajax_nopriv_woostify_pro_select_template', array( $this, 'header_footer_builder_select_template' ) );
			add_action( 'wp_ajax_woostify_pro_load_autocomplete', array( $this, 'header_footer_builder_load_autocomplete' ) );
			add_action( 'wp_ajax_woostify_pro_load_post', array( $this, 'header_footer_builder_load_post' ) );

			// Add Template Type column on 'hf_builder' list in admin screen.
			add_filter( 'manage_hf_builder_posts_columns', array( $this, 'add_header_footer_builder_column_head' ), 10 );
			add_action( 'manage_hf_builder_posts_custom_column', array( $this, 'add_header_footer_builder_column_content' ), 10, 2 );

			// Scripts and styles.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'elementor_enqueue_scripts' ), 999 );
		}

		/**
		 * Define constant
		 */
		public function define_constants() {
			if ( ! defined( 'WOOSTIFY_PRO_HEADER_FOOTER_BUILDER' ) ) {
				define( 'WOOSTIFY_PRO_HEADER_FOOTER_BUILDER', WOOSTIFY_PRO_VERSION );
			}
		}

		/**
		 * Init
		 */
		public function init_action() {
			// Register a Theme Builder post type.
			$args = array(
				'label'               => __( 'Header Footer Template', 'woostify-pro' ),
				'supports'            => array( 'title', 'editor', 'thumbnail', 'elementor' ),
				'rewrite'             => array( 'slug' => 'header-footer-builder' ),
				'show_in_rest'        => true,
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'page',
			);
			register_post_type( 'hf_builder', $args );

			// Flush rewrite rules.
			if ( ! get_option( 'woostify_hf_builder_flush_rewrite_rules' ) ) {
				flush_rewrite_rules();
				update_option( 'woostify_hf_builder_flush_rewrite_rules', true );
			}
		}

		/**
		 * Get page type
		 *
		 * @return string
		 */
		public function page_type() {
			$page_type = '';
			if ( is_home() ) {
				$page_type = 'blog';
			} elseif ( is_archive() ) {
				$page_type = 'archive';
			} elseif ( is_search() ) {
				$page_type = 'search';
			} elseif ( is_404() ) {
				$page_type = 'not_found';
			}

			return $page_type;
		}

		/**
		 * Template( header or Footer ) with conditon For All
		 *
		 * @param string $template template.
		 * @return false|WP_Query
		 */
		public function display_all( $template = 'header' ) {
			$args = array(
				'post_type'           => 'hf_builder',
				'orderby'             => 'id',
				'order'               => 'DESC',
				'posts_per_page'      => 1,
				'ignore_sticky_posts' => 1,
				'meta_query'          => array( //phpcs:ignore
					array(
						'key'     => 'woostify-header-footer-builder-template',
						'compare' => 'LIKE',
						'value'   => $template,
					),
					array(
						'key'     => 'woostify-header-template-display-on',
						'compare' => 'LIKE',
						'value'   => 'all',
					),
				),
			);

			$header = new WP_Query( $args );

			$this->check_ex_post( $header );

			if ( $header->have_posts() ) {
				return $header;
			}

			return false;
		}

		/**
		 * Template( header or Footer ) With condition For Archive, Blog Page, Search Page.
		 *
		 * @param string $page_type page type.
		 * @param string $template template.
		 * @return false|WP_Query
		 */
		public function display_template( $page_type, $template = 'header' ) {
			if ( empty( $page_type ) ) {
				return false;
			}
			$args   = array(
				'post_type'           => 'hf_builder',
				'orderby'             => 'id',
				'order'               => 'DESC',
				'posts_per_page'      => 1,
				'ignore_sticky_posts' => 1,
				'meta_query'          => array( //phpcs:ignore
					array(
						'key'     => 'woostify-header-footer-builder-template',
						'compare' => 'LIKE',
						'value'   => $template,
					),
					array(
						'key'     => 'woostify-header-template-display-on',
						'compare' => 'LIKE',
						'value'   => $page_type,
					),
				),
			);
			$header = new WP_Query( $args );

			if ( $header->have_posts() ) {
				return $header;
			}

			return false;
		}

		/**
		 * Template( header or Footer ) with Conditon For Archive, Blog Page, Search Page.
		 *
		 * @param int    $post_id post id.
		 * @param string $post_type post type.
		 * @param string $template template.
		 * @return false|WP_Query
		 */
		public function all_single( $post_id, $post_type, $template = 'header' ) {
			if ( ! is_single() && ! is_page() ) {
				return false;
			}

			$args   = array(
				'post_type'           => 'hf_builder',
				'orderby'             => 'id',
				'order'               => 'DESC',
				'posts_per_page'      => 1,
				'ignore_sticky_posts' => 1,
				'meta_query'          => array( //phpcs:ignore
					array(
						'key'     => 'woostify-header-footer-builder-template',
						'compare' => 'LIKE',
						'value'   => $template,
					),
					array(
						'key'     => 'woostify-header-template-post-type',
						'compare' => 'LIKE',
						'value'   => $post_type,
					),
					array(
						'key'     => 'woostify-header-template-post-ids',
						'compare' => 'LIKE',
						'value'   => 'all',
					),
				),
			);
			$header = new WP_Query( $args );

			if ( $header->have_posts() ) {

				while ( $header->have_posts() ) {
					$header->the_post();
					$id           = get_the_ID();
					$ex_post      = get_post_meta( $id, 'woostify-header-template-ex-post-ids', true );
					$no_display   = get_post_meta( $id, 'woostify-header-template-not-display-on', true );
					$ex_post_type = get_post_meta( $id, 'woostify-header-template-ex-post-type', true );
					$list_ex_post = array();
					$post_type    = get_post_type( $post_id );
					$list_ex_post = explode( ',', $ex_post );
					if ( 'all' === $ex_post && $post_type === $ex_post_type ) {

						return false;
					}
                    if ( in_array( $post_id, $list_ex_post ) ) { //phpcs:ignore

						return false;
					}
				}
				wp_reset_postdata();

				return $header;
			}

			return false;
		}

		/**
		 * Template( header or Footer ) with Conditon For Archive, Blog Page, Search Page.
		 *
		 * @param int    $id post id.
		 * @param string $post_type post type.
		 * @param string $template template.
		 * @return false|WP_Query
		 */
		public function current_single( $id, $post_type, $template = 'header' ) {
			if ( ! is_single() && ! is_page() ) {
				return false;
			}
			$args = array(
				'post_type'           => 'hf_builder',
				'orderby'             => 'id',
				'order'               => 'DESC',
				'posts_per_page'      => -1,
				'ignore_sticky_posts' => 1,
				'meta_query'          => array(//phpcs:ignore
					array(
						'key'     => 'woostify-header-footer-builder-template',
						'compare' => 'LIKE',
						'value'   => $template,
					),
					array(
						'key'     => 'woostify-header-template-post-type',
						'compare' => 'LIKE',
						'value'   => $post_type,
					),
				),
			);

			$header = new \WP_Query( $args );

			if ( $header->have_posts() ) {

				$list_header = $header->posts;
				$current     = array();

				foreach ( $list_header as $key => $post ) {
					$list_id = get_post_meta( $post->ID, 'woostify-header-template-post-ids', true );
                    if ( ! empty( $list_id ) || 'all' != $list_id ) { // phpcs:ignore
						$post_id = explode( ',', $list_id );
                        if ( in_array( $id, $post_id ) ) { // phpcs:ignore
							$current[0] = $post;
						}
					}
				}
				wp_reset_postdata();

				if ( empty( $current ) ) {

					return false;
				} else {
					$header->posts      = $current;
					$header->post_count = 1;

					return $header;
				}
			}

			return false;
		}

		/**
		 * Check condition exclude.
		 *
		 * @param object $header header object list.
		 * @return false
		 */
		public function check_ex_post( $header ) {
			$post_id = $this->post_id;
			if ( $header->have_posts() ) {
				while ( $header->have_posts() ) {
					$header->the_post();
					$id           = get_the_ID();
					$ex_post      = get_post_meta( $id, 'woostify-header-template-ex-post-ids', true );
					$no_display   = get_post_meta( $id, 'woostify-header-template-not-display-on', true );
					$ex_post_type = get_post_meta( $id, 'woostify-header-template-ex-post-type', true );
					$list_ex_post = array();
					$post_type    = get_post_type( $post_id );

					if ( 'blog' === $no_display && is_home() ) {

						return false;
					}
					if ( 'archive' === $no_display && is_archive() ) {
						return false;
					}

					if ( 'search' === $no_display && is_search() ) {
						return false;
					}

					if ( 'not_found' === $no_display && is_404() ) {
						return false;
					}

					if ( ! empty( $ex_post ) && 'blog' !== $no_display && 'archive' !== $no_display && 'search' !== $no_display && 'not_found' !== $no_display ) {
						$list_ex_post = explode( ',', $ex_post );
						if ( 'all' === $ex_post && is_single() && $post_type === $ex_post_type ) {

							return false;
						}
                        if ( in_array( $post_id, $list_ex_post ) ) { //phpcs:ignore
							return false;
						}
					}
				}
				wp_reset_postdata();
			}
		}

		/**
		 * Get template id
		 *
		 * @param string $template template.
		 * @return false|int|string
		 */
		public function template_id( $template = 'header' ) {
			global $post;
			$shop_id = get_option( 'woocommerce_shop_page_id' );
			if ( ! empty( $post ) ) {
				$this->post_id   = $post->ID;
				$this->post_type = get_post_type( $post->ID );
			}

			if ( class_exists( 'Woocommerce' ) && is_shop() ) {
				$this->post_id   = $shop_id;
				$this->post_type = get_post_type( $shop_id );
			}

			$post_id              = $this->post_id;
			$maintenance_mode     = get_option( 'elementor_maintenance_mode_mode' );
			$maintenance_template = get_option( 'elementor_maintenance_mode_template_id' );
			if ( 'coming_soon' === $maintenance_mode && $maintenance_template === $post_id ) {
				return false;
			}
			$page_type = $this->page_type();
			$post_type = $this->post_type;
			$id        = '';

			if ( $this->display_all( $template ) || $this->display_template( $page_type, $template ) || $this->all_single( $post_id, $post_type, $template ) || $this->current_single( $post_id, $post_type, $template ) ) {
				if ( $this->display_all( $template ) ) {
					$header = $this->display_all( $template );
				}

				if ( $this->display_template( $page_type, $template ) ) {
					$header = $this->display_template( $page_type, $template );
				}
				if ( $this->all_single( $post_id, $post_type, $template ) ) {
					$header = $this->all_single( $post_id, $post_type, $template );
				}
				if ( $this->current_single( $post_id, $post_type, $template ) ) {
					$header = $this->current_single( $post_id, $post_type, $template );
				}

				while ( $header->have_posts() ) {
					$header->the_post();
					$id = get_the_ID();
				}
				wp_reset_postdata();

				return $id;
			}

			return false;
		}

		/**
		 * Init
		 */
		public function wp_action() {
			$header_template_id     = $this->template_exist( 'header' );
			$footer_template_id     = $this->template_exist( 'footer' );
			$new_header_template_id = $this->template_id( 'header' );
			$new_footer_template_id = $this->template_id( 'footer' );
			if ( $new_header_template_id ) {
				$header_template_id = $new_header_template_id;
			}
			if ( $new_footer_template_id ) {
				$footer_template_id = $new_footer_template_id;
			}

			if ( $header_template_id && $footer_template_id ) {
				if ( ! woostify_elementor_has_location( 'header' ) ) {
					// Header.
					remove_action( 'woostify_theme_header', 'woostify_template_header' );
					add_action( 'woostify_theme_header', 'woostify_view_open', 0 );
					add_action( 'woostify_theme_header', array( $this, 'print_header_template' ), 20 );
					add_filter( 'woostify_has_header_layout_classes', array( $this, 'add_body_classes' ) );
				}

				if ( ! woostify_elementor_has_location( 'footer' ) ) {
					// Footer.
					remove_action( 'woostify_theme_footer', 'woostify_template_footer' );
					add_action( 'woostify_theme_footer', array( $this, 'print_footer_template' ), 20 );
					add_action( 'woostify_after_footer', 'woostify_view_close', 0 );
				}
			} elseif ( $header_template_id && ! $footer_template_id ) {
				if ( ! woostify_elementor_has_location( 'header' ) ) {
					// Header.
					remove_action( 'woostify_theme_header', 'woostify_template_header' );
					add_action( 'woostify_theme_header', 'woostify_view_open', 0 );
					add_action( 'woostify_theme_header', array( $this, 'print_header_template' ), 20 );
					add_filter( 'woostify_has_header_layout_classes', array( $this, 'add_body_classes' ) );
				}
			} elseif ( ! $header_template_id && $footer_template_id ) {
				if ( ! woostify_elementor_has_location( 'footer' ) ) {
					// Footer.
					remove_action( 'woostify_theme_footer', 'woostify_template_footer' );
					add_action( 'woostify_theme_footer', array( $this, 'print_footer_template' ), 40 );
					add_action( 'woostify_after_footer', 'woostify_view_close', 0 );
				}
			}
		}

		/**
		 * Add body class for header footer builder template
		 *
		 * @return string
		 */
		public function add_body_classes() {
			$classes = 'has-header-builder-template';
			return $classes;
		}

		/**
		 * Column head
		 *
		 * @param      array $defaults  The defaults.
		 */
		public function add_header_footer_builder_column_head( $defaults ) {
			$order    = array();
			$checkbox = 'title';
			foreach ( $defaults as $key => $value ) {
				$order[ $key ] = $value;
				if ( $key === $checkbox ) {
					$order['hf_builder_type']       = __( 'Type', 'woostify-pro' );
					$order['hf_builder_display_on'] = __( 'Display On', 'woostify-pro' );
				}
			}

			return $order;
		}

		/**
		 * Column content
		 *
		 * @param      string $column_name  The column name.
		 * @param      int    $post_id      The post id.
		 */
		public function add_header_footer_builder_column_content( $column_name, $post_id ) {
			if ( 'hf_builder_type' === $column_name ) {
				$type = woostify_get_metabox( $post_id, 'woostify-header-footer-builder-template' );
				?>
					<span><?php echo esc_html( ucfirst( $type ) ); ?></span>
				<?php
			}
			if ( 'hf_builder_display_on' === $column_name ) {
				$display_on = woostify_get_metabox( $post_id, 'woostify-header-template-display-on' );
				?>
				<span><?php echo esc_html( ucfirst( $display_on ) ); ?></span>
				<?php
			}
		}

		/**
		 * Add Theme Builder admin menu
		 */
		public function add_header_footer_builder_admin_menu() {
			add_submenu_page( 'woostify-welcome', 'Header Footer Builder', __( 'Header Footer Builder', 'woostify-pro' ), 'manage_options', 'edit.php?post_type=hf_builder' );
		}

		/**
		 * Theme Builder metabox
		 */
		public function header_footer_builder_metabox() {
			add_action( 'add_meta_boxes', array( $this, 'setup_header_footer_builder_metabox' ) );
			add_action( 'save_post', array( $this, 'save_header_footer_builder_metabox' ) );

			self::$meta_option = array(
				'woostify-header-footer-builder-template' => array(
					'default'  => 'default',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'woostify-header-template-sticky'         => array(
					'default'  => 'default',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'woostify-header-template-shrink'         => array(
					'default'  => 'default',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'woostify-header-template-sticky-on'      => array(
					'default'  => 'all-device',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'woostify-header-template-display-on'     => array(
					'default'  => 'all',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'woostify-header-template-not-display-on' => array(
					'default'  => '0',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'woostify-header-template-post-ids'       => array(
					'default'  => 'all',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'woostify-header-template-post-type'      => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'woostify-header-template-ex-post-ids'    => array(
					'default'  => 'all',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'woostify-header-template-ex-post-type'   => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
			);
		}


		/**
		 * Detect meta value change
		 *
		 * @param      int $post_id The post ID.
		 */
		public function save_post_meta( $post_id ) {
			$post_type = get_post_type( $post_id );
			if ( 'hf_builder' !== $post_type ) {
				return;
			}

			$post_status        = get_post_status( $post_id );
			$header_template_id = intval( get_option( 'woostify_header_template_id' ) );
			$footer_template_id = intval( get_option( 'woostify_footer_template_id' ) );

			if ( 'publish' === $post_status ) {
				if ( 'header' === woostify_get_metabox( $post_id, 'woostify-header-footer-builder-template' ) ) {
					update_option( 'woostify_header_template_id', $post_id );
				} elseif ( 'footer' === woostify_get_metabox( $post_id, 'woostify-header-footer-builder-template' ) ) {
					update_option( 'woostify_footer_template_id', $post_id );
				}
			} else {
				if ( $header_template_id === $post_id ) {
					delete_option( 'woostify_header_template_id' );
				} elseif ( $footer_template_id === $post_id ) {
					delete_option( 'woostify_footer_template_id' );
				}
			}
		}

		/**
		 * Get metabox options
		 */
		public static function get_header_footer_builder_metabox_option() {
			return self::$meta_option;
		}

		/**
		 *  Setup Metabox
		 */
		public function setup_header_footer_builder_metabox() {
			add_meta_box(
				'woostify_metabox_settings_header_footer_builder',
				__( 'Header Footer Template Settings', 'woostify-pro' ),
				array( $this, 'header_footer_builder_markup' ),
				'hf_builder',
				'side'
			);
		}

		/**
		 * Metabox Markup
		 *
		 * @param  object $post Post object.
		 * @return void
		 */
		public function header_footer_builder_markup( $post ) {

			wp_nonce_field( basename( __FILE__ ), 'woostify_metabox_settings_header_footer_builder' );
			$stored = get_post_meta( $post->ID );

			// Set stored and override defaults.
			foreach ( $stored as $key => $value ) {
				self::$meta_option[ $key ]['default'] = isset( $stored[ $key ][0] ) ? $stored[ $key ][0] : '';
			}

			// Get defaults.
			$meta = self::get_header_footer_builder_metabox_option();

			/**
			 * Get options
			 */
			$template       = isset( $meta['woostify-header-footer-builder-template']['default'] ) ? $meta['woostify-header-footer-builder-template']['default'] : 'default';
			$sticky         = isset( $meta['woostify-header-template-sticky']['default'] ) ? $meta['woostify-header-template-sticky']['default'] : 'default';
			$shrink         = isset( $meta['woostify-header-template-shrink']['default'] ) ? $meta['woostify-header-template-shrink']['default'] : 'default';
			$sticky_on      = isset( $meta['woostify-header-template-sticky-on']['default'] ) ? $meta['woostify-header-template-sticky-on']['default'] : 'default';
			$display_on     = isset( $meta['woostify-header-template-display-on']['default'] ) ? $meta['woostify-header-template-display-on']['default'] : 'all';
			$not_display_on = isset( $meta['woostify-header-template-not-display-on']['default'] ) ? $meta['woostify-header-template-not-display-on']['default'] : '0';
			$post_ids       = isset( $meta['woostify-header-template-post-ids']['default'] ) ? $meta['woostify-header-template-post-ids']['default'] : 'all';
			$post_type      = isset( $meta['woostify-header-template-post-type']['default'] ) ? $meta['woostify-header-template-post-type']['default'] : '';
			$ex_post_ids    = isset( $meta['woostify-header-template-ex-post-ids']['default'] ) ? $meta['woostify-header-template-ex-post-ids']['default'] : '';
			$ex_post_type   = isset( $meta['woostify-header-template-ex-post-type']['default'] ) ? $meta['woostify-header-template-ex-post-type']['default'] : '';
			$list_post      = $post_ids;
			$list_ex_post   = $ex_post_ids;
			if ( 'all' !== $post_ids ) {
				$list_post = explode( ',', $post_ids );
			}

			if ( 'all' !== $ex_post_ids ) {
				$list_ex_post = explode( ',', $ex_post_ids );
			}

			$options = woostify_post_type_support();
			?>

			<div class="woostify-pro-hfb-options-wrapper">
				<div class="input-wrapper">
					<div class="woostify-metabox-option">
						<label for="woostify-header-footer-builder-template" class="woostify-metabox-option-title"><?php esc_html_e( 'Template', 'woostify-pro' ); ?></label>
						<select name="woostify-header-footer-builder-template" id="woostify-header-footer-builder-template" class="woostify-metabox-option-control">
							<option value="default" <?php selected( $template, 'default' ); ?>>
								<?php esc_html_e( 'Select Option', 'woostify-pro' ); ?>
							</option>

							<option value="header" <?php selected( $template, 'header' ); ?>>
								<?php esc_html_e( 'Header', 'woostify-pro' ); ?>
							</option>

							<option value="footer" <?php selected( $template, 'footer' ); ?>>
								<?php esc_html_e( 'Footer', 'woostify-pro' ); ?>
							</option>
						</select>
					</div>
				</div>
			<?php if ( 'default' !== $template ) { ?>
				<div class="input-wrapper">
						<div class="condition-group display--on">
							<div class="parent-item">
								<div class="woostify-metabox-option">
									<label class="woostify-metabox-option-title" for="woostify-header-footer-builder-display-on"><?php echo esc_html__( 'Display On', 'woostify-pro' ); ?></label>
									<select name="woostify-header-template-display-on" class="woostify-metabox-option-control display-on" id="woostify-header-footer-builder-display-on">
										<?php
										foreach ( $options as $key => $option ) :
                                            $selected = ( $key == $display_on ) ? 'selected' : ''; // phpcs:ignore
											?>
											<option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $option ); ?></option>
										<?php endforeach ?>
									</select>
								</div>
							</div>
							<div class="child-item">
								<?php if ( ! empty( $post_ids ) && ! empty( $post_type ) ) : ?>
									<div class="input-item-wrapper woostify-metabox-option">
										<div class="woostify-section-select-post select-all <?php echo ( is_string( $list_post ) ? 'select-all' : 'render--post has-option' ); ?>">
											<span class="woostify-select-all-post<?php echo ( is_string( $list_post ) ? '' : ' hidden' ); ?>">
												<span class="woostify-select-all"><?php echo esc_html__( 'All', 'woostify-pro' ); ?></span>
												<span class="woostify-arrow ion-chevron-down"></span>
											</span>
											<div class="woostify-section-render--post <?php echo ( is_string( $list_post ) ? 'hidden' : '' ); ?>">
												<div class="woostify-auto-complete-field">
													<?php
													if ( is_array( $list_post ) ) :
														foreach ( $list_post as $id ) :
															$id = (int) $id;
															?>
															<span class="woostify-auto-complete-key">
																<span class="woostify-title"><?php echo esc_html( get_the_title( $id ) ); ?></span>
																<span class="btn-woostify-auto-complete-delete ion-close" data-item="<?php echo esc_attr( $id ); ?>"></span>
															</span>
															<?php
														endforeach;
														endif;
													?>
													<input type="text" class="woostify--hf-post-name" aria-autocomplete="list" size="1">
												</div>
											</div>
										</div>
										<input type="hidden" name="woostify-header-template-post-ids" value="<?php echo esc_html( $post_ids ); ?>" class="woostify-post-ids">
										<input type="hidden" name="woostify-header-template-post-type" value="<?php echo esc_attr( $post_type ); ?>" class="woostify-post-type">
										<div class="woostify-data"></div>
									</div>
								<?php endif; ?>
							</div>
						</div>
						<div class="condition-group not-display">
							<div class="parent-item">
								<div class="woostify-metabox-option">
									<label class="woostify-metabox-option-title" for="woostify-header-footer-builder-no-display-on"><?php echo esc_html__( 'Do Not Display On', 'woostify-pro' ); ?></label>
									<select name="woostify-header-template-not-display-on" class="woostify-metabox-option-control no-display-on" id="woostify-header-footer-builder-no-display-on">
										<option value="0"><?php echo esc_html__( 'Select', 'woostify-pro' ); ?></option>
										<?php
										unset( $options['all'] );
										foreach ( $options as $key => $option ) :
                                            $selected = ( $key == $not_display_on ) ? 'selected' : ''; // phpcs:ignore
											?>
											<option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $option ); ?></option>
										<?php endforeach ?>
									</select>
								</div>
							</div>
							<div class="child-item">
								<?php if ( ! empty( $ex_post_ids ) && ! empty( $ex_post_type ) ) : ?>
									<div class="input-item-wrapper woostify-metabox-option">
										<div class="woostify-section-select-post select-all <?php echo ( is_string( $list_ex_post ) ? 'select-all' : 'render--post has-option' ); ?>">
											<span class="woostify-select-all-post<?php echo ( is_string( $list_ex_post ) ? '' : ' hidden' ); ?>">
												<span class="woostify-select-all"><?php echo esc_html__( 'All', 'woostify-pro' ); ?></span>
												<span class="woostify-arrow ion-chevron-down"></span>
											</span>
											<div class="woostify-section-render--post <?php echo ( is_string( $list_ex_post ) ? 'hidden' : '' ); ?>">
												<div class="woostify-auto-complete-field">
													<?php
													if ( is_array( $list_ex_post ) ) :
														foreach ( $list_ex_post as $id ) :
															$id = (int) $id;
															?>
															<span class="woostify-auto-complete-key">
																<span class="woostify-title"><?php echo esc_html( get_the_title( $id ) ); ?></span>
																<span class="btn-woostify-auto-complete-delete ion-close" data-item="<?php echo esc_attr( $id ); ?>"></span>
															</span>
															<?php
														endforeach;
													endif;
													?>
													<input type="text" class="woostify--hf-post-name" aria-autocomplete="list" size="1">
												</div>
											</div>
										</div>
										<input type="hidden" name="woostify-header-template-ex-post-ids" value="<?php echo esc_html( $ex_post_ids ); ?>" class="woostify-post-ids">
										<input type="hidden" name="woostify-header-template-ex-post-type" value="<?php echo esc_attr( $ex_post_type ); ?>" class="woostify-post-type">
										<div class="woostify-data"></div>
									</div>
								<?php endif; ?>
							</div>
						</div>

						<div class="condition-group sticky <?php echo 'header' !== $template ? 'hidden' : ''; ?>">
							<div class="parent-item">
								<div class="woostify-metabox-option">
									<label for="woostify-header-template-sticky">
										<input type="checkbox" id="woostify-header-template-sticky" name="woostify-header-template-sticky" value="sticky" <?php checked( $sticky, 'sticky' ); ?> />
										<?php esc_html_e( 'Sticky', 'woostify-pro' ); ?>
									</label>
								</div>
							</div>
							<div class="child-item <?php echo 'sticky' !== $sticky ? 'hidden' : ''; ?>">
								<div class="input-item-wrapper">
									<div class="woostify-metabox-option">
										<label for="woostify-header-template-shrink" class="woostify-metabox-option-title">
											<input type="checkbox" id="woostify-header-template-shrink" name="woostify-header-template-shrink" value="shrink" <?php checked( $shrink, 'shrink' ); ?> />
											<?php esc_html_e( 'Shrink On Scroll', 'woostify-pro' ); ?>
										</label>
									</div>
									<div class="woostify-metabox-option">
										<label for="woostify-header-template-sticky-on" class="woostify-metabox-option-title">
											<?php esc_html_e( 'Sticky On', 'woostify-pro' ); ?>
										</label>
										<select name="woostify-header-template-sticky-on" class="woostify-metabox-option-control" id="woostify-header-template-sticky-on">
											<option value="all-device" <?php selected( $sticky_on, 'all-device' ); ?>>
												<?php esc_html_e( 'Desktop + Mobile', 'woostify-pro' ); ?>
											</option>

											<option value="desktop" <?php selected( $sticky_on, 'desktop' ); ?>>
												<?php esc_html_e( 'Desktop', 'woostify-pro' ); ?>
											</option>

											<option value="mobile" <?php selected( $sticky_on, 'mobile' ); ?>>
												<?php esc_html_e( 'Mobile', 'woostify-pro' ); ?>
											</option>
										</select>
									</div>
								</div>
							</div>
						</div>
				</div>
				<?php } ?>
			</div>
            <input type="hidden" id="woostify-hfb-nonce" value="<?php echo wp_create_nonce( 'select_template_ajax_nonce' ); //phpcs:ignore ?>">
			<?php
		}

		/**
		 * Metabox Save
		 *
		 * @param  number $post_id Post ID.
		 * @return void
		 */
		public function save_header_footer_builder_metabox( $post_id ) {

			// Checks save status.
			$is_user_can_edit = current_user_can( 'edit_posts' );
			$is_autosave      = wp_is_post_autosave( $post_id );
			$is_revision      = wp_is_post_revision( $post_id );
			$is_valid_nonce   = ( isset( $_POST['woostify_metabox_settings_header_footer_builder'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woostify_metabox_settings_header_footer_builder'] ) ), basename( __FILE__ ) ) ) ? true : false;

			// Exits script depending on save status.
			if ( $is_autosave || $is_revision || ! $is_valid_nonce || ! $is_user_can_edit ) {
				return;
			}

			/**
			 * Get meta options
			 */
			$post_meta = self::get_header_footer_builder_metabox_option();

			foreach ( $post_meta as $key => $data ) {

				// Sanitize values.
				$sanitize_filter = isset( $data['sanitize'] ) ? $data['sanitize'] : 'FILTER_DEFAULT';

				switch ( $sanitize_filter ) {

					case 'FILTER_SANITIZE_STRING':
							$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_STRING );
						break;

					case 'FILTER_SANITIZE_URL':
							$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_URL );
						break;

					case 'FILTER_SANITIZE_NUMBER_INT':
							$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_NUMBER_INT );
						break;

					default:
							$meta_value = filter_input( INPUT_POST, $key, FILTER_DEFAULT );
						break;
				}

				// Store values.
				if ( $meta_value ) {
					update_post_meta( $post_id, $key, $meta_value );
				} else {
					delete_post_meta( $post_id, $key );
				}
			}

		}

		/**
		 * Check header footer template exist
		 *
		 * @param string $template template.
		 * @return false|int
		 */
		public function template_exist( $template = 'header' ) {
			$args = array(
				'post_type'           => 'hf_builder',
				'post_status'         => 'publish',
				'orderby'             => 'id',
				'order'               => 'DESC',
				'posts_per_page'      => 1,
				'ignore_sticky_posts' => 1,
				'meta_query'     => array( // phpcs:ignore
					array(
						'key'   => 'woostify-header-footer-builder-template',
						'value' => $template,
					),
					array(
						'key'     => 'woostify-header-template-display-on',
						'compare' => 'NOT EXISTS',
					),
				),
			);

			$query = new WP_Query( $args );

			// Check have posts.
			if ( $query->have_posts() ) {
				return $query->posts[0]->ID; // Return ID.
			}

			return false;
		}

		/**
		 * Single hf_builder template
		 *
		 * @param string $template The path of the template to include.
		 */
		public function single_template( $template ) {
			if ( is_singular( 'hf_builder' ) && file_exists( WOOSTIFY_THEME_DIR . 'inc/elementor/elementor-library.php' ) ) {
				$template = WOOSTIFY_THEME_DIR . 'inc/elementor/elementor-library.php';
			}

			return $template;
		}

		/**
		 * Render Header Template
		 */
		public function print_header_template() {
			$id     = $this->template_exist( 'header' );
			$new_id = $this->template_id( 'header' );

			if ( $new_id ) {
				$id = $new_id;
			}

			if ( ! $id ) {
				return;
			}

			$sticky    = get_post_meta( $id, 'woostify-header-template-sticky', true );
			$shrink    = get_post_meta( $id, 'woostify-header-template-shrink', true );
			$sticky_on = get_post_meta( $id, 'woostify-header-template-sticky-on', true );

			$classes[] = 'woostify-header-template-builder';
			$classes[] = 'sticky' === $sticky ? 'has-sticky' : '';
			$classes[] = 'sticky' === $sticky && 'shrink' === $shrink ? 'has-shrink' : '';
			$classes[] = 'sticky' === $sticky ? 'sticky-on-' . $sticky_on : '';
			$classes   = implode( ' ', array_filter( $classes ) );
			?>
			<div class="<?php echo esc_attr( $classes ); ?>">
				<div class="woostify-header-template-builder-inner">
					<?php
					if ( woostify_is_elementor_page( $id ) ) {
						$frontend           = new \Elementor\Frontend();
						$elementor_instance = \Elementor\Plugin::instance();
						if ( is_404() || is_search() ) {
							$editor       = $elementor_instance->editor;
							$is_edit_mode = $editor->is_edit_mode();
							$editor->set_edit_mode( false );
							$with_css = $is_edit_mode;
							$content  = $elementor_instance->frontend->get_builder_content( $id, $with_css );
							\Elementor\Plugin::$instance->editor->set_edit_mode( $is_edit_mode );
							echo $content; //phpcs:ignore
						} else {
							echo $frontend->get_builder_content_for_display( $id, true ); // phpcs:ignore
						} // phpcs:ignore
						wp_reset_postdata();
					} else {
						echo apply_filters( 'the_content', get_post_field( 'post_content', $id ) ); // phpcs:ignore
					}
					?>
				</div>
			</div>
			<?php
		}

		/**
		 * Render Footer Template
		 */
		public function print_footer_template() {
			$id     = $this->template_exist( 'footer' );
			$new_id = $this->template_id( 'footer' );
			if ( $new_id ) {
				$id = $new_id;
			}

			if ( ! $id ) {
				return;
			}

			if ( woostify_is_elementor_page( $id ) ) {
				$frontend           = new \Elementor\Frontend();
				$elementor_instance = \Elementor\Plugin::instance();
				if ( is_404() || is_search() ) {
					$editor       = $elementor_instance->editor;
					$is_edit_mode = $editor->is_edit_mode();
					$editor->set_edit_mode( false );
					$with_css = $is_edit_mode;
					$content  = $elementor_instance->frontend->get_builder_content( $id, $with_css );
					\Elementor\Plugin::$instance->editor->set_edit_mode( $is_edit_mode );
					echo $content; //phpcs:ignore
				} else {
					echo $frontend->get_builder_content_for_display( $id, true ); //phpcs:ignore
				}
				wp_reset_postdata();
			} else {
				echo apply_filters( 'the_content', get_post_field( 'post_content', $id ) ); // phpcs:ignore
			}
		}

		/**
		 * Enqueue styles and scripts.
		 */
		public function enqueue_scripts() {
			$header_id     = $this->template_exist( 'header' );
			$footer_id     = $this->template_exist( 'footer' );
			$new_header_id = $this->template_id( 'header' );
			$new_footer_id = $this->template_id( 'footer' );
			if ( $new_header_id ) {
				$header_id = $new_header_id;
			}
			if ( $new_footer_id ) {
				$footer_id = $new_footer_id;
			}

			if ( $header_id ) {
				if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
					$css_file = new \Elementor\Core\Files\CSS\Post( $header_id );
				} elseif ( class_exists( '\Elementor\Post_CSS_File' ) ) {
					$css_file = new \Elementor\Post_CSS_File( $header_id );
				}

				$css_file->enqueue();
			}

			if ( $footer_id ) {
				if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
					$css_file = new \Elementor\Core\Files\CSS\Post( $footer_id );
				} elseif ( class_exists( '\Elementor\Post_CSS_File' ) ) {
					$css_file = new \Elementor\Post_CSS_File( $footer_id );
				}

				$css_file->enqueue();
			}
		}

		/**
		 * Elementor enqueue styles and scripts.
		 */
		public function elementor_enqueue_scripts() {
			$header_id     = $this->template_exist( 'header' );
			$footer_id     = $this->template_exist( 'footer' );
			$new_header_id = $this->template_id( 'header' );
			$new_footer_id = $this->template_id( 'footer' );
			if ( $new_header_id ) {
				$header_id = $new_header_id;
			}
			if ( $new_footer_id ) {
				$footer_id = $new_footer_id;
			}

			if ( ! $header_id && ! $footer_id ) {
				return;
			}

			// Add elementor frontend script.
			if ( ! woostify_is_elementor_page() ) {
				$elementor_frontend = new \Elementor\Frontend();
				$elementor_frontend->enqueue_scripts();
			}

			// Pro detect.
			if ( ! did_action( 'elementor_pro/init' ) ) {
				return false;
			}

			// Add elementor pro frontend script.
			if ( ! woostify_is_elementor_page() ) {
				$elementor_pro = \ElementorPro\Plugin::instance();
				$elementor_pro->enqueue_frontend_scripts();
				$elementor_pro->enqueue_styles();
			}
		}

		/**
		 * Ajax hfb select template
		 */
		public function header_footer_builder_select_template() {
			check_ajax_referer( 'select_template_ajax_nonce' );

			$options  = woostify_post_type_support();
			$template = isset( $_POST['template'] ) ? sanitize_text_field( $_POST['template'] ) : 'default'; //phpcs:ignore
			$html     = '';

			if ( 'default' !== $template ) :
				ob_start();
				?>
				<div class="input-wrapper">
					<div class="condition-group display--on">
						<div class="parent-item">
							<div class="woostify-metabox-option">
								<label class="woostify-metabox-option-title" for="woostify-header-footer-builder-display-on"><?php echo esc_html__( 'Display On', 'woostify-pro' ); ?></label>
								<select name="woostify-header-template-display-on" class="woostify-metabox-option-control display-on" id="woostify-header-footer-builder-display-on">
									<?php
									foreach ( $options as $key => $option ) :
										?>
										<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $option ); ?></option>
									<?php endforeach ?>
								</select>
							</div>
						</div>
						<div class="child-item">
						</div>
					</div>

					<div class="condition-group not-display">
						<div class="parent-item">
							<div class="woostify-metabox-option">
								<label class="woostify-metabox-option-title" for="woostify-header-footer-builder-no-display-on"><?php echo esc_html__( 'Do Not Display On', 'woostify-pro' ); ?></label>
								<select name="woostify-header-template-not-display-on" class="woostify-metabox-option-control no-display-on" id="woostify-header-footer-builder-no-display-on">
									<option value="0"><?php echo esc_html__( 'Select', 'woostify-pro' ); ?></option>
									<?php
									unset( $options['all'] );
									foreach ( $options as $key => $option ) :
										?>
										<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $option ); ?></option>
									<?php endforeach ?>
								</select>
							</div>
						</div>
						<div class="child-item">
						</div>
					</div>

					<div class="condition-group sticky <?php echo 'header' !== $template ? 'hidden' : ''; ?>">
						<div class="parent-item">
							<div class="woostify-metabox-option">
								<label for="woostify-header-template-sticky">
									<input type="checkbox" id="woostify-header-template-sticky" name="woostify-header-template-sticky" value="sticky" />
									<?php esc_html_e( 'Sticky', 'woostify-pro' ); ?>
								</label>
							</div>
						</div>
						<div class="child-item hidden">
							<div class="input-item-wrapper">
								<div class="woostify-metabox-option">
									<label for="woostify-header-template-shrink" class="woostify-metabox-option-title">
										<input type="checkbox" id="woostify-header-template-shrink" name="woostify-header-template-shrink" value="shrink" />
										<?php esc_html_e( 'Shrink On Scroll', 'woostify-pro' ); ?>
									</label>
								</div>
								<div class="woostify-metabox-option">
									<label for="woostify-header-template-sticky-on" class="woostify-metabox-option-title">
										<?php esc_html_e( 'Sticky On', 'woostify-pro' ); ?>
									</label>
									<select name="woostify-header-template-sticky-on" class="woostify-metabox-option-control" id="woostify-header-template-sticky-on">
										<option value="all-device">
											<?php esc_html_e( 'Desktop + Mobile', 'woostify-pro' ); ?>
										</option>

										<option value="desktop">
											<?php esc_html_e( 'Desktop', 'woostify-pro' ); ?>
										</option>

										<option value="mobile">
											<?php esc_html_e( 'Mobile', 'woostify-pro' ); ?>
										</option>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
				$html = ob_get_contents();
				ob_clean();
				endif;
			wp_send_json_success( $html );
			wp_die();
		}

		/**
		 * Load autocomplete
		 */
		public function header_footer_builder_load_autocomplete() {
			check_ajax_referer( 'select_template_ajax_nonce' );
			$post_type = isset( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : 'all'; //phpcs:ignore
			$type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'display_on'; //phpcs:ignore
			$html      = '';

			if ( '0' !== $post_type && 'all' !== $post_type && 'archive' !== $post_type && 'search' !== $post_type && 'blog' !== $post_type && 'not_found' !== $post_type ) :
				ob_start();
				?>
				<div class="input-item-wrapper  woostify-metabox-option">
					<div class="woostify-section-select-post">
					<span class="woostify-select-all-post">
						<span class="woostify-select-all"><?php echo esc_html__( 'All', 'woostify-pro' ); ?></span>
						<span class="woostify-arrow ion-chevron-down"></span>
					</span>
						<div class="woostify-section-render--post hidden">
							<div class="woostify-auto-complete-field">
								<input type="text" class="woostify--hf-post-name" aria-autocomplete="list" size="1">
							</div>
						</div>
					</div>
					<input type="hidden" name="<?php echo 'display_on' === $type ? 'woostify-header-template-post-type' : 'woostify-header-template-ex-post-type'; ?>" value="<?php echo esc_attr( $post_type ); ?>" class="woostify-post-type">
					<input type="hidden" name="<?php echo 'display_on' === $type ? 'woostify-header-template-post-ids' : 'woostify-header-template-ex-post-ids'; ?>" value="all" class="woostify-post-ids">
					<div class="woostify-data"></div>
				</div>
				<?php
				$html = ob_get_contents();
				ob_clean();
				endif;
			wp_send_json_success( $html );
			wp_die();
		}

		/**
		 * Ajax load post
		 */
		public function header_footer_builder_load_post() {
			check_ajax_referer( 'select_template_ajax_nonce' );

			$post_type      = isset( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : '';
			$keyword        = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
			$selected       = isset( $_POST['selected'] ) ? sanitize_text_field( wp_unslash( $_POST['selected'] ) ) : 'all';
			$selected_posts = 'all' === $selected ? array() : explode( ',', $selected );
			$html           = '';

			if ( '' === $keyword ) {
				wp_send_json_success( $html );
				wp_die();
			}

			$the_query = new WP_Query(
				array(
					's'              => $keyword,
					'posts_per_page' => -1,
					'post_type'      => $post_type,
				)
			);

			if ( $the_query->have_posts() ) {
				ob_start();
				?>
				<div class="woostify-hf-list-post">
					<ul class="hf-list-post">
						<?php
						while ( $the_query->have_posts() ) {
							$the_query->the_post();
							$results[ get_the_ID() ] = get_the_title();
							?>
							<li class="post-item <?php echo in_array( esc_attr( get_the_ID() ), $selected_posts, true ) ? 'disabled' : ''; ?>" data-item="<?php echo esc_attr( get_the_ID() ); ?>">
								<?php the_title(); ?>
							</li>
							<?php
						}
						?>
					</ul>
				</div>
				<?php
				$html = ob_get_contents();
				ob_clean();
			}

			wp_send_json_success( $html );
			wp_die();
		}
	}

	Woostify_Header_Footer_Builder::get_instance();
}

