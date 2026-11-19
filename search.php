<?php
/**
 * Search results.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

get_header();

coregas_banner(
	coregas_archive_banner(
		'search',
		array(
			'eyebrow' => coregas_label( 'search_eyebrow' ),
			'title'   => coregas_label( 'search_title', array( 'query' => get_search_query() ) ),
			'text'    => '',
		)
	)
);

get_template_part( 'template-parts/listing' );

get_footer();
