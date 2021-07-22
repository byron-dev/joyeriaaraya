<?php
/**
 * Elementor Products Base
 *
 * @package  Woostify Pro
 */

namespace Elementor;

/**
 * Woostify Elementor Slider Base.
 */
abstract class Woostify_Elementor_Products_Base extends Widget_Base {
	/**
	 * Query
	 *
	 * @param boolean $current_query The current query source.
	 */
	protected function section_query( $current_query = true ) {
		$source = array(
			'sale'     => esc_html__( 'Sale', 'woostify-pro' ),
			'featured' => esc_html__( 'Featured', 'woostify-pro' ),
			'latest'   => esc_html__( 'Latest Products', 'woostify-pro' ),
			'select'   => esc_html__( 'Manual Selection', 'woostify-pro' ),
		);

		if ( $current_query ) {
			$source['current_query'] = esc_html__( 'Current Query', 'woostify-pro' );
		}

		// Start.
		$this->start_controls_section(
			'product_query',
			array(
				'label' => esc_html__( 'Query', 'woostify-pro' ),
			)
		);

		// Source.
		$this->add_control(
			'source',
			array(
				'label'   => esc_html__( 'Source', 'woostify-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'latest',
				'options' => $source,
			)
		);

		// Include manual products.
		$this->add_control(
			'include_products',
			array(
				'label'     => esc_html__( 'Select Products', 'woostify-pro' ),
				'type'      => 'autocomplete',
				'condition' => array(
					'source' => 'select',
				),
				'query'     => array(
					'type' => 'post_type',
					'name' => 'product',
				),
			)
		);

		// TAB START.
		$this->start_controls_tabs(
			'query_tabs',
			array(
				'condition' => array(
					'source' => array( 'sale', 'featured', 'latest' ),
				),
			)
		);
		$this->start_controls_tab(
			'query_include',
			array(
				'label' => __( 'Include', 'woostify-pro' ),
			)
		);

		// Include products categories.
		$this->add_control(
			'include_terms',
			array(
				'label' => esc_html__( 'Terms', 'woostify-pro' ),
				'type'  => 'autocomplete',
				'query' => array(
					'type' => 'term',
					'name' => 'wc_term',
				),
			)
		);

		$this->end_controls_tab();
		$this->start_controls_tab(
			'query_exclude',
			array(
				'label' => __( 'Exclude', 'woostify-pro' ),
			)
		);

		// Exclude terms.
		$this->add_control(
			'exclude_terms',
			array(
				'label' => esc_html__( 'Terms', 'woostify-pro' ),
				'type'  => 'autocomplete',
				'query' => array(
					'type' => 'term',
					'name' => 'wc_term',
				),
			)
		);

		// Exclude products.
		$this->add_control(
			'exclude_products',
			array(
				'label' => esc_html__( 'Products', 'woostify-pro' ),
				'type'  => 'autocomplete',
				'query' => array(
					'type' => 'post_type',
					'name' => 'product',
				),
			)
		);

		// TAB END.
		$this->end_controls_tab();
		$this->end_controls_tabs();

		// Posts per page.
		$this->add_control(
			'count',
			array(
				'label'     => esc_html__( 'Total Products', 'woostify-pro' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 6,
				'min'       => 1,
				'max'       => 100,
				'step'      => 1,
				'separator' => 'before',
				'condition' => array(
					'source!' => 'current_query',
				),
			)
		);

		// Order by.
		$this->add_control(
			'order_by',
			array(
				'label'     => esc_html__( 'Order By', 'woostify-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'id',
				'condition' => array(
					'source!' => 'current_query',
				),
				'options'   => array(
					'id'         => esc_html__( 'ID', 'woostify-pro' ),
					'title'      => esc_html__( 'Title', 'woostify-pro' ),
					'price'      => esc_html__( 'Price', 'woostify-pro' ),
					'rating'     => esc_html__( 'Rating', 'woostify-pro' ),
					'popularity' => esc_html__( 'Popularity', 'woostify-pro' ),
					'date'       => esc_html__( 'Date', 'woostify-pro' ),
					'menu_order' => esc_html__( 'Menu Order', 'woostify-pro' ),
					'rand'       => esc_html__( 'Random', 'woostify-pro' ),
				),
			)
		);

		// Order.
		$this->add_control(
			'order',
			array(
				'label'     => esc_html__( 'Order', 'woostify-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'ASC',
				'condition' => array(
					'source!' => 'current_query',
				),
				'options'   => array(
					'ASC'  => esc_html__( 'ASC', 'woostify-pro' ),
					'DESC' => esc_html__( 'DESC', 'woostify-pro' ),
				),
			)
		);

		$this->end_controls_section();
	}
}
