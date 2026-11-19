<?php
/**
 * Theme supports, menus and front-end assets.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'coregas_setup' );
/**
 * Theme supports and menu locations.
 */
function coregas_setup() {
	load_theme_textdomain( 'coregas', COREGAS_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'automatic-feed-links' );
	add_image_size( 'coregas-card', 900, 600, true );
	add_image_size( 'coregas-wide', 1920, 1080, false );

	register_nav_menus(
		array(
			'primary'  => __( 'Primary (header)', 'coregas' ),
			'footer_1' => __( 'Footer column 1', 'coregas' ),
			'footer_2' => __( 'Footer column 2', 'coregas' ),
			'footer_3' => __( 'Footer column 3', 'coregas' ),
			'footer_4' => __( 'Footer column 4', 'coregas' ),
			'legal'    => __( 'Footer legal row', 'coregas' ),
		)
	);
}

/**
 * Version string for a theme file: its last-modified time, so browsers fetch every change
 * automatically after a deploy (falls back to the theme version).
 *
 * @param string $rel Path inside the theme.
 * @return string
 */
function coregas_asset_ver( $rel ) {
	$path = COREGAS_DIR . '/' . ltrim( $rel, '/' );
	return file_exists( $path ) ? COREGAS_VERSION . '.' . filemtime( $path ) : COREGAS_VERSION;
}

/**
 * Google Fonts stylesheet URL for the selected fonts.
 *
 * @return string
 */
function coregas_fonts_url() {
	$heading  = coregas_heading_fonts();
	$body     = coregas_body_fonts();
	$h        = coregas_setting( 'font_heading' );
	$b        = coregas_setting( 'font_body' );
	$families = array();
	$families[] = isset( $heading[ $h ] ) ? $heading[ $h ]['google'] : $heading['barlow']['google'];
	$families[] = isset( $body[ $b ] ) ? $body[ $b ]['google'] : $body['geist']['google'];
	$families[] = 'IBM+Plex+Mono:wght@400;500';
	// Same family chosen twice (e.g. Barlow + Barlow): Google merges duplicates fine, but keep the list unique.
	$families = array_unique( $families );
	return 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $families ) . '&display=swap';
}

add_action( 'wp_enqueue_scripts', 'coregas_assets' );
/**
 * Front-end styles and scripts.
 */
function coregas_assets() {
	wp_enqueue_style( 'coregas-fonts', coregas_fonts_url(), array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Google Fonts URLs are versioned by query.
	wp_enqueue_style( 'coregas-main', COREGAS_URI . '/assets/css/main.css', array(), coregas_asset_ver( 'assets/css/main.css' ) );

	// Optional JS smooth scrolling is loaded on demand by main.js (setting, or ?smooth=1 per browser).
	$deps = array();
	wp_enqueue_script( 'coregas-main', COREGAS_URI . '/assets/js/main.js', $deps, coregas_asset_ver( 'assets/js/main.js' ), true );
	wp_localize_script(
		'coregas-main',
		'coregasConfig',
		array(
			'heroIntro'    => '1' === coregas_setting( 'hero_intro' ),
			'hideOnScroll' => '1' === coregas_setting( 'header_hide_on_scroll' ),
			'smoothScroll' => '1' === coregas_setting( 'smooth_scroll' ),
		)
	);
}

add_filter( 'wp_resource_hints', 'coregas_resource_hints', 10, 2 );
/**
 * Preconnect to Google Fonts.
 *
 * @param array  $urls URLs.
 * @param string $type Hint type.
 * @return array
 */
function coregas_resource_hints( $urls, $type ) {
	if ( 'preconnect' === $type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}

add_filter( 'body_class', 'coregas_body_class' );
/**
 * Pages that do not open with a dark hero/banner get a solid header.
 *
 * @param array $classes Classes.
 * @return array
 */
function coregas_body_class( $classes ) {
	if ( ! coregas_has_dark_top() ) {
		$classes[] = 'header-solid';
	}
	return $classes;
}

/**
 * Whether the current view opens with a dark hero or banner.
 *
 * @return bool
 */
function coregas_has_dark_top() {
	if ( is_front_page() ) {
		$first = coregas_get_sections( get_queried_object_id() );
		return $first && 'hero' === coregas_meta( $first[0]->ID, 'section_type' );
	}
	if ( is_page() ) {
		return '1' === coregas_meta( get_queried_object_id(), 'banner_show' );
	}
	// Archives, single items, news and search all open with the dark banner.
	return ! is_404();
}

add_filter( 'excerpt_length', fn() => 26 );
add_filter( 'excerpt_more', fn() => '…' );
