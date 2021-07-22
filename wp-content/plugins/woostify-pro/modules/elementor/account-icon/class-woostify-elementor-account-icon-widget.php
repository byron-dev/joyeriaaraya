<?php
/**
 * Elementor Account Icon Widget
 *
 * @package Woostify Pro
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for woostify elementor Account icon widget.
 */
class Woostify_Elementor_Account_Icon_Widget extends Widget_Base {
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
		return 'woostify-account-icon';
	}

	/**
	 * Gets the title.
	 */
	public function get_title() {
		return __( 'Woostify - Account Icon', 'woostify-pro' );
	}

	/**
	 * Gets the icon.
	 */
	public function get_icon() {
		return 'eicon-person';
	}

	/**
	 * Gets the keywords.
	 */
	public function get_keywords() {
		return array( 'woostify', 'account', 'user' );
	}

	/**
	 * Get menu list
	 */
	private function get_nav_list() {
		$nav_menu = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
		$menu_ids = array( 'none' => __( 'None', 'woostify-pro' ) );

		if ( ! empty( $nav_menu ) ) {
			foreach ( $nav_menu as $k ) {
				$menu_ids[ $k->term_id ] = $k->name;
			}
		}

		return $menu_ids;
	}

	/**
	 * General
	 */
	public function general() {
		$this->start_controls_section(
			'general',
			array(
				'label' => __( 'General', 'woostify-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		// Alignment.
		$this->add_control(
			'alignment',
			array(
				'type'    => Controls_Manager::CHOOSE,
				'label'   => esc_html__( 'Alignment', 'woostify-pro' ),
				'options' => array(
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
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Icon
	 */
	public function account_icon() {
		$this->start_controls_section(
			'cart',
			array(
				'label' => __( 'Icon', 'woostify-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'type',
			array(
				'label'   => __( 'Icon', 'woostify-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'theme',
				'options' => array(
					'theme' => __( 'Use Theme Icon', 'woostify-pro' ),
					'icon'  => __( 'Use Custom Icon', 'woostify-pro' ),
					'image' => __( 'Use Image', 'woostify-pro' ),
				),
			)
		);

		$this->add_control(
			'icon',
			array(
				'label'     => __( 'Choose Icon', 'woostify-pro' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-user',
					'library' => 'solid',
				),
				'condition' => array(
					'type' => 'icon',
				),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .my-account-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .custom-svg-icon svg path' => 'fill: {{VALUE}};',
				),
				'condition' => array(
					'type' => array( 'icon', 'theme' ),
				),
				'separator' => 'before',
			)
		);

		$this->add_control(
			'icon_hover_color',
			array(
				'label'     => __( 'Hover Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .my-account-icon:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .my-account-icon:hover svg path' => 'fill: {{VALUE}};',
				),
				'condition' => array(
					'type' => array( 'icon', 'theme' ),
				),
			)
		);

		$this->add_control(
			'icon_size',
			array(
				'label'      => __( 'Size', 'woostify-pro' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'separator'  => 'before',
				'range'      => array(
					'px' => array(
						'min'  => 10,
						'max'  => 200,
						'step' => 1,
					),
					'%'  => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .my-account-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .custom-svg-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'type' => array( 'icon', 'theme' ),
				),
			)
		);

		$this->add_control(
			'image',
			array(
				'label'     => __( 'Choose Image', 'woostify-pro' ),
				'type'      => Controls_Manager::MEDIA,
				'default'   => array(
					'url' => Utils::get_placeholder_image_src(),
				),
				'condition' => array(
					'type' => 'image',
				),
			)
		);

		$this->add_control(
			'icon_url',
			array(
				'label'       => __( 'Custom Url', 'woostify-pro' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => __( 'https://your-site.url/', 'woostify-pro' ),
				'default'     => '',
			)
		);

		// Padding.
		$this->add_responsive_control(
			'padding',
			array(
				'type'       => Controls_Manager::DIMENSIONS,
				'label'      => esc_html__( 'Padding', 'woostify-pro' ),
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array(
					'{{WRAPPER}} .my-account-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Submenu
	 */
	public function submenu() {
		$this->start_controls_section(
			'menu',
			array(
				'label' => __( 'Menu', 'woostify-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'menu_id',
			array(
				'label'   => __( 'Select Menu', 'woostify-pro' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => $this->get_nav_list(),
			)
		);

		$this->add_control(
			'logout',
			array(
				'label'        => __( 'Logout Url', 'woostify-pro' ),
				'description'  => __( 'Only display for logged-in users.', 'woostify-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'none',
				'label_on'     => __( 'Yes', 'woostify-pro' ),
				'label_off'    => __( 'No', 'woostify-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array(
					'menu_id!' => 'none',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Controls
	 */
	protected function _register_controls() { // phpcs:ignore
		$this->general();
		$this->account_icon();
		$this->submenu();
	}

	/**
	 * Render
	 */
	public function render() {
		$settings = $this->get_settings_for_display();
		$icon     = ( 'theme' === $settings['type'] ) ? apply_filters( 'woostify_header_my_account_icon', 'ti-user' ) : '';
		if ( 'icon' === $settings['type'] && ! empty( $settings['icon']['value'] ) ) {
			if ( is_array( $settings['icon']['value'] ) ) {
				$icon = 'custom-svg-icon';
			} else {
				$icon = $settings['icon']['value'];
			}
		}
		$icon_url        = '' !== trim( $settings['icon_url'] ) ? trim( $settings['icon_url'] ) : '#';
		$alignment       = $settings['alignment'] ? 'alignment-' . $settings['alignment'] : '';
		$page_account_id = get_option( 'woocommerce_myaccount_page_id' );
		$logout_url      = wp_logout_url( apply_filters( 'woostify_logout_redirect', get_permalink( $page_account_id ) ) );

		?>

		<div class="woostify-account-icon-widget my-account <?php echo esc_attr( $alignment ); ?>">
			<a class="my-account-icon <?php echo esc_attr( $icon ); ?>" href="<?php echo esc_url( $icon_url ); ?>">
				<?php

				if ( 'icon' === $settings['type'] && ! empty( $settings['icon']['value'] ) ) {
					if ( is_array( $settings['icon']['value'] ) ) {
						Icons_Manager::render_icon( $settings['icon'] );
					}
				} elseif ( 'image' === $settings['type'] ) {
					$img_id  = 'image' === $settings['type'] ? $settings['image']['id'] : $settings['icon']['value']['id'];
					$img_url = 'image' === $settings['type'] ? $settings['image']['url'] : $settings['icon']['value']['url'];
					$img_alt = woostify_image_alt( $img_id, __( 'Account Icon', 'woostify-pro' ) );
					?>
						<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>">
					<?php
				}

				?>

			</a>
			<?php if ( 'none' !== $settings['menu_id'] ) { ?>
				<div class="subbox">
					<?php
					$args = array(
						'menu'           => $settings['menu_id'],
						'container'      => '',
						'theme_location' => '__faker',
					);

					wp_nav_menu( $args );

					if ( 'yes' === $settings['logout'] && is_user_logged_in() ) {
						?>
						<li>
							<a href="<?php echo esc_url( $logout_url ); ?>"><?php esc_html_e( 'Logout', 'woostify-pro' ); ?></a>
						</li>
						<?php
					}
					?>
				</div>
			<?php } ?>
		</div>
		<?php
	}
}
Plugin::instance()->widgets_manager->register_widget_type( new Woostify_Elementor_Account_Icon_Widget() );
