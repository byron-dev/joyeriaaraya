<?php
/**
 * Elementor Products Widget
 *
 * @package Woostify Pro
 */

namespace Elementor;

/**
 * Class woostify elementor products widget.
 */
class Woostify_Woocommerce_Notices extends Widget_Base {
	/**
	 * Category
	 */
	public function get_categories() {
		return array( 'woostify-theme' );
	}

	/**
	 * Name
	 */
	public function get_name() {
		return 'woostify-notices';
	}

	/**
	 * Add a script.
	 */
	public function get_script_depends() {
		return array( 'woostify-remove-default-notice' );
	}

	/**
	 * Title
	 */
	public function get_title() {
		return esc_html__( 'Woostify - Notices', 'woostify-pro' );
	}

	/**
	 * Icon
	 */
	public function get_icon() {
		return 'eicon-woocommerce';
	}

	/**
	 * Controls
	 */
	protected function register_controls() {
		$this->section_general();
	}

	/**
	 * General
	 */
	private function section_general() {
		$this->start_controls_section(
			'product_content',
			array(
				'label' => esc_html__( 'General', 'woostify-pro' ),
			)
		);
	}

	/**
	 * Render
	 */
	protected function render() {
		// Sometime section is null. Make it the object.
		if ( ! WC()->session && woostify_is_elementor_editor() ) {
			WC()->session = new \WC_Session_Handler();
		}

		woocommerce_output_all_notices();
	}
}
Plugin::instance()->widgets_manager->register_widget_type( new Woostify_Woocommerce_Notices() );
