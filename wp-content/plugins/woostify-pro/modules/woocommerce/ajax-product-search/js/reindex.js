/**
 * Index search
 *
 * @package Woostify
 */

'use strict';

( function ( $ ) {

	'use strict';

	// Show Auto Complete display.
	$( 'body' ).on(
		'click',
		'.btn-index-data',
		function( e ) {
			var btn = $( this );
			btn.prop( 'disabled', true );
			var data = {
				action: 'index_data',
				_ajax_nonce: admin.nonce,
			};

			$.ajax(
				{
					type: 'GET',
					url: admin.url,
					data: data,
					beforeSend: function ( response ) {
						$( '.index-data .progress' ).addClass( 'loading' );
						btn.text( "Start Index Data..." );
					},
					success: function ( response ) {
						btn.prop( 'disabled', false );
						$( '.index-data .progress' ).removeClass( 'loading' );
						btn.text( "Index Data Success" );
						if ( response.data ) {
							$( '.last-index' ).text( response.data.time );
							$( '.index-total-product' ).text( response.data.total_product );
						}
					},
				}
			);
		}
	);

	$('.btn-close-notice').on(
		'click',
		function (e) {
			$( this ).parents( '.woostify-notice' ).slideUp();
		}
	);

} )( jQuery );
