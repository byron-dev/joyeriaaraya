<?php
/**
 * Elementor Recently Viewed Products Widget
 *
 * @package Woostify Pro
 */

namespace Elementor;

/**
 * Class woostify elementor product recently viewed widget.
 */
class Woostify_Elementor_Product_Recently_Viewed_Widget extends Woostify_Elementor_Slider_Base {
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
		return 'woostify-product-recently-viewed';
	}

	/**
	 * Title
	 */
	public function get_title() {
		return esc_html__( 'Woostify - Recently Viewed Products', 'woostify-pro' );
	}

	/**
	 * Icon
	 */
	public function get_icon() {
		return 'eicon-history';
	}

	/**
	 * Gets the keywords.
	 */
	public function get_keywords() {
		return array( 'woostify', 'woocommerce', 'shop', 'product', 'recently view', 'store' );
	}

	/**
	 * Controls
	 */
	protected function _register_controls() { // phpcs:ignore
		$this->general();
		$this->style();
	}

	/**
	 * General
	 */
	private function general() {
		$this->start_controls_section(
			'general',
			array(
				'label' => esc_html__( 'General', 'woostify-pro' ),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => esc_html__( 'Title', 'woostify-pro' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Recently Products', 'woostify-pro' ),
			)
		);

		$this->add_control(
			'title_2',
			array(
				'label'   => esc_html__( 'Empty Title', 'woostify-pro' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'No Recently Products', 'woostify-pro' ),
			)
		);

		$this->add_control(
			'html',
			array(
				'label'   => esc_html__( 'HTML Tag', 'woostify-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h4',
				'options' => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				),
			)
		);

		$this->add_control(
			'col',
			array(
				'type'    => Controls_Manager::SELECT,
				'label'   => esc_html__( 'Columns', 'woostify-pro' ),
				'default' => 4,
				'options' => array(
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
			'count',
			array(
				'label'   => esc_html__( 'Total Products', 'woostify-pro' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 4,
				'min'     => 1,
				'max'     => 100,
				'step'    => 1,
			)
		);

		$this->add_control(
			'order_by',
			array(
				'label'   => esc_html__( 'Order By', 'woostify-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'id',
				'options' => array(
					'id'   => esc_html__( 'ID', 'woostify-pro' ),
					'name' => esc_html__( 'Name', 'woostify-pro' ),
					'date' => esc_html__( 'Date', 'woostify-pro' ),
					'rand' => esc_html__( 'Random', 'woostify-pro' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => esc_html__( 'Order', 'woostify-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'ASC',
				'options' => array(
					'ASC'  => esc_html__( 'ASC', 'woostify-pro' ),
					'DESC' => esc_html__( 'DESC', 'woostify-pro' ),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style
	 */
	protected function style() {
		$this->start_controls_section(
			'style',
			array(
				'label' => esc_html__( 'Style', 'woostify-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Title alignment.
		$this->add_responsive_control(
			'Title_alignment',
			array(
				'type'           => Controls_Manager::CHOOSE,
				'label'          => esc_html__( 'Title Alignment', 'woostify-pro' ),
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
					'{{WRAPPER}} .title-product-recently' => 'text-align: {{VALUE}};',
				),
			)
		);

		// Title Typo.
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typo',
				'selector' => '{{WRAPPER}} .title-product-recently',
			)
		);

		// Color.
		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Text Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .title-product-recently' => 'color: {{VALUE}};',
				),
			)
		);

		// Title Spacing.
		$this->add_control(
			'title_spacing',
			array(
				'label'     => __( 'Title Spacing', 'woostify-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'max' => 1000,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .title-product-recently' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}


	/**
	 * Render
	 */
	protected function render() {
		$settings                  = $this->get_settings_for_display();
		$html                      = $settings['html'];
		$cookies                   = isset( $_COOKIE['woostify_product_recently_viewed'] ) ? $_COOKIE['woostify_product_recently_viewed'] : false;
		$title_no_product_recently = $settings['title_2'];
		$title_product_recently    = $settings['title'];

		if ( ! $cookies ) {
			if ( $title_no_product_recently ) {
				?>
					<<?php echo esc_attr( $html ); ?> class="title-product-recently"><?php echo esc_html( $title_no_product_recently ); ?></<?php echo esc_attr( $html ); ?>>
				<?php
			}
			return;
		}

		$ids       = explode( '|', $cookies );
		$container = woostify_site_container();
		$args      = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $settings['count'],
			'orderby'        => $settings['order_by'],
			'order'          => $settings['order'],
			'post__in'       => $ids,
		);

		$products_query = new \WP_Query( $args );
		if ( ! $products_query->have_posts() ) {
			?>
			<p class="text-center"><?php esc_html_e( 'No posts found!', 'woostify-pro' ); ?></p>
			<?php
			return;
		}

		if ( $title_product_recently ) {
			?>
				<<?php echo esc_attr( $html ); ?> class="title-product-recently"><?php echo esc_html( $title_product_recently ); ?></<?php echo esc_attr( $html ); ?>>
			<?php
		}

		?>
		<div class="woostify-products-recently-viewed-widget">
			<?php
			global $woocommerce_loop;
			$woocommerce_loop['columns'] = (int) $settings['col'];

			woocommerce_product_loop_start();

			while ( $products_query->have_posts() ) :
				$products_query->the_post();

				wc_get_template_part( 'content', 'product' );
			endwhile;

			woocommerce_product_loop_end();

			// Reset loop.
			woocommerce_reset_loop();
			wp_reset_postdata();
			?>
		</div>

		<?php
	}
}
Plugin::instance()->widgets_manager->register_widget_type( new Woostify_Elementor_Product_Recently_Viewed_Widget() );
