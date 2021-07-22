<?php
/**
 * Products render
 *
 * @package Woostify Pro
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main class
 */
abstract class Woostify_Base_Products_Renderer extends \WC_Shortcode_Products {

	/**
	 * Override original `get_content` that returns an HTML wrapper even if no results found.
	 *
	 * @return string Products HTML
	 */
	public function get_content() {
		// Sometime section is null. Make it the object.
		if ( ! WC()->session && woostify_is_elementor_editor() ) {
			WC()->session = new \WC_Session_Handler();
		}

		$result = $this->get_query_results();

		if ( empty( $result->total ) ) {
			return '';
		}

		return parent::get_content();
	}
}
