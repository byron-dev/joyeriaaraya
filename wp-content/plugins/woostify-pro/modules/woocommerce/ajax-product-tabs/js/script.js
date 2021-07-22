/**
 * Product Tab
 *
 * @package Woostify Pro
 */

'use strict';

// Init slider for first tab.
var woostifyInitSliderFirstTab = function() {
	var element = document.querySelectorAll( '.woostify-products-tab-widget[data-layout="carousel-layout"]' );
	if ( ! element.length ) {
		return;
	}

	element.forEach(
		function( ele ) {
			var section = ele.querySelector( '.woostify-products-tab-content' );
			if ( ! section ) {
				return;
			}

			var productTotal = ele.querySelectorAll( '.products .product' ),
				products     = section.querySelector( '.products' ),
				arrows       = section.getAttribute( 'data-arrows' ),
				dots         = section.getAttribute( 'data-dots' ),
				tabColumns   = section.getAttribute( 'data-columns' ) || 4,
				colTablet    = section.getAttribute( 'data-columns-tablet' ) || 3,
				colMobile    = section.getAttribute( 'data-columns-mobile' ) || 2,
				arrowsCont   = ele.querySelector( '.woostify-product-tab-arrows-container' );

			if ( productTotal.length > tabColumns ) {
				if ( products && 'function' === typeof( woostifyRemoveClassPrefix ) ) {
					woostifyRemoveClassPrefix( products, 'columns' );
				}

				var options = {
					container: products || false,
					items: colMobile,
					controls: arrows ? true : false,
					controlsContainer: arrowsCont || false,
					nav: dots ? true : false,
					gutter: 0,
					loop: false,
					responsive: {
						600: {
							items: colTablet,
							gutter: 15
						},
						992: {
							items: tabColumns,
							gutter: 30
						}
					}
				};

				var slider = tns( options );
			} else if ( arrowsCont ) {
				arrowsCont.classList.add( 'hidden' );
			}
		}
	);
}

// Product tab widget.
var woostifyProductTab = function() {
	var selector = document.querySelectorAll( '.woostify-products-tab-widget' );
	if ( ! selector.length ) {
		return;
	}

	selector.forEach(
		function( element ) {
			var button = element.querySelectorAll( '.woostify-products-tab-btn' ),
				layout = element.getAttribute( 'data-layout' );

			if ( ! button.length ) {
				return;
			}

			for ( var i = 0, j = button.length; i < j; i++ ) {
				button[i].onclick = function() {
					if ( this.matches( '.ready.active' ) ) {
						return;
					}

					var t          = this,
						sibsButton = siblings( t ),
						tabId      = t.getAttribute( 'data-id' ),
						// Arrows.
						arrowsCont = element.querySelector( '.woostify-product-tab-arrows-container[data-id="' + tabId + '"]' ),
						sibsArrows = arrowsCont ? siblings( arrowsCont ) : [],
						// Tab content.
						tabContent = element.querySelector( '.woostify-products-tab-content[data-id="' + tabId + '"]' ),
						sibsTab    = siblings( tabContent ),
						// Tab attributes.
						tabQuery   = tabContent ? tabContent.getAttribute( 'data-query' ) : [],
						arrows     = tabContent ? tabContent.getAttribute( 'data-arrows' ) : false,
						dots       = tabContent ? tabContent.getAttribute( 'data-dots' ) : false,
						tabColumns = tabContent ? tabContent.getAttribute( 'data-columns' ) : 4,
						colTablet  = tabContent ? tabContent.getAttribute( 'data-columns-tablet' ) : 3,
						colMobile  = tabContent ? tabContent.getAttribute( 'data-columns-mobile' ) : 2,
						processing = function() {
							// Highlight this.
							t.classList.add( 'active' );
							if ( tabContent ) {
								tabContent.classList.add( 'active' );
							}

							if ( arrowsCont ) {
								arrowsCont.classList.add( 'active' );
							}

							// Siblings.
							if ( sibsButton.length ) {
								for ( var x = 0, y = sibsButton.length; x < y; x++ ) {
									sibsButton[x].classList.remove( 'active' );

									if ( sibsTab.length ) {
										sibsTab[x].classList.remove( 'active' );
									}

									if ( sibsArrows.length ) {
										sibsArrows[x].classList.remove( 'active' );
									}
								}
							}
						}

					// Set ready state.
					if ( t.classList.contains( 'ready' ) ) {
						processing();

						return;
					}

					// Animation loading.
					element.classList.add( 'loading' );

					var data = {
						action: 'product_tab',
						ajax_nonce: woostify_ajax_product_tab_data.ajax_nonce,
						tab_id: tabId,
						tab_query: tabQuery,
						tab_columns: tabColumns,
						tab_columns_tablet: colTablet,
						tab_columns_mobile: colMobile
					}

					// Request.
					var request = new Request(
						woostify_ajax_product_tab_data.ajax_url,
						{
							method: 'POST',
							body: new URLSearchParams( data ).toString(),
							credentials: 'same-origin',
							headers: new Headers(
								{
									'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
								}
							)
						}
					);

					// Fetch API.
					fetch( request )
						.then(
							function( res ) {
								if ( 200 !== res.status ) {
									console.log( 'Status Code: ' + res.status );
									return;
								}

								res.json().then(
									function( r ) {
										if ( ! r.success ) {
											return;
										}

										// Append html.
										tabContent.innerHTML = r.data.content;

										// Carousel init.
										if ( 'carousel-layout' == layout ) {
											if ( r.data.count > tabColumns ) {
												// Remove 'columns' prefix class name.
												var products = tabContent.querySelector( '.products' );
												if ( products && 'function' === typeof( woostifyRemoveClassPrefix ) ) {
													woostifyRemoveClassPrefix( products, 'columns' );
												}

												var options = {
													container: products || false,
													items: colMobile,
													controls: arrows ? true : false,
													controlsContainer: arrowsCont || false,
													nav: dots ? true : false,
													gutter: 0,
													loop: false,
													responsive: {
														600: {
															items: colTablet,
															gutter: 15
														},
														992: {
															items: tabColumns,
															gutter: 30
														}
													}
												};

												var slider = tns( options );
											} else if ( arrowsCont ) {
												arrowsCont.classList.add( 'hidden' );
											}
										}

										// Re-init swatch list.
										if ( 'function' === typeof( woostifySwatchList ) ) {
											woostifySwatchList();
										}

										// Re-init quick view.
										if ( 'function' === typeof( woostifyQuickView ) ) {
											woostifyQuickView();
										}

										// Re-init countdown urgency.
										if ( 'function' === typeof( woostifyCountdownUrgency ) ) {
											woostifyCountdownUrgency();
										}
									}
								);
							}
						).finally(
							function() {
								element.classList.remove( 'loading' );

								t.classList.add( 'ready' );
								processing();

								// Remove some attributes.
								tabContent.removeAttribute( 'data-columns' );
								tabContent.removeAttribute( 'data-columns-tablet' );
								tabContent.removeAttribute( 'data-columns-mobile' );
								tabContent.removeAttribute( 'data-query' );
							}
						);
				}
			}
		}
	);
}

// DOM loaded.
document.addEventListener(
	'DOMContentLoaded',
	function() {
		woostifyInitSliderFirstTab();
		woostifyProductTab();
	}
);
