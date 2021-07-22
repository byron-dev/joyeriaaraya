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
class Woostify_Elementor_Products extends Woostify_Elementor_Products_Base {
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
		return 'woostify-new-products';
	}

	/**
	 * Title
	 */
	public function get_title() {
		return esc_html__( 'Woostify - Products', 'woostify-pro' );
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
	protected function _register_controls() { // phpcs:ignore
		$this->section_general();
		$this->section_query( true );
		$this->section_product_style();
		$this->section_box_style();
		$this->section_sale_flash();
		$this->section_icons_style();
		$this->section_pagination();
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

		$this->add_responsive_control(
			'col',
			array(
				'type'           => Controls_Manager::SELECT,
				'label'          => esc_html__( 'Columns', 'woostify-pro' ),
				'default'        => 4,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'options'        => array(
					1 => 1,
					2 => 2,
					3 => 3,
					4 => 4,
					5 => 5,
					6 => 6,
				),
			)
		);

		$this->add_control(
			'pro_pagi',
			array(
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Pagination', 'woostify-pro' ),
				'default'      => '',
				'label_on'     => esc_html__( 'Yes', 'woostify-pro' ),
				'label_off'    => esc_html__( 'No', 'woostify-pro' ),
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'result_count',
			array(
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Show Result Count', 'woostify-pro' ),
				'default'      => '',
				'label_on'     => esc_html__( 'Yes', 'woostify-pro' ),
				'label_off'    => esc_html__( 'No', 'woostify-pro' ),
				'return_value' => 'yes',
				'condition'    => array(
					'pro_pagi' => 'yes',
				),
			)
		);

		$this->add_control(
			'ordering',
			array(
				'type'         => Controls_Manager::SWITCHER,
				'label'        => esc_html__( 'Show Order', 'woostify-pro' ),
				'default'      => '',
				'label_on'     => esc_html__( 'Yes', 'woostify-pro' ),
				'label_off'    => esc_html__( 'No', 'woostify-pro' ),
				'return_value' => 'yes',
				'condition'    => array(
					'pro_pagi' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Product Box
	 */
	private function section_box_style() {
		$this->start_controls_section(
			'box_style',
			array(
				'label' => esc_html__( 'Box', 'woostify-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Border.
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'box_style_border',
				'label'    => __( 'Border', 'woostify-pro' ),
				'selector' => '{{WRAPPER}} .woostify-products-widget .products .product',
			)
		);

		// Border Box radius.
		$this->add_responsive_control(
			'box_style_border_radius',
			array(
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Border Radius', 'woostify-pro' ),
				'size_units' => array(
					'px',
					'em',
				),
				'selectors'  => array(
					'{{WRAPPER}} .woostify-products-widget .products .product' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// TAB START.
		$this->start_controls_tabs( 'product_box_tabs' );

		// Normal.
		$this->start_controls_tab(
			'product_box_normal',
			array(
				'label' => __( 'Normal', 'woostify-pro' ),
			)
		);

		// BG color.
		$this->add_control(
			'product_box_bg_color',
			array(
				'label'     => __( 'Background Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .products .product' => 'background-color: {{VALUE}};',
				),
			)
		);

		// END NORMAL.
		$this->end_controls_tab();

		// HOVER.
		$this->start_controls_tab(
			'product_box_hover',
			array(
				'label' => __( 'Hover', 'woostify-pro' ),
			)
		);

		// Hover BG color.
		$this->add_control(
			'product_box_hover_bg_color',
			array(
				'label'     => __( 'Background Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .products .product:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		// Hover border color.
		$this->add_control(
			'product_box_hover_border_color',
			array(
				'label'     => __( 'Border Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .products .product:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		// TAB END.
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Product style
	 */
	private function section_product_style() {
		$this->start_controls_section(
			'product_style',
			array(
				'label' => esc_html__( 'Products', 'woostify-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Column Gap.
		$this->add_responsive_control(
			'columns_gap',
			array(
				'label'           => __( 'Columns Gap', 'woostify-pro' ),
				'type'            => Controls_Manager::SLIDER,
				'range'           => array(
					'px' => array(
						'max' => 200,
					),
				),
				'devices'         => array(
					'desktop',
					'tablet',
					'mobile',
				),
				'desktop_default' => array(
					'size' => 20,
					'unit' => 'px',
				),
				'tablet_default'  => array(
					'size' => 20,
					'unit' => 'px',
				),
				'mobile_default'  => array(
					'size' => 20,
					'unit' => 'px',
				),
				'selectors'       => array(
					'{{WRAPPER}} .woostify-products-widget .products' => 'grid-column-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// Rows Gap.
		$this->add_responsive_control(
			'rows_gap',
			array(
				'label'           => __( 'Rows Gap', 'woostify-pro' ),
				'type'            => Controls_Manager::SLIDER,
				'range'           => array(
					'px' => array(
						'max' => 200,
					),
				),
				'devices'         => array(
					'desktop',
					'tablet',
					'mobile',
				),
				'desktop_default' => array(
					'size' => 30,
					'unit' => 'px',
				),
				'tablet_default'  => array(
					'size' => 30,
					'unit' => 'px',
				),
				'mobile_default'  => array(
					'size' => 30,
					'unit' => 'px',
				),
				'selectors'       => array(
					'{{WRAPPER}} .woostify-products-widget .products' => 'grid-row-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'product_image',
			array(
				'label'     => __( 'image', 'woostify-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Border.
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'add_to_cart_button_border',
				'label'    => __( 'Border', 'woostify-pro' ),
				'selector' => '{{WRAPPER}} .woostify-products-widget .product-loop-image',
			)
		);

		// Border Image radius.
		$this->add_responsive_control(
			'padding',
			array(
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Border Radius', 'woostify-pro' ),
				'size_units' => array(
					'px',
					'em',
				),
				'selectors'  => array(
					'{{WRAPPER}} .woostify-products-widget .product-loop-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Image Spacing.
		$this->add_responsive_control(
			'image_spacing',
			array(
				'label'           => __( 'Spacing', 'woostify-pro' ),
				'type'            => Controls_Manager::SLIDER,
				'range'           => array(
					'px' => array(
						'max' => 200,
					),
				),
				'devices'         => array(
					'desktop',
					'tablet',
					'mobile',
				),
				'desktop_default' => array(
					'size' => 0,
					'unit' => 'px',
				),
				'tablet_default'  => array(
					'size' => 0,
					'unit' => 'px',
				),
				'mobile_default'  => array(
					'size' => 0,
					'unit' => 'px',
				),
				'selectors'       => array(
					'{{WRAPPER}} .woostify-products-widget .product-loop-image-wrapper' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'product_title',
			array(
				'label'     => __( 'Title', 'woostify-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'product_style_title_typo',
				'label'    => esc_html__( 'Typography', 'woostify-pro' ),
				'selector' => '{{WRAPPER}} .woocommerce-loop-product__title a',
			)
		);

		// Title Spacing.
		$this->add_responsive_control(
			'title_spacing',
			array(
				'label'           => __( 'Spacing', 'woostify-pro' ),
				'type'            => Controls_Manager::SLIDER,
				'range'           => array(
					'px' => array(
						'max' => 200,
					),
				),
				'devices'         => array(
					'desktop',
					'tablet',
					'mobile',
				),
				'desktop_default' => array(
					'size' => 0,
					'unit' => 'px',
				),
				'tablet_default'  => array(
					'size' => 0,
					'unit' => 'px',
				),
				'mobile_default'  => array(
					'size' => 0,
					'unit' => 'px',
				),
				'selectors'       => array(
					'{{WRAPPER}} .woostify-products-widget .woocommerce-loop-product__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// TAB START.
		$this->start_controls_tabs( 'product_style_tabs' );

		// Normal.
		$this->start_controls_tab(
			'product_style_normal',
			array(
				'label' => __( 'Normal', 'woostify-pro' ),
			)
		);

		// Color.
		$this->add_control(
			'product_style_title_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce-loop-product__title a ' => 'color: {{VALUE}};',
				),
			)
		);

		// END NORMAL.
		$this->end_controls_tab();

		// HOVER.
		$this->start_controls_tab(
			'product_style_hover',
			array(
				'label' => __( 'Hover', 'woostify-pro' ),
			)
		);

		// Hover color.
		$this->add_control(
			'product_style_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woocommerce-loop-product__title a:hover ' => 'color: {{VALUE}};',
				),
			)
		);

		// TAB END.
		$this->end_controls_tab();
		$this->end_controls_tabs();

		// Price.
		$this->add_control(
			'product_price',
			array(
				'label'     => __( 'Price', 'woostify-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Color Price.
		$this->add_control(
			'product_price_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .price ins span, {{WRAPPER}} .woostify-products-widget .price span' => 'color: {{VALUE}};',
				),
			)
		);

		// Price Typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'product_price_typo',
				'label'    => esc_html__( 'Typography', 'woostify-pro' ),
				'selector' => '{{WRAPPER}} .woostify-products-widget .price ins span, {{WRAPPER}} .woostify-products-widget .price span',
			)
		);

		// Regular Price.
		$this->add_control(
			'product_regular_price',
			array(
				'label'     => __( 'Sale Price', 'woostify-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		// Color Regular Price.
		$this->add_control(
			'product_regular_price_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .price del span ' => 'color: {{VALUE}};',
				),
			)
		);

		// Regular Price Typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'product_regular_price_typo',
				'label'    => esc_html__( 'Typography', 'woostify-pro' ),
				'selector' => '{{WRAPPER}} .woostify-products-widget .price del span',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Sale Flash
	 */
	private function section_sale_flash() {
		$this->start_controls_section(
			'section_sale_flash',
			array(
				'label' => __( 'Sale Flash', 'woostify-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Color.
		$this->add_control(
			'product_sale_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .woostify-tag-on-sale' => 'color: {{VALUE}};',
				),
			)
		);

		// Hover BG color.
		$this->add_control(
			'product_sale_bg_color',
			array(
				'label'     => __( 'Background Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .woostify-tag-on-sale' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'product_sale_typo',
				'label'    => esc_html__( 'Typography', 'woostify-pro' ),
				'selector' => '{{WRAPPER}} .woostify-products-widget .woostify-tag-on-sale',
			)
		);

		// Border Sale radius.
		$this->add_responsive_control(
			'product_sale_border_radius',
			array(
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Border Radius', 'woostify-pro' ),
				'size_units' => array(
					'px',
					'em',
				),
				'selectors'  => array(
					'{{WRAPPER}} .woostify-products-widget .woostify-tag-on-sale' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Sale Width.
		$this->add_control(
			'sale_width',
			array(
				'label'     => __( 'Width', 'woostify-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 200,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .woostify-tag-on-sale' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// Sale Height.
		$this->add_control(
			'sale_height',
			array(
				'label'     => __( 'Height', 'woostify-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 200,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .woostify-tag-on-sale' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Button Style.
	 */
	private function section_icons_style() {
		$this->start_controls_section(
			'section_button',
			array(
				'label' => __( 'Button', 'woostify-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Button.
		$this->add_control(
			'product_button',
			array(
				'label' => __( 'Add To Cart', 'woostify-pro' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		// Button Padding.
		$this->add_responsive_control(
			'button_padding',
			array(
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => __( 'Padding', 'woostify-pro' ),
				'size_units' => array(
					'px',
					'em',
				),
				'selectors'  => array(
					'{{WRAPPER}} .woostify-products-widget .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		// Button Typography.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'product_button_typo',
				'label'    => esc_html__( 'Typography', 'woostify-pro' ),
				'selector' => '{{WRAPPER}} .woostify-products-widget .button',
			)
		);

		// Border.
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'product_button_border',
				'label'    => __( 'Border', 'woostify-pro' ),
				'selector' => '{{WRAPPER}} .woostify-products-widget .button',
			)
		);

		// TAB START.
		$this->start_controls_tabs( 'product_button_tabs' );

		// Normal.
		$this->start_controls_tab(
			'product_button_normal',
			array(
				'label' => __( 'Normal', 'woostify-pro' ),
			)
		);

		// Color.
		$this->add_control(
			'product_button_text_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .button' => 'color: {{VALUE}};',
				),
			)
		);

		// BG color.
		$this->add_control(
			'product_button_bg_color',
			array(
				'label'     => __( 'Background Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .button' => 'background-color: {{VALUE}};',
				),
			)
		);

		// END NORMAL.
		$this->end_controls_tab();

		// HOVER.
		$this->start_controls_tab(
			'product_button_hover',
			array(
				'label' => __( 'Hover', 'woostify-pro' ),
			)
		);

		// Hover color.
		$this->add_control(
			'product_hover_text_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .button:hover' => 'color: {{VALUE}};',
				),
			)
		);

		// Hover BG color.
		$this->add_control(
			'product_button_hover_bg_color',
			array(
				'label'     => __( 'Background Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .button:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		// Hover border color.
		$this->add_control(
			'product_button_hover_border_color',
			array(
				'label'     => __( 'Border Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .button:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		// TAB END.
		$this->end_controls_tab();
		$this->end_controls_tabs();

		// Button Spacing.
		$this->add_responsive_control(
			'button_spacing',
			array(
				'label'           => __( 'Spacing', 'woostify-pro' ),
				'type'            => Controls_Manager::SLIDER,
				'range'           => array(
					'px' => array(
						'max' => 200,
					),
				),
				'devices'         => array(
					'desktop',
					'tablet',
					'mobile',
				),
				'desktop_default' => array(
					'size' => 0,
					'unit' => 'px',
				),
				'tablet_default'  => array(
					'size' => 0,
					'unit' => 'px',
				),
				'mobile_default'  => array(
					'size' => 0,
					'unit' => 'px',
				),
				'selectors'       => array(
					'{{WRAPPER}} .woostify-products-widget .button' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'icons_quickview',
			array(
				'label'     => __( 'Quick View', 'woostify-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'product_style_quickview_typo',
				'label'    => esc_html__( 'Typography', 'woostify-pro' ),
				'selector' => '{{WRAPPER}} .woostify-products-widget .product-quick-view-btn:before',
			)
		);

		// TAB START.
		$this->start_controls_tabs( 'product_quickview_tabs' );

		// Normal.
		$this->start_controls_tab(
			'product_quickview_normal',
			array(
				'label' => __( 'Normal', 'woostify-pro' ),
			)
		);

		// Color.
		$this->add_control(
			'product_quickview_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .product-quick-view-btn:before' => 'color: {{VALUE}};',
					'{{WRAPPER}} .woostify-products-widget .product-quick-view-btn' => 'color: {{VALUE}};',
				),
			)
		);

		// BG color.
		$this->add_control(
			'product_quickview_bg_color',
			array(
				'label'     => __( 'Background Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .product-quick-view-btn' => 'background-color: {{VALUE}};',
				),
			)
		);

		// END NORMAL.
		$this->end_controls_tab();

		// HOVER.
		$this->start_controls_tab(
			'product_quickview_hover',
			array(
				'label' => __( 'Hover', 'woostify-pro' ),
			)
		);

		// Hover color.
		$this->add_control(
			'product_hover_quickview_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .quick-view-with-text:hover.product-quick-view-btn:before,
					{{WRAPPER}} .woostify-products-widget .product-quick-view-btn:hover,
					{{WRAPPER}} .woostify-products-widget .quick-view-with-icon:hover.product-quick-view-btn:before' => 'color: {{VALUE}};',
				),
			)
		);

		// Hover BG color.
		$this->add_control(
			'product_quickview_hover_bg_color',
			array(
				'label'     => __( 'Background Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .product-quick-view-btn:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		// TAB END.
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'icons_wishlist',
			array(
				'label'     => __( 'Wishlist', 'woostify-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'product_style_wishlist_typo',
				'label'    => esc_html__( 'Typography', 'woostify-pro' ),
				'selector' => '{{WRAPPER}} .woostify-products-widget .tinvwl_add_to_wishlist_button:before',
			)
		);

		// TAB START.
		$this->start_controls_tabs( 'product_wishlist_tabs' );

		// Normal.
		$this->start_controls_tab(
			'product_wishlist_normal',
			array(
				'label' => __( 'Normal', 'woostify-pro' ),
			)
		);

		// Color.
		$this->add_control(
			'product_wishlist_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .tinvwl_add_to_wishlist_button:before' => 'color: {{VALUE}};',
				),
			)
		);

		// BG color.
		$this->add_control(
			'product_wishlist_bg_color',
			array(
				'label'     => __( 'Background Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .tinvwl_add_to_wishlist_button' => 'background-color: {{VALUE}};',
				),
			)
		);

		// END NORMAL.
		$this->end_controls_tab();

		// HOVER.
		$this->start_controls_tab(
			'product_wishlist_hover',
			array(
				'label' => __( 'Hover', 'woostify-pro' ),
			)
		);

		// Hover color.
		$this->add_control(
			'product_hover_wishlist_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .tinvwl-position-after:hover.tinvwl_add_to_wishlist_button:before' => 'color: {{VALUE}};',
				),
			)
		);

		// Hover BG color.
		$this->add_control(
			'product_wishlist_hover_bg_color',
			array(
				'label'     => __( 'Background Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-products-widget .tinvwl_add_to_wishlist_button:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		// TAB END.
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Pagination
	 */
	private function section_pagination() {
		$this->start_controls_section(
			'pro_pagi_section',
			array(
				'label'     => esc_html__( 'Pagination', 'woostify-pro' ),
				'condition' => array(
					'pro_pagi' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'pagi_position',
			array(
				'type'           => Controls_Manager::CHOOSE,
				'label'          => esc_html__( 'Alignment', 'woostify-pro' ),
				'options'        => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'woostify-pro' ),
						'icon'  => 'fa fa-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'woostify-pro' ),
						'icon'  => 'fa fa-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'woostify-pro' ),
						'icon'  => 'fa fa-align-right',
					),
				),
				'default'        => 'center',
				'tablet_default' => 'center',
				'mobile_default' => 'center',
				'selectors'      => array(
					'{{WRAPPER}} .pagination' => 'text-align: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'pagi_space',
			array(
				'type'           => Controls_Manager::DIMENSIONS,
				'label'          => esc_html__( 'Space', 'woostify-pro' ),
				'size_units'     => array( 'px', 'em' ),
				'selectors'      => array(
					'{{WRAPPER}} .pagination' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'default'        => array(
					'top'      => '30',
					'right'    => '0',
					'bottom'   => '0',
					'left'     => '0',
					'unit'     => 'px',
					'isLinked' => false,
				),
				'tablet_default' => array(
					'top'      => '20',
					'right'    => '0',
					'bottom'   => '20',
					'left'     => '0',
					'unit'     => 'px',
					'isLinked' => false,
				),
				'mobile_default' => array(
					'top'      => '15',
					'right'    => '0',
					'bottom'   => '15',
					'left'     => '0',
					'unit'     => 'px',
					'isLinked' => false,
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Gets the shortcode object.
	 *
	 * @param      array $settings The settings.
	 */
	protected function get_shortcode_object( $settings ) {
		if ( 'current_query' === $settings['source'] ) {
			$type = 'current_query';
			if ( is_search() && ( 'activated' === get_option( 'woostify_wc_ajax_product_search' ) || defined( 'WOOSTIFY_PRO_AJAX_PRODUCT_SEARCH' ) ) ) {
				$data = $this->search_data();
				return new \Woostify\Woocommerce\Products_Render( $settings, $data );
			}
			return new Woostify_Current_Query_Renderer( $settings, $type );
		}

		$type = 'products';
		return new Woostify_Products_Renderer( $settings, $type );
	}

	/**
	 * Return search data in search page.
	 */
	public function search_data() {
		$search_by_sku   = get_option( 'woostify_ajax_search_product_by_sku', '1' );
		$cat_id          = '';
		$out_stock       = get_option( 'woostify_ajax_search_product_remove_out_stock_product', '0' );
		$search_by_title = get_option( 'woostify_ajax_search_product_by_title', '1' );
		$s               = get_query_var( 's' );
		$orderby         = get_query_var( 'orderby' );
		$paged           = get_query_var( 'page' ) ? get_query_var( 'page' ) : 1;
		if ( array_key_exists( 'cat_id', $_GET ) ) { // phpcs:ignore
			$cat_id = (int) $_GET['cat_id']; // phpcs:ignore
		}

		$args = array(
			'keyword'          => $s,
			'search_by_sku'    => $search_by_sku,
			'search_by_title'  => $search_by_title,
			'cat_id'           => $cat_id,
			'paged'            => $paged,
			'product_per_page' => woostify_products_per_page(),
			'outstock'         => $out_stock,
		);

		return $args;
	}

	/**
	 * Render
	 */
	protected function render() {
		// For Products_Renderer.
		if ( ! isset( $GLOBALS['post'] ) ) {
			$GLOBALS['post'] = null; // phpcs:ignore
		}

		$settings  = $this->get_settings();
		$shortcode = $this->get_shortcode_object( $settings );
		$content   = $shortcode->get_content();

		if ( empty( $content ) ) {
			echo '<p class="woocommerce-info">' . esc_html__( 'No products found!', 'woostify-pro' ) . '</p>';
			return;
		}

		?>
		<div class="woostify-products-widget">
			<?php echo $content; // phpcs:ignore ?>
		</div>
		<?php
	}
}
Plugin::instance()->widgets_manager->register_widget_type( new Woostify_Elementor_Products() );
