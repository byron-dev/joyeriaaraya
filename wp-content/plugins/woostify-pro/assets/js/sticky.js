var $ = jQuery.noConflict();

$( window ).on(
	'load',
	function() {
		"use strict";
		woostifySectionSticky();

	}
);

function woostifySectionSticky() {
	var $sticky = $( '.woostify-sticky-yes' );
	if ( $sticky.length !== 0 ) {
		$sticky.each(
			function ( index ) {
				var sticky   = $( this );
				var copyElem = sticky.siblings( '.elementor-section' );
				if ( copyElem.hasClass( 'elementor-sticky--active' ) || ( copyElem.hasClass( 'elementor-sticky--active' ) && copyElem.hasClass( 'she-header-yes' ) ) ) {
					return;
				}
				var bodyTop      = $( 'body' ).offset().top;
				var top          = sticky.offset().top;
				var left         = sticky.offset().left;
				var width        = sticky.width();
				var height       = sticky.height();
				var widthScreen  = $( window ).width();
				var data         = sticky.attr( 'data-settings' );
				data             = JSON.parse( data );
				var screen       = data.woostify_on;
				var wrap         = sticky.parents( '.elementor-section-wrap' );
				var element      = sticky.html();
				var classSticky  = sticky.attr( 'class' );
				var margin       = data.woostify_distance;
				var tabletMargin = data.woostify_distance_tablet;
				var mobileMargin = data.woostify_distance_mobile;
				var logo         = sticky.find( '.custom-logo' );
				var logoUrl      = logo.attr( 'src' );
				var logoStick    = data.woostify_logo.url;
				var menuColor    = data.woostify_menu_color;
				var transparent  = sticky.hasClass( 'woostify-header-transparent-yes' );
				var enabled      = 'desktop';
				var stickyOffset = top;
				if ( widthScreen >= 1025 ) {
					enabled      = 'desktop';
					stickyOffset = margin.size + top;
				} else if ( widthScreen > 767 && widthScreen < 1025 ) {
					enabled      = 'tablet';
					stickyOffset = tabletMargin.size + top;
				} else if ( widthScreen <= 767 ) {
					enabled      = "mobile";
					stickyOffset = mobileMargin.size + top;
				}

				if ( screen.includes( enabled ) ) {
					if ( ! transparent ) {
						var coppyClass = 'woostify-header--default elementor-section';
						if ( sticky.hasClass( 'elementor-section-boxed' ) ) {
							coppyClass += ' elementor-section-boxed';
						}
						if ( sticky.hasClass( 'elementor-hidden-phone' ) ) {
							coppyClass += ' elementor-hidden-phone';
						}

						if ( sticky.hasClass( 'elementor-hidden-tablet' ) ) {
							coppyClass += ' elementor-hidden-tablet';
						}

						if ( sticky.hasClass( 'elementor-hidden-desktop' ) ) {
							coppyClass += ' elementor-hidden-desktop';
						}

						sticky.after(
							'<section class="' + coppyClass + '">' +
							element +
							'</section>'
						);

						var copy = sticky.siblings( '.woostify-header--default' );

						copy.css(
							{
								'visibility' : 'hidden',
								'transition' : 'none 0s ease 0s',
							}
						);
					}

					sticky.css(
						{
							'top' : top + 'px',
							'position' : 'fixed',
							'left' : left + 'px',
							'width' : width + 'px',
						}
					);
					if ( $( window ).scrollTop() > stickyOffset ) {
						sticky.css( {'top' : bodyTop + 'px' } );
						if ( menuColor && menuColor !== '' ) {
							sticky.find( '.woostify-menu > li > a' ).css( { 'color' : menuColor } );
						}
						if ( logoStick !== '' ) {
							logo.attr( 'src', logoStick );
						} else {
							logo.attr( 'src', logoUrl );
						}
						sticky.addClass( 'woostify-sticky--active' );
						sticky.css( {'background-color' : data.woostify_background } );
					} else {
						var defaultTop = sticky.offset().top;

						sticky.css( 'top', defaultTop + 'px' );
						if ( ( stickyOffset - $( window ).scrollTop() ) > 0 ) {
							sticky.css( {'top': ( top - $( window ).scrollTop() ) + 'px'} );
						}
					}

					$( window ).scroll(
						function() {
							var scroll = $( window ).scrollTop();
							if ( ( stickyOffset - scroll ) >= 0 ) {
								sticky.css( {'top': ( top - scroll ) + 'px'} );

								sticky.removeClass( 'woostify-sticky--active' );
								sticky.css( { "background-color" : '' } );
								logo.attr( 'src', logoUrl );
								sticky.find( '.woostify-nav-menu-widget > .woostify-nav-menu-inner > nav > ul > li > a' ).css( { 'color' : '' } );

							} else {
								sticky.css( {'top' : bodyTop + 'px' } );

								sticky.addClass( 'woostify-sticky--active' );
								sticky.css( {'background-color' : data.woostify_background } );
								if ( menuColor && menuColor !== '' ) {
									sticky.find( '.woostify-nav-menu-widget > .woostify-nav-menu-inner > nav > ul > li > a' ).css( { 'color' : menuColor } );
								}

								if ( logoStick !== '' ) {
									logo.attr( 'src', logoStick );
									logo.attr( 'srcset', '' );
								} else {
									logo.attr( 'src', logoUrl );
								}
							}
						}
					);
				}

				$( window ).resize(
					function() {
						width = $( window ).width();
						sticky.css( { 'width' : width + 'px' } );
						var bodyTop = $( 'body' ).offset().top;
						sticky.css( { 'top' : bodyTop + 'px' } );

						if ( width >= 1025 ) {
							stickyOffset = margin.size + top;
						} else if ( width > 767 && width < 1025 ) {
							stickyOffset = tabletMargin.size + top;
						} else if ( width <= 767 ) {
							stickyOffset = mobileMargin.size + top;
						}

						if ( $( window ).scrollTop() > stickyOffset ) {
							sticky.css( {'top' : bodyTop + 'px' } );
						} else {
							if ( ( stickyOffset - $( window ).scrollTop() ) > 0 ) {
								sticky.css( {'top': ( top - $( window ).scrollTop() ) + 'px'} );
								/*sticky.css({'top': 0 + 'px'});*/
							}
						}
					}
				);
			}
		);
	}

}
