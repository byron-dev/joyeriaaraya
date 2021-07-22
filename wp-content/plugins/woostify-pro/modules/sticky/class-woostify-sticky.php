<?php
/**
 * Sticky Woostify Class
 *
 * @package  Woostify Pro
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Element_Base;

if ( ! class_exists( 'Woostify_Sticky' ) ) {
	/**
	 * Class Woostify_Sticky
	 */
	class Woostify_Sticky {
		/**
		 * Instance variable
		 *
		 * @var null
		 */
		private static $instance = null;

		/**
		 * Instance
		 *
		 * @return Woostify_Sticky|null
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Woostify_Sticky constructor.
		 */
		public function __construct() {

			$this->add_actions();
		}

		/**
		 * Get widget name
		 *
		 * @return string
		 */
		public function get_name() {
			return 'woostify-sticky';
		}

		/**
		 * Register Elementor Controls
		 *
		 * @param Element_Base $element Elementor base.
		 */
		public function register_controls( Element_Base $element ) {
			$element->start_controls_section(
				'section_woostify_sticky',
				array(
					'label' => __( 'Sticky', 'woostify-pro' ),
					'tab'   => Controls_Manager::TAB_ADVANCED,
				)
			);

			$element->add_control(
				'woostify_sticky',
				array(
					'label'              => __( 'Enable', 'woostify-pro' ),
					'type'               => Controls_Manager::SWITCHER,
					'label_on'           => __( 'On', 'woostify-pro' ),
					'label_off'          => __( 'Off', 'woostify-pro' ),
					'return_value'       => 'yes',
					'default'            => '',
					'frontend_available' => true,
					'prefix_class'       => 'woostify-sticky-',
				)
			);

			$element->add_control(
				'woostify_transparent',
				array(
					'label'              => __( 'Transparent', 'woostify-pro' ),
					'type'               => Controls_Manager::SWITCHER,
					'separator'          => 'before',
					'label_on'           => __( 'On', 'woostify-pro' ),
					'label_off'          => __( 'Off', 'woostify-pro' ),
					'return_value'       => 'yes',
					'default'            => '',
					'frontend_available' => true,
					'prefix_class'       => 'woostify-header-transparent-',
				)
			);

			$element->add_control(
				'woostify_on',
				array(
					'label'              => __( 'Enable On', 'woostify-pro' ),
					'type'               => Controls_Manager::SELECT2,
					'multiple'           => true,
					'label_block'        => 'true',
					'default'            => array( 'desktop', 'tablet', 'mobile' ),
					'options'            => array(
						'desktop' => __( 'Desktop', 'woostify-pro' ),
						'tablet'  => __( 'Tablet', 'woostify-pro' ),
						'mobile'  => __( 'Mobile', 'woostify-pro' ),
					),
					'condition'          => array(
						'woostify_sticky!' => '',
					),
					'render_type'        => 'none',
					'frontend_available' => true,
				)
			);

			$element->add_responsive_control(
				'woostify_distance',
				array(
					'label'              => __( 'Scroll Distance (px)', 'woostify-pro' ),
					'type'               => Controls_Manager::SLIDER,
					'size_units'         => array( 'px' ),
					'description'        => __( 'Choose the scroll distance to enable Sticky Header Effects', 'woostify-pro' ),
					'frontend_available' => true,
					'default'            => array(
						'size' => 0,
					),
					'range'              => array(
						'px' => array(
							'min' => 0,
							'max' => 500,
						),
					),
					'condition'          => array(
						'woostify_sticky!' => '',
					),
				)
			);

			$element->add_control(
				'woostify_background_show',
				array(
					'label'              => __( 'Header Background', 'woostify-pro' ),
					'type'               => Controls_Manager::SWITCHER,
					'separator'          => 'before',
					'label_on'           => __( 'On', 'woostify-pro' ),
					'label_off'          => __( 'Off', 'woostify-pro' ),
					'return_value'       => 'yes',
					'default'            => '',
					'frontend_available' => true,
					'prefix_class'       => 'woostify-sticky-background-',
					'description'        => __( 'Choose background color after scrolling', 'woostify-pro' ),
					'condition'          => array(
						'woostify_sticky!' => '',
					),

				)
			);

			$element->add_control(
				'woostify_background',
				array(
					'label'              => __( 'Color', 'woostify-pro' ),
					'type'               => Controls_Manager::COLOR,
					'render_type'        => 'none',
					'frontend_available' => true,
					'condition'          => array(
						'woostify_background_show' => 'yes',
						'woostify_sticky!'         => '',
					),
				)
			);

			$element->add_control(
				'woostify_logo',
				array(
					'label'              => __( 'Logo Sticky', 'woostify-pro' ),
					'type'               => Controls_Manager::MEDIA,
					'frontend_available' => true,
					'description'        => __( 'Choose Logo after scrolling', 'woostify-pro' ),
					'condition'          => array(
						'woostify_sticky!' => '',
					),
				)
			);

			$element->add_control(
				'woostify_menu_color_custom',
				array(
					'label'              => __( 'Menu Color', 'woostify-pro' ),
					'type'               => Controls_Manager::SWITCHER,
					'separator'          => 'before',
					'label_on'           => __( 'On', 'woostify-pro' ),
					'label_off'          => __( 'Off', 'woostify-pro' ),
					'return_value'       => 'yes',
					'default'            => '',
					'frontend_available' => true,
					'description'        => __( 'Choose menu color after scrolling', 'woostify-pro' ),
					'condition'          => array(
						'woostify_sticky!' => '',
					),

				)
			);

			$element->add_control(
				'woostify_menu_color',
				array(
					'label'              => __( 'Menu Color', 'woostify-pro' ),
					'type'               => Controls_Manager::COLOR,
					'render_type'        => 'none',
					'frontend_available' => true,
					'condition'          => array(
						'woostify_menu_color_custom' => 'yes',
						'woostify_sticky!'           => '',
					),
				)
			);

			$element->end_controls_section();
		}

		/**
		 * Add actions
		 */
		private function add_actions() {
			add_action( 'elementor/element/section/section_advanced/after_section_end', array( $this, 'register_controls' ) );
			add_action( 'elementor/element/common/section_advanced/after_section_start', array( $this, 'register_controls' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * Enqueue scripts
		 */
		public function enqueue_scripts() {
			wp_enqueue_script(
				'woostify-sticky',
				WOOSTIFY_PRO_URI . 'assets/js/sticky' . woostify_suffix() . '.js',
				array( 'jquery' ),
				WOOSTIFY_PRO_VERSION,
				false
			);
		}
	}

	Woostify_Sticky::instance();
}
