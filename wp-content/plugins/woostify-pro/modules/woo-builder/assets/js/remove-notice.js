/**
 * Remove notice
 *
 * @package woostify
 */

'use strict';

document.addEventListener(
	'DOMContentLoaded',
	function() {
		let noticeWrap = document.querySelectorAll( '.woocommerce-notices-wrapper' );
		if ( ! noticeWrap.length ) {
			return;
		}

		noticeWrap.forEach(
			function( notice ) {
				if ( ! notice.parentNode.classList.contains( 'elementor-widget-container' ) ) {
					notice.remove();
				}
			}
		);
	}
);
