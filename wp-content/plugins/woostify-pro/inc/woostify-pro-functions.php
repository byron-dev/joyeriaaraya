<?php
/**
 * Woostify pro functions.
 *
 * @package Woostify Pro
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'woostify_post_type_support' ) ) {
	/**
	 * Post type supports
	 */
	function woostify_post_type_support() {
		$post_types       = get_post_types();
		$post_types_unset = array(
			'attachment'          => __( 'attachment', 'woostify-pro' ),
			'revision'            => __( 'revision', 'woostify-pro' ),
			'nav_menu_item'       => __( 'nav_menu_item', 'woostify-pro' ),
			'custom_css'          => __( 'custom_css', 'woostify-pro' ),
			'customize_changeset' => __( 'customize_changeset', 'woostify-pro' ),
			'oembed_cache'        => __( 'oembed_cache', 'woostify-pro' ),
			'user_request'        => __( 'user_request', 'woostify-pro' ),
			'wp_block'            => __( 'wp_block', 'woostify-pro' ),
			'elementor_library'   => __( 'elementor_library', 'woostify-pro' ),
			'btf_builder'         => __( 'btf_builder', 'woostify-pro' ),
			'elementor-hf'        => __( 'elementor-hf', 'woostify-pro' ),
			'elementor_font'      => __( 'elementor_font', 'woostify-pro' ),
			'elementor_icons'     => __( 'elementor_icons', 'woostify-pro' ),
			'wpforms'             => __( 'wpforms', 'woostify-pro' ),
			'wpforms_log'         => __( 'wpforms_log', 'woostify-pro' ),
			'acf-field-group'     => __( 'acf-field-group', 'woostify-pro' ),
			'acf-field'           => __( 'acf-field', 'woostify-pro' ),
			'booked_appointments' => __( 'booked_appointments', 'woostify-pro' ),
			'wpcf7_contact_form'  => __( 'wpcf7_contact_form', 'woostify-pro' ),
			'scheduled-action'    => __( 'scheduled-action', 'woostify-pro' ),
			'shop_order'          => __( 'shop_order', 'woostify-pro' ),
			'shop_order_refund'   => __( 'shop_order_refund', 'woostify-pro' ),
			'shop_coupon'         => __( 'shop_coupon', 'woostify-pro' ),
		);
		$diff             = array_diff( $post_types, $post_types_unset );
		$default          = array(
			'all'       => __( 'All', 'woostify-pro' ),
			'blog'      => __( 'Blog Page', 'woostify-pro' ),
			'archive'   => __( 'Archive Page', 'woostify-pro' ),
			'search'    => __( 'Search Page', 'woostify-pro' ),
			'not_found' => __( '404 Page', 'woostify-pro' ),
		);
		$options          = array_merge( $default, $diff );

		return $options;
	}
}
