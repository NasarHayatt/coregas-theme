<?php
/**
 * Home page: renders the Page Sections assigned to it.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_id = get_queried_object_id();
if ( ! coregas_render_sections( $page_id ) ) {
	// No sections yet: behave like a normal page so the site never shows a blank home.
	while ( have_posts() ) {
		the_post();
		coregas_banner( array( 'title' => get_the_title(), 'fallback' => coregas_current_banner_placeholder() ) );
		echo '<section class="sec sec--light"><div class="sec__inner prose">';
		the_content();
		echo '</div></section>';
	}
}

get_footer();
