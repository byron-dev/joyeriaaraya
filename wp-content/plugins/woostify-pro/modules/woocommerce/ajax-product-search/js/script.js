/**
 * Ajax product search
 *
 * @package Woostify Pro
 */

/* global woostify_ajax_product_search_data */

'use strict';

// Set delay time when user typing.
var woostifySearchDelay = function() {
	var timer = ( arguments.length > 0 && undefined !== arguments[0] ) ? arguments[0] : 0;

	return function( callback, ms ) {
		clearTimeout( timer );
		timer = setTimeout( callback, ms );
	};
}();

// Ajax product search.
var woostifyAjaxProductSearch = function() {
	var selector  = document.querySelectorAll( '.search-field' ),
		htmlClass = {
			link: 'aps-link',
			image: 'aps-thumbnail',
			itemTag: '<div class="aps-item">',
			contentTag: '<div class="asp-content">',
			priceTag: '<div class="aps-price">',
			titleTag: '<h4 class="aps-title">',
			skuTag: '<div class="aps-sku">',
			closeTag: '</div>',
			titleClose: '</h4>',
	},
	resultSuccess = {
		dataHtml: function( data, resultsHtml ) {
			console.log( data );
			var html        = '',
				htmlTag     = this,
				counterHtml = jQuery( resultsHtml ).find( '.search-dialog-count' ),
				products    = data.products,
				i,j;
			jQuery( '.link-search-page' ).html( data.product_found + ' ' + data.product );

			if ( data.products.length > 0 ) {
				if ( counterHtml.length > 0 ) {
					counterHtml.html( data.product_found + ' ' + data.product );
				} else {
					var title = '<span class="search-dialog-count aps-highlight">' + data.product_found + ' products</span>';
					jQuery( '.dialog-search-title' ).append( title );
				}

				for ( i = 0, j = products.length; i < j; i++ ) {
					var item = products[i],
						sku  = '';
					if ( item.sku_hightline ) {
						sku = 'SKU: ';
					}
					html += htmlTag.itemTag +
						'<a class="' + htmlTag.link + '" href="' + item.url + '"></a>' +
						'<img class="' + htmlTag.image + '" src="' + item.image + '" alt="' + item.name + '">' +
						htmlTag.contentTag +
							htmlTag.titleTag + item.name_hightline + htmlTag.titleClose +
							htmlTag.priceTag + item.html_price + htmlTag.closeTag +
							htmlTag.skuTag + sku + item.sku_hightline + htmlTag.closeTag +
						htmlTag.closeTag +
					htmlTag.closeTag;
				}

				resultsHtml.querySelector( '.ajax-search-results' ).innerHTML = html;
			} else {
				resultsHtml.querySelector( '.ajax-search-results' ).innerHTML = '<div class="aps-no-posts-found">' + data.not_found + '</div>';
				counterHtml.html( '' );
			}
		}
	};
	if ( ! selector.length || 'undefined' === typeof( woostify_ajax_product_search_data ) ) {
		return;
	}

	selector.forEach(
		function( element ) {
			var form            = element.closest( 'form' ),
				isProductSearch = form ? form.classList.contains( 'woocommerce-product-search' ) : false,
				search          = form ? form.querySelector( '.search-field' ) : false;

			if ( ! form || ! isProductSearch || ! search ) {
				return;
			}

			search.setAttribute( 'autocomplete', 'off' );

			var parent       = form.closest( '.site-search' ) || form.closest( '.dialog-search-content' ),
				results      = parent ? parent.querySelector( '.ajax-search-results' ) : false;

			if ( ! results ) {
				return;
			}

			// Add clear text button.
			var clearText = document.createElement( 'span' );
			clearText.setAttribute( 'class', 'clear-search-results ti-close' );
			search.parentNode.insertBefore( clearText, search.nextSibling );

			// Append select markup html.
			if ( woostify_ajax_product_search_data.select ) {
				form.insertAdjacentHTML( 'afterbegin', woostify_ajax_product_search_data.select );
				form.classList.add( 'category-filter' );
			}

			// Category selector.
			var category = form.querySelector( '.ajax-product-search-category-filter' ),
				language = form.querySelector( 'input[name=lang]' );

			// Fetch.
			var fetchApi = function() {
				var categoryFilter = undefined !== arguments[0] ? arguments[0] : false,
					catId          = category ? category.value.trim() : '',
					lang           = language ? language.value.trim() : '';

				// Return if search field is empty.
				var keyword = search.value.trim();
				if ( ! keyword ) {
					clearText.classList.remove( 'show' );
					results.classList.add( 'hide' );
					jQuery( parent ).find( '.total-result-wrapper' ).html();
					jQuery( parent ).find( '.search-dialog-count' ).html();
					return;
				}

				// Clear text and hide search results.
				clearText.classList.add( 'show' );
				results.classList.remove( 'hide' );

				clearText.onclick = function() {
					search.value = '';
					clearText.classList.remove( 'show' );
					results.classList.add( 'hide' );
					jQuery( parent ).find( '.total-result-wrapper' ).html();
					jQuery( parent ).find( '.search-dialog-count' ).html();
				}

				// If current value === Prev value AND NOT category filter, return.
				var prevValue = search.getAttribute( 'data-value' );
				if ( keyword === prevValue && ! categoryFilter ) {
					return;
				}

				// Set Current search value.
				search.setAttribute( 'data-value', keyword );

				// Add class 'loading' to View.
				form.classList.add( 'loading' );
				var url;
				url = jQuery( form ).attr( 'action' ) + '?post_type=product&s=' + keyword;

				if ( catId ) {
					url += '&cat_id=' + catId;
				}
				if ( lang ) {
					url += '&lang=' + lang;
				}
				// Redirect search page.
				jQuery( document ).on(
					'keyup',
					function(e) {
						if ( e.keyCode === 13 ) {
							window.location.href = url;
						}
					}
				);
				var data = {
					ajax_nonce: woostify_ajax_product_search_data.ajax_nonce,
					keyword: keyword,
					cat_id: catId,
					lang: lang
				};
				jQuery.ajax(
					{
						type: 'GET',
						url: woostify_ajax_product_search_data.url,
						data: data,
						beforeSend: function ( response ) {
							jQuery( form ).addClass( 'loading' );
						},
						success: function ( response ) {
							jQuery( form ).removeClass( 'loading' );
							jQuery( parent ).find( '.total-result-wrapper' ).html();
							if ( response.data.products.length > 0 ) {
								jQuery( parent ).find( '.total-result-wrapper' ).html( '<a href="' + url + '" class="link-search-page aps-highlight">' + response.data.size + ' products</a>' );
							}
							resultSuccess.dataHtml.call( htmlClass, response.data, parent );
						},
					}
				);
			}

			// When user typing...
			search.addEventListener(
				'input',
				function() {
					woostifySearchDelay(
						function() {
							fetchApi();
						},
						500
					);
				}
			);

			// When user update select field.
			if ( category ) {
				category.addEventListener(
					'change',
					function() {
						fetchApi( true );
						search.focus();
					}
				);
			}

		}
	);
}

document.addEventListener(
	'DOMContentLoaded',
	function() {
		woostifyAjaxProductSearch();
	}
);
