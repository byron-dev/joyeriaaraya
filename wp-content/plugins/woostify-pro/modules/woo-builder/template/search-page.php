<?php
/**
 * My account template
 *
 * @package Woostify Pro
 */

// Header.
require_once WOOSTIFY_PRO_PATH . 'modules/woo-builder/template/header.php';


?>

<div class="woocommerce-seach-page woo-builder-search-page">
	<?php
		do_action( 'woostify_search_page_content' );
	?>
</div>


<?php
// Footer.
require_once WOOSTIFY_PRO_PATH . 'modules/woo-builder/template/footer.php';
