<?php
/**
 * Archives: content types, categories, tags, dates.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

get_header();

$default = array(
	'eyebrow' => is_post_type_archive() ? coregas_setting( 'company_name' ) : '',
	'title'   => wp_strip_all_tags( post_type_archive_title( '', false ) ? post_type_archive_title( '', false ) : get_the_archive_title() ),
	'text'    => wp_strip_all_tags( get_the_archive_description() ),
);
$context = is_post_type_archive() ? (string) get_query_var( 'post_type' ) : 'news';
coregas_banner( coregas_archive_banner( $context, $default ) ); // editable in Theme Settings → Pages & wording.

get_template_part( 'template-parts/listing' );

get_footer();
