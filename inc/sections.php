<?php
/**
 * Page Sections: query and render the sections assigned to a page.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Published sections for a page, in order.
 *
 * @param int $page_id Page ID.
 * @return WP_Post[]
 */
function coregas_get_sections( $page_id ) {
	static $cache = array();
	$page_id = (int) $page_id;
	if ( ! $page_id ) {
		return array();
	}
	if ( ! isset( $cache[ $page_id ] ) ) {
		$cache[ $page_id ] = get_posts(
			array(
				'post_type'        => 'cg_section',
				'post_status'      => 'publish',
				'posts_per_page'   => 50,
				'orderby'          => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
				'meta_key'         => COREGAS_META_PREFIX . 'page', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'       => (string) $page_id, // phpcs:ignore WordPress.DB.SlowDBQuery
				'suppress_filters' => false,
			)
		);
	}
	return $cache[ $page_id ];
}

/**
 * Render all sections for a page.
 *
 * @param int $page_id Page ID.
 * @return bool Whether anything was rendered.
 */
function coregas_render_sections( $page_id ) {
	$sections = coregas_get_sections( $page_id );
	foreach ( $sections as $section ) {
		$type = coregas_meta( $section->ID, 'section_type' );
		if ( ! in_array( $type, array( 'hero', 'about', 'text', 'cards', 'cta', 'split', 'steps', 'columns', 'gallery', 'faq', 'contact', 'embed' ), true ) ) {
			continue;
		}
		get_template_part( 'template-parts/sections/' . $type, null, array( 'id' => $section->ID, 'post' => $section ) );
	}
	return (bool) $sections;
}

/**
 * id="" attribute for a section (custom anchor or a stable fallback).
 *
 * @param int    $id       Section ID.
 * @param string $fallback Fallback anchor.
 * @return string
 */
function coregas_section_anchor( $id, $fallback = '' ) {
	$anchor = sanitize_title( (string) coregas_meta( $id, 'anchor' ) );
	if ( ! $anchor ) {
		$anchor = $fallback ? $fallback : 'section-' . $id;
	}
	return ' id="' . esc_attr( $anchor ) . '"';
}

/**
 * Shared heading block for text / cards / cta sections.
 *
 * @param int  $id     Section ID.
 * @param bool $center Center aligned.
 */
function coregas_section_head( $id, $center = false ) {
	$eyebrow = coregas_meta( $id, 'sec_eyebrow' );
	$heading = coregas_meta( $id, 'sec_heading' );
	$intro   = coregas_meta( $id, 'sec_intro' );
	if ( ! $eyebrow && ! $heading && ! $intro ) {
		return;
	}
	echo '<header class="sec__head' . ( $center ? ' sec__head--center' : '' ) . '">';
	if ( $eyebrow ) {
		echo '<p class="mono label"><span class="dot"></span>' . esc_html( $eyebrow ) . '</p>';
	}
	if ( $heading ) {
		echo '<h2>' . esc_html( $heading ) . '</h2>';
	}
	if ( $intro ) {
		echo '<div class="sec__intro">' . coregas_paragraphs( $intro ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper.
	}
	echo '</header>';
}

/**
 * Buttons repeater under a section.
 *
 * @param int    $id    Section ID.
 * @param string $style Pill style.
 */
function coregas_section_links( $id, $style = 'maroon' ) {
	$links = coregas_meta( $id, 'sec_links' );
	if ( ! is_array( $links ) || ! $links ) {
		return;
	}
	echo '<div class="sec__links">';
	foreach ( $links as $l ) {
		echo coregas_button( isset( $l['label'] ) ? $l['label'] : '', isset( $l['url'] ) ? $l['url'] : '', $style ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper.
	}
	echo '</div>';
}
