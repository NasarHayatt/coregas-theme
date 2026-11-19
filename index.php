<?php
/**
 * Fallback template and the news (posts) listing.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

get_header();

$posts_page = (int) get_option( 'page_for_posts' );
coregas_banner(
	coregas_archive_banner(
		'news',
		array(
			'eyebrow' => coregas_setting( 'company_name' ),
			'title'   => $posts_page ? get_the_title( $posts_page ) : __( 'News', 'coregas' ),
			'text'    => '',
		)
	)
);

get_template_part( 'template-parts/listing' );

get_footer();
