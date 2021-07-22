<?php
/**
 * Elementor Product Categories Widget
 *
 * @package Woostify Pro
 */

namespace Elementor;

/**
 * Class woostify elementor product categories widget.
 */
class Woostify_Elementor_Product_Categories_Widget extends Widget_Base {
	/**
	 * Category
	 */
	public function get_categories() {
		return array( 'woostify-product' );
	}

	/**
	 * Name
	 */
	public function get_name() {
		return 'woostify-product-categories';
	}

	/**
	 * Title
	 */
	public function get_title() {
		return esc_html__( 'Woostify - Product Categories', 'woostify-pro' );
	}

	/**
	 * Icon
	 */
	public function get_icon() {
		return 'eicon-product-categories';
	}

	/**
	 * Controls
	 */
	protected function _register_controls() { // phpcs:ignore
		$this->section_general();
		$this->section_query();
		$this->section_style();
		$this->section_categories();
	}

	/**
	 * General
	 */
	private function section_general() {
		$this->start_controls_section(
			'product_general',
			array(
				'label' => esc_html__( 'General', 'woostify-pro' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'woostify-pro' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'Product Categories', 'woostify-pro' ),
			)
		);

		$this->add_control(
			'count',
			array(
				'label'        => __( 'Show product counts', 'woostify-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'none',
				'label_on'     => __( 'Yes', 'woostify-pro' ),
				'label_off'    => __( 'No', 'woostify-pro' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'hierarchical',
			array(
				'label'        => __( 'Show hierarchy', 'woostify-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'none',
				'label_on'     => __( 'Yes', 'woostify-pro' ),
				'label_off'    => __( 'No', 'woostify-pro' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'hide_empty',
			array(
				'label'        => __( 'Hide empty categories', 'woostify-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'none',
				'label_on'     => __( 'Yes', 'woostify-pro' ),
				'label_off'    => __( 'No', 'woostify-pro' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'max_depth',
			array(
				'label' => __( 'Maximum depth', 'woostify-pro' ),
				'type'  => Controls_Manager::NUMBER,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Query
	 */
	private function section_query() {
		$this->start_controls_section(
			'category_query',
			array(
				'label' => esc_html__( 'Query', 'woostify-pro' ),
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => esc_html__( 'Source', 'woostify-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'all',
				'options' => array(
					'all'    => esc_html__( 'Show All', 'woostify-pro' ),
					'parent' => esc_html__( 'By Parent', 'woostify-pro' ),

				),
			)
		);

		$categories = get_terms( 'product_cat' );

		$options = array();
		foreach ( $categories as $category ) {
			$options[ $category->term_id ] = $category->name;
		}

		$parent_options = array( '0' => __( 'Only Top Level', 'woostify-pro' ) ) + $options;
		$this->add_control(
			'cat_parent',
			array(
				'label'     => __( 'Parent', 'woostify-pro' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '0',
				'options'   => $parent_options,
				'condition' => array(
					'source' => 'parent',
				),
			)
		);

		$this->add_control(
			'show_parent',
			array(
				'label'        => __( 'Show parent', 'woostify-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'none',
				'label_on'     => __( 'Yes', 'woostify-pro' ),
				'label_off'    => __( 'No', 'woostify-pro' ),
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => array(
					'source' => 'parent',
				),
			)
		);

		// ORDER BY.
		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Order By', 'woostify-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'name',
				'options' => array(
					'name'        => __( 'Name', 'woostify-pro' ),
					'slug'        => __( 'Slug', 'woostify-pro' ),
					'description' => __( 'Description', 'woostify-pro' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => __( 'Order', 'woostify-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => array(
					'asc'  => __( 'ASC', 'woostify-pro' ),
					'desc' => __( 'DESC', 'woostify-pro' ),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style
	 */
	private function section_style() {
		$this->start_controls_section(
			'product_style',
			array(
				'label' => esc_html__( 'Title', 'woostify-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Color.
		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product-categories-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typo',
				'selector' => '{{WRAPPER}} .product-categories-title',
			)
		);

		// Title Spacing.
		$this->add_control(
			'title_spacing',
			array(
				'label'     => __( 'Spacing', 'woostify-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 200,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .product-categories-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Categories
	 */
	private function section_categories() {
		$this->start_controls_section(
			'products_categories',
			array(
				'label' => esc_html__( 'Categories', 'woostify-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'categories_list_typo',
				'label'    => esc_html__( 'Typography', 'woostify-pro' ),
				'selector' => '{{WRAPPER}} .product-categories li a',
			)
		);

		// List Spacing.
		$this->add_control(
			'categories_list_spacing',
			array(
				'label'     => __( 'Spacing', 'woostify-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 200,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .product-categories li' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// TAB START.
		$this->start_controls_tabs( 'categories_list_tabs' );

		// Normal.
		$this->start_controls_tab(
			'categories_list_normal',
			array(
				'label' => __( 'Normal', 'woostify-pro' ),
			)
		);

		// Color.
		$this->add_control(
			'categories_list_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product-categories li a ' => 'color: {{VALUE}};',
				),
			)
		);

		// END NORMAL.
		$this->end_controls_tab();

		// HOVER.
		$this->start_controls_tab(
			'categories_list_hover',
			array(
				'label' => __( 'Hover', 'woostify-pro' ),
			)
		);

		// Hover color.
		$this->add_control(
			'categories_list_hover_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product-categories li a:hover ' => 'color: {{VALUE}};',
				),
			)
		);

		// TAB END.
		$this->end_controls_tab();
		$this->end_controls_tabs();

		// Count.
		$this->add_control(
			'categories_count',
			array(
				'label'     => __( 'Count', 'woostify-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'categories_count_typo',
				'label'    => esc_html__( 'Typography', 'woostify-pro' ),
				'selector' => '{{WRAPPER}} .product-categories .count',
			)
		);

		// Color.
		$this->add_control(
			'categories_count_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .product-categories .count ' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();

	}

	/**
	 * Render
	 */
	protected function render() {
		$settings     = $this->get_settings_for_display();
		$title        = $settings['title'];
		$order        = $settings['order'];
		$orderby      = $settings['orderby'];
		$count        = $settings['count'];
		$hierarchical = $settings['hierarchical'];
		$hide_empty   = $settings['hide_empty'];
		$max_depth    = $settings['max_depth'];
		$parent_cat   = $settings['cat_parent'];
		$show_parent  = $settings['show_parent'];

		?>
			<div class="woostify-product-categories">
				<h3 class="product-categories-title"><?php echo esc_html( $title ); ?></h3>
				<?php
					$list_args = array(
						'show_count'   => $count,
						'taxonomy'     => 'product_cat',
						'hide_empty'   => $hide_empty,
						'hierarchical' => $hierarchical,
						'show_count'   => $count,
						'order'        => $order,
						'orderby'      => $orderby,
					);

					$termchildren = get_term_children( $parent_cat, 'product_cat' );

					if ( $show_parent ) {
						$termchildren[] = $parent_cat;
					}

					if ( $termchildren ) {
						$list_args['include'] = $termchildren;
					} else {
						$list_args['include'] = $parent_cat;
					}

					$this->current_cat   = false;
					$this->cat_ancestors = array();

					include_once WC()->plugin_path() . '/includes/walkers/class-wc-product-cat-list-walker.php';

					$list_args['walker']                     = new \WC_Product_Cat_List_Walker();
					$list_args['title_li']                   = '';
					$list_args['pad_counts']                 = 1;
					$list_args['show_option_none']           = __( 'No product categories exist.', 'woostify-pro' );
					$list_args['current_category']           = ( $this->current_cat ) ? $this->current_cat->term_id : '';
					$list_args['current_category_ancestors'] = $this->cat_ancestors;
					$list_args['max_depth']                  = $max_depth;

					echo '<ul class="product-categories">';

					wp_list_categories( apply_filters( 'woocommerce_product_categories_widget_args', $list_args ) );

					echo '</ul>';
					?>
			</div>
		<?php
	}
}
Plugin::instance()->widgets_manager->register_widget_type( new Woostify_Elementor_Product_Categories_Widget() );
