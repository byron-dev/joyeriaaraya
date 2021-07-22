/**
 * Featured product
 *
 * @package Woostify Pro
 */

'use strict';

// Run scripts only elementor loaded.
function onWidgetElementorLoaded( callback ) {
	if ( undefined === window.elementorFrontend || undefined === window.elementorFrontend.hooks ) {
		setTimeout(
			function() {
				onWidgetElementorLoaded( callback )
			}
		);

		return;
	}

	callback();
}

function woostifyFeaturedProduct( $scope ) {
	var featured = 'undefined' === typeof( $scope ) ? jQuery( '.adv-featured-product' ) : $scope.find( '.adv-featured-product' );
	if ( ! featured.length || ! jQuery().slick ) {
		return;
	}

	featured.each(
		function() {
			var t         = jQuery( this ),
				perRow    = t.data( 'items' ),
				arrows    = t.parent().find( '.adv-featured-product-arrow' ),
				arrowPrev = arrows.find( '.prev-arrow' ),
				arrowNext = arrows.find( '.next-arrow' ),
				options   = {
					rows: 1,
					slidesPerRow: perRow,
					prevArrow: arrowPrev,
					nextArrow: arrowNext,
					adaptiveHeight: true
			}

			t.slick( options );
			t.slick( 'resize' );
		}
	);
}

document.addEventListener(
	'DOMContentLoaded',
	function() {
		woostifyFeaturedProduct();

		// For Elementor Preview Mode.
		if ( 'function' === typeof( onWidgetElementorLoaded ) ) {
			onWidgetElementorLoaded(
				function() {
					window.elementorFrontend.hooks.addAction(
						'frontend/element_ready/wp-widget-advanced-featured-product.default',
						function( $scope ) {
							woostifyFeaturedProduct( $scope );
						}
					);
				}
			);
		}
	}
);
