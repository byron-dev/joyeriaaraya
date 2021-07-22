<?php
/**
 * Elementor Product Form Review Widget
 *
 * @package Woostify Pro
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class widget.
 */
class Woostify_Product_Form_Review extends Widget_Base {
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
		return 'woostify-product-form-review';
	}

	/**
	 * Gets the title.
	 */
	public function get_title() {
		return __( 'Woostify - Product Form Review', 'woostify-pro' );
	}

	/**
	 * Gets the icon.
	 */
	public function get_icon() {
		return 'eicon-review';
	}

	/**
	 * Gets the keywords.
	 */
	public function get_keywords() {
		return array( 'woostify', 'woocommerce', 'shop', 'product', 'tabs', 'store' );
	}

	/**
	 * Controls
	 */
	protected function register_controls() {
		$this->general();
	}

	/**
	 * General
	 */
	protected function general() {
		$this->start_controls_section(
			'start',
			array(
				'label' => __( 'General', 'woostify-pro' ),
			)
		);

		$this->add_control(
			'button_submit',
			array(
				'label' => __( 'Button', 'woostify-pro' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		// TAB START.
		$this->start_controls_tabs( 'submit_button_tabs' );

		// Normal.
		$this->start_controls_tab(
			'submit_button_normal',
			array(
				'label' => __( 'Normal', 'woostify-pro' ),
			)
		);

		// Color.
		$this->add_control(
			'submit_button_text_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-product-form-review .submit' => 'color: {{VALUE}};',
				),
			)
		);

		// BG color.
		$this->add_control(
			'submit_button_bg_color',
			array(
				'label'     => __( 'Background', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-product-form-review .submit' => 'background-color: {{VALUE}};',
				),
			)
		);

		// END NORMAL.
		$this->end_controls_tab();

		// HOVER.
		$this->start_controls_tab(
			'submit_button_hover',
			array(
				'label' => __( 'Hover', 'woostify-pro' ),
			)
		);

		// Hover color.
		$this->add_control(
			'submit_button_hover_text_color',
			array(
				'label'     => __( 'Color', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-product-form-review .submit:hover' => 'color: {{VALUE}};',
				),
			)
		);

		// Hover BG color.
		$this->add_control(
			'submit_button_hover_bg_color',
			array(
				'label'     => __( 'Background', 'woostify-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .woostify-product-form-review .submit:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		// TAB END.
		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Render
	 */
	protected function render() {
		global $product;
		if ( woostify_is_elementor_editor() ) {
			$product_id = \Woostify_Woo_Builder::init()->get_product_id();
			$product    = wc_get_product( $product_id );
		}

		if ( empty( $product ) ) {
			return;
		}

		$product_id = $product->get_id();
		?>
		<div class="woostify-product-form-review">
			<div id="reviews" class="woocommerce-Reviews">
				<div id="comments">
					<?php
					$comments = get_comments(
						array(
							'post_id' => $product_id,
						)
					);

					if ( $comments ) {
						?>
						<ol class="commentlist">
						<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ), $comments ); ?>
						</ol>
						<?php
						if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
							echo '<nav class="woocommerce-pagination">';
							paginate_comments_links(
								apply_filters(
									'woocommerce_comment_pagination_args',
									array(
										'prev_text' => '&larr;',
										'next_text' => '&rarr;',
										'type'      => 'list',
									)
								)
							);
							echo '</nav>';
						endif;
						?>
						<?php
					} else {
						?>
							<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
						<?php
					}

					?>

				</div>

				<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
					<div id="review_form_wrapper">
						<div id="review_form">
							<?php
							$commenter    = wp_get_current_commenter();
							$comment_form = array(
								/* translators: %s is product title */
								'title_reply'         => '',
								/* translators: %s is product title */
								'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'woocommerce' ),
								'title_reply_before'  => '<span id="reply-title" class="comment-reply-title">',
								'title_reply_after'   => '</span>',
								'comment_notes_after' => '',
								'label_submit'        => esc_html__( 'Submit', 'woocommerce' ),
								'logged_in_as'        => '',
								'comment_field'       => '',
							);

							$name_email_required = (bool) get_option( 'require_name_email', 1 );
							$fields              = array(
								'author' => array(
									'label'    => __( 'Name', 'woocommerce' ),
									'type'     => 'text',
									'value'    => $commenter['comment_author'],
									'required' => $name_email_required,
								),
								'email'  => array(
									'label'    => __( 'Email', 'woocommerce' ),
									'type'     => 'email',
									'value'    => $commenter['comment_author_email'],
									'required' => $name_email_required,
								),
							);

							$comment_form['fields'] = array();

							foreach ( $fields as $key => $field ) {
								$field_html  = '<p class="comment-form-' . esc_attr( $key ) . '">';
								$field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );

								if ( $field['required'] ) {
									$field_html .= '&nbsp;<span class="required">*</span>';
								}

								$field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></p>';

								$comment_form['fields'][ $key ] = $field_html;
							}

							$account_page_url = wc_get_page_permalink( 'myaccount' );
							if ( $account_page_url ) {
								/* translators: %s opening and closing link tags respectively */
								$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'woocommerce' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
							}

							$comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Your review', 'woocommerce' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>';

							comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ), $product_id );
							?>
						</div>
					</div>
				<?php else : ?>
					<p class="woocommerce-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce' ); ?></p>
				<?php endif; ?>

				<div class="clear"></div>
			</div>
		<?php
		wp_reset_postdata();
		// On render widget from Editor - trigger the init manually.
		if ( woostify_is_elementor_editor() ) {
			?>
			<script>
				jQuery( '#rating' ).trigger( 'init' );
			</script>
			<?php
		}
	}
}
Plugin::instance()->widgets_manager->register_widget_type( new Woostify_Product_Form_Review() );
