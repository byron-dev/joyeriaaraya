<?php
/**
 * Elementor Widgets
 *
 * @package  Woostify Pro
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Woostify_Elementor_Widgets' ) ) {

	/**
	 * Main Elementor Widgets Class
	 */
	class Woostify_Elementor_Widgets {
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
			$this->define_constants();
			add_action( 'elementor/widgets/widgets_registered', array( $this, 'add_widgets' ) );
			add_action( 'elementor/elements/categories_registered', array( $this, 'woostify_widget_categories' ) );
			add_action( 'elementor/preview/enqueue_scripts', array( $this, 'elementor_preview_register_scripts' ) );
			add_action( 'elementor/frontend/after_register_scripts', array( $this, 'elementor_front_end_register_scripts' ) );
			add_action( 'elementor/widgets/widgets_registered', array( $this, 'remove_widgets' ), 15 );
		}

		/**
		 * Define constant
		 */
		public function define_constants() {
			define( 'WOOSTIFY_PRO_ELEMENTOR_DIR', WOOSTIFY_PRO_PATH . 'modules/elementor/' );
			define( 'WOOSTIFY_PRO_ELEMENTOR_URI', WOOSTIFY_PRO_URI . 'modules/elementor/' );

			if ( ! defined( 'WOOSTIFY_PRO_ELEMENTOR_WIDGETS' ) ) {
				define( 'WOOSTIFY_PRO_ELEMENTOR_WIDGETS', WOOSTIFY_PRO_VERSION );
			}
		}

		/**
		 * Adds widgets.
		 */
		public function add_widgets() {
			// Widgets.
			$widgets = glob( WOOSTIFY_PRO_ELEMENTOR_DIR . '**/class-woostify-*.php' );
			foreach ( $widgets as $widget ) {
				if ( file_exists( $widget ) ) {
					require_once $widget;
				}
			}

			// Woocommerce widgets.
			if ( woostify_is_woocommerce_activated() ) {
				// Product base.
				require_once WOOSTIFY_PRO_PATH . 'inc/elementor/product-base/class-woostify-base-products-renderer.php';
				require_once WOOSTIFY_PRO_PATH . 'inc/elementor/product-base/class-woostify-current-query-renderer.php';
				require_once WOOSTIFY_PRO_PATH . 'inc/elementor/product-base/class-woostify-products-renderer.php';
				require_once WOOSTIFY_PRO_PATH . 'inc/elementor/product-base/class-woostify-elementor-products-base.php';

				// Deprecated.
				require_once WOOSTIFY_PRO_PATH . 'inc/elementor/deprecated/class-woostify-products-renderer-deprecated.php';

				// Widgets.
				$wc_widgets = glob( WOOSTIFY_PRO_ELEMENTOR_DIR . 'woocommerce/**/class-woostify-*.php' );
				foreach ( $wc_widgets as $wc_widget ) {
					if ( file_exists( $wc_widget ) ) {
						require_once $wc_widget;
					}
				}
			}
		}

		/**
		 * Hide some Elementor Pro widget
		 *
		 * @param      object $widgets_manager  The widgets manager.
		 */
		public function remove_widgets( $widgets_manager ) {
			// Remove only user active Elementor Bundle.
			if ( ! defined( 'WOOSTIFY_PRO_ELEMENTOR_WIDGETS' ) ) {
				return;
			}

			$widgets_manager->unregister_widget_type( 'woocommerce-product-images' );
		}

		/**
		 * Add Elementor Category
		 *
		 * @param      Elements_Manager $elements_manager The elements manager.
		 */
		public function woostify_widget_categories( $elements_manager ) {
			// Woostify theme.
			$elements_manager->add_category(
				'woostify-theme',
				array(
					'title'  => esc_html__( 'Woostify Theme', 'woostify-pro' ),
					'active' => false,
				)
			);

			// Product categorty.
			$elements_manager->add_category(
				'woostify-product',
				array(
					'title'  => esc_html__( 'Woostify Product', 'woostify-pro' ),
					'active' => false,
				)
			);

			// My account page category.
			$elements_manager->add_category(
				'woostify-my-account-page',
				array(
					'title'  => esc_html__( 'Woostify My Account', 'woostify-pro' ),
					'active' => false,
				)
			);

			// Cart page category.
			$elements_manager->add_category(
				'woostify-cart-page',
				array(
					'title'  => esc_html__( 'Woostify Cart', 'woostify-pro' ),
					'active' => false,
				)
			);

			// Checkout page category.
			$elements_manager->add_category(
				'woostify-checkout-page',
				array(
					'title'  => esc_html__( 'Woostify Checkout', 'woostify-pro' ),
					'active' => false,
				)
			);

			// Thank you page category.
			$elements_manager->add_category(
				'woostify-thankyou-page',
				array(
					'title'  => esc_html__( 'Woostify Thankyou ', 'woostify-pro' ),
					'active' => false,
				)
			);

			// Search page category.
			$elements_manager->add_category(
				'woostify-search-page',
				array(
					'title'  => esc_html__( 'Woostify Search ', 'woostify-pro' ),
					'active' => false,
				)
			);

			// Woostify deprecated.
			$elements_manager->add_category(
				'woostify-deprecated',
				array(
					'title'  => esc_html__( 'Woostify Deprecated', 'woostify-pro' ),
					'active' => false,
				)
			);
		}

		/**
		 * Register preview mode scripts
		 */
		public function elementor_preview_register_scripts() {
			// Scripts.
			wp_enqueue_script( 'woostify-elementor-widget' );
			wp_enqueue_script( 'woostify-countdown' );

			// Styles.
			wp_enqueue_style( 'animate' );
		}

		/**
		 * Register elementor scripts
		 */
		public function elementor_front_end_register_scripts() {

			$options_free = woostify_options( false );
			$screen_width = $options_free['header_menu_breakpoint'];

			// Countdown.
			wp_register_script(
				'woostify-countdown',
				WOOSTIFY_PRO_ELEMENTOR_URI . 'assets/js/countdown' . woostify_suffix() . '.js',
				array(),
				WOOSTIFY_PRO_VERSION,
				true
			);

			// Elementor widgets js.
			wp_register_script(
				'woostify-elementor-widget',
				WOOSTIFY_PRO_ELEMENTOR_URI . 'assets/js/elementor-widgets' . woostify_suffix() . '.js',
				array( 'tiny-slider', 'woostify-countdown' ),
				WOOSTIFY_PRO_VERSION,
				true
			);

			// Elementor Product List js.
			wp_register_script(
				'woostify-product-list',
				WOOSTIFY_PRO_ELEMENTOR_URI . 'assets/js/product-list' . woostify_suffix() . '.js',
				array( 'slick' ),
				WOOSTIFY_PRO_VERSION,
				true
			);

			// Animate animation.
			wp_register_style(
				'animate',
				WOOSTIFY_PRO_ELEMENTOR_URI . 'assets/css/animate.css',
				array(),
				WOOSTIFY_PRO_VERSION
			);

			// Elementor widgets.
			wp_register_style(
				'woostify-elementor-widgets',
				WOOSTIFY_PRO_ELEMENTOR_URI . 'assets/css/woostify-elementor-widgets.css',
				array(),
				WOOSTIFY_PRO_VERSION
			);

			// Load Woostify Pro widget first.
			wp_enqueue_style( 'woostify-elementor-widgets' );

			$styles = '
				@media ( min-width: ' . esc_attr( $screen_width + 1 ) . 'px ) {
					.woostify-nav-menu-widget .main-navigation > ul {
					    display: inline-flex;
					    flex-wrap: wrap;
					}

					.woostify-nav-menu-widget .sub-menu {
					    display: inline-block !important;
					}

					.style-indicator-none .menu-item-arrow.arrow-icon {
    					display: none;
  					}

				}

				@media ( max-width: ' . esc_attr( $screen_width ) . 'px ) {

					.woostify-nav-menu-widget[data-menu-position="left"] .woostify-nav-menu-inner {
					    left: 0;
					    transform: translateX(-100%);
					}

					.woostify-nav-menu-widget[data-menu-position="left"] .woostify-close-nav-menu-button {
					    right: 15px;
					    transform: translateX(180%);
					}

					.woostify-nav-menu-widget[data-menu-position="right"] .woostify-nav-menu-inner {
					    right: 0;
					    transform: translateX(100%);
					}

					.woostify-nav-menu-widget[data-menu-position="right"] .woostify-close-nav-menu-button {
					    left: 15px;
					    transform: translateX(-180%);
					}

					.woostify-nav-menu-widget .menu-item-has-children > a {
					    display: flex;
					    align-items: center;
					}

					.woostify-nav-menu-widget .woostify-toggle-nav-menu-button {
					    display: inline-flex;
					    cursor: pointer;
					}

					.woostify-nav-menu-open .woostify-nav-menu-widget .woostify-nav-menu-inner {
					    opacity: 1;
					    visibility: visible;
					    transform: translateX(0);
					}

					.woostify-nav-menu-open .woostify-nav-menu-widget .woostify-nav-menu-overlay {
					    opacity: 1;
					    visibility: visible;
					}

					.woostify-nav-menu-open .woostify-nav-menu-widget .woostify-close-nav-menu-button {
					    opacity: 1;
					    visibility: visible;
					    transform: translateX(0);
					}

					.woostify-nav-menu-widget .nav-inner-ready {
					    opacity: 1;
					    visibility: visible;
					}

					.woostify-nav-menu-widget .woostify-nav-menu-inner {
					    text-align: left;
					    position: fixed;
					    top: 0;
					    bottom: 0;
					    background-color: #fff;
					    width: 320px;
					    max-width: 80%;
					    padding: 20px;
					    z-index: 50;
					    overflow-x: hidden;
					    overflow-y: scroll;
					    transition: transform 0.3s;
					}

					.logged-in.admin-bar .woostify-nav-menu-widget .woostify-nav-menu-inner {
					    top: 46px;
					}

					.woostify-nav-menu-widget .woostify-nav-menu-inner .arrow-icon {
					    transition-duration: 0.3s;
					}

					.woostify-nav-menu-widget .woostify-nav-menu-inner .arrow-icon.active {
					    transform: rotate(180deg);
					}

					.woostify-nav-menu-widget .woostify-nav-menu-inner .sub-menu {
					    display: none;
					    padding-left: 10px;
					}

					.woostify-nav-menu-widget .woostify-nav-menu-inner ul {
					    margin: 0;
					    padding: 0;
					    list-style: none;
					}

					.woostify-nav-menu-widget .woostify-nav-menu-inner li {
					    position: relative;
					}

					.woostify-nav-menu-widget .site-search {
					    margin-bottom: 15px;
					}
					.woostify-nav-menu-widget .main-navigation a,
					.woostify-nav-menu-widget .woostify-nav-menu-account-action a {
					    padding: 5px 0;
					    line-height: 2.5em;
					    font-size: 1.1em;
					}

					.woostify-nav-menu-widget .woostify-nav-menu-account-action {
					    border-top: 1px solid #eee;
					    margin-top: 30px;
					    display: block;
					}

					.woostify-nav-menu-widget .woostify-close-nav-menu-button {
					    opacity: 0;
					    visibility: hidden;
					    pointer-events: none;
					    top: 15px;
					    position: fixed;
					    width: 30px;
					    height: 30px;
					    cursor: pointer;
					    z-index: 50;
					    color: #fff;
					    border: 1px solid #fff;
					    display: flex;
					    justify-content: center;
					    align-items: center;
					    transition-duration: 0.3s;
					}

					.logged-in.admin-bar .woostify-nav-menu-widget .woostify-close-nav-menu-button {
					    top: 60px;
					}

					.woostify-nav-menu-widget .woostify-close-nav-menu-button:before {
					    content: "\e646";
					}

					.woostify-nav-menu-widget .woostify-nav-menu-overlay {
					    opacity: 0;
					    visibility: hidden;
					    position: fixed;
					    z-index: 49;
					    left: 0;
					    top: 0;
					    bottom: 0;
					    right: 0;
					    transition-duration: 0.3s;
					    background-color: rgba(0, 0, 0, 0.6);
					}
				}
			';
			wp_add_inline_style(
				'woostify-elementor-widgets',
				$styles
			);
		}
	}

	Woostify_Elementor_Widgets::init();
}
