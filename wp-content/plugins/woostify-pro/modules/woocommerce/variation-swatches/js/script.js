/**
 * Variation Swatches
 *
 * @package Woostify Pro
 */

/* global woostify_variation_swatches_admin */

'use strict';

// Check variation is available.
var woostifyAvailableVariations = function( target ) {
	var selector = target.closest( '#woostify-quick-view-panel' ) ? document.getElementById( 'woostify-quick-view-panel' ) : document.getElementById( 'view' );
	if ( ! selector ) {
		return;
	}

	var availableSelect = selector.querySelectorAll( '.variations [name^="attribute_"] option' );
	if ( availableSelect.length ) {
		var availableValue = [];

		availableSelect.forEach(
			function( as ) {
				var selectValue = as.getAttribute( 'value' );
				if ( ! selectValue || as.disabled ) {
					return;
				}

				availableValue.push( selectValue );
			}
		);

		var availableSwatch = selector.querySelectorAll( '.variations .swatch' );
		if ( availableSwatch.length ) {
			availableSwatch.forEach(
				function( awv ) {
					var swatchValue = awv.getAttribute( 'data-value' );
					if ( availableValue.includes( swatchValue ) ) {
						awv.classList.remove( 'unavailable' );
					} else {
						awv.classList.add( 'unavailable' );
					}
				}
			);
		}
	}
}

// Variation swatches.
var woostifyVariationSwatches = function() {
	var form = document.querySelectorAll( 'form.variations_form' );
	if ( ! form.length ) {
		return;
	}

	for ( var i = 0, j = form.length; i < j; i ++ ) {
		var element = form[i],
			swatch  = element.querySelectorAll( '.swatch' );

		if ( ! swatch.length ) {
			return;
		}

		var selected   = [],
			change     = new Event( 'change', { bubbles: true } ),
			noMatching = new Event( 'woostify_no_matching_variations' );

		swatch.forEach(
			function( el ) {
				el.onclick = function( e ) {
					e.preventDefault();

					if ( el.classList.contains( 'unavailable' ) ) {
						return;
					}

					var variations = el.closest( '.variations' ),
						parent     = el.closest( '.value' ),
						allSelect  = variations.querySelectorAll( 'select' ),
						select     = parent.querySelector( 'select' ),
						attribute  = select.getAttribute( 'data-attribute_name' ) || select.getAttribute( 'name' ),
						value      = el.getAttribute( 'data-value' ),
						combi      = select ? select.querySelectorAll( 'option[value="' + value + '"]' ) : [],
						sibs       = siblings( el );

					// Check if this combination is available.
					if ( ! combi.length ) {
						element.dispatchEvent( noMatching, el );

						return;
					}

					if ( -1 === selected.indexOf( attribute ) ) {
						selected.push( attribute );
					}

					// Highlight swatch.
					if ( el.classList.contains( 'selected' ) ) {
						select.value = '';
						el.classList.remove( 'selected' );

						delete selected[ selected.indexOf( attribute ) ];
					} else {
						el.classList.add( 'selected' );

						if ( sibs.length ) {
							sibs.forEach(
								function( sb ) {
									sb.classList.remove( 'selected' );
								}
							);
						}

						select.value = value;
					}

					// Trigger 'change' event.
					select.dispatchEvent( change );
				}
			}
		);

		// Reset variations.
		var reset = element.querySelector( '.reset_variations' );
		if ( reset ) {
			reset.addEventListener(
				'click',
				function() {
					var resetSwatches = element.querySelectorAll( '.swatch' );
					if ( resetSwatches.length ) {
						resetSwatches.forEach(
							function( rs ) {
								// Remove all 'unavailable', 'selected' class.
								rs.classList.remove( 'unavailable', 'selected' );
							}
						);
					}

					// Reset selected.
					selected = [];
				}
			);
		}

		// Warning if no matching variations.
		element.addEventListener(
			'woostify_no_matching_variations',
			function() {
				window.alert( wc_add_to_cart_variation_params.i18n_no_matching_variations_text );
			}
		);
	}
}

// Swatch list.
var woostifySwatchList = function() {
	var list = document.querySelectorAll( '.swatch-list' );
	if ( ! list.length ) {
		return;
	}

	list.forEach(
		function( element ) {
			var parent    = element.closest( '.product' ),
				imageWrap = parent.querySelector( '.product-loop-image-wrapper' ),
				image     = parent.querySelector( '.product-loop-image' ),
				items     = element.querySelectorAll( '.swatch' );

			if ( ! items.length ) {
				return;
			}

			items.forEach(
				function( item ) {
					var sib = siblings( item ),
						src = item.getAttribute( 'data-slug' );

					// Set selected swatch.
					if ( item.classList.contains( 'selected' ) ) {
						image.setAttribute( 'srcset', '' );
						image.src = src;
					}

					item.onclick = function() {
						if ( ! image.getAttribute( 'data-swatch' ) ) {
							image.setAttribute( 'data-swatch', image.src );
						}

						imageWrap.classList.add( 'circle-loading' );

						// Remove srcset attribute.
						image.setAttribute( 'srcset', '' );

						// For siblings.
						if ( sib.length ) {
							sib.forEach(
								function( el ) {
									el.classList.remove( 'selected' );
								}
							);
						}

						// Highlight.
						if ( item.classList.contains( 'selected' ) ) {
							item.classList.remove( 'selected' );
							image.src = image.getAttribute( 'data-swatch' );
						} else {
							item.classList.add( 'selected' );
							image.src = src;
						}

						// Image loading.
						var img = new Image();
						img.src = src;

						img.onload = function() {
							imageWrap.classList.remove( 'circle-loading' );
						};
					}
				}
			);
		}
	);
}

document.addEventListener(
	'DOMContentLoaded',
	function() {
		woostifyVariationSwatches();
		woostifySwatchList();

		jQuery( document.body ).on(
			'check_variations',
			function( e ) {
				woostifyAvailableVariations( e.target );
			}
		);
	}
);
