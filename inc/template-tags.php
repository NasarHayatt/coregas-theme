<?php
/**
 * Rendering helpers used by templates.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve an editor-entered link: "/path/" → site URL, "#anchor" and full URLs unchanged.
 *
 * @param string $url Raw link.
 * @return string
 */
function coregas_link( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return '';
	}
	if ( 0 === strpos( $url, '#' ) ) {
		return $url;
	}
	if ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) {
		return home_url( $url );
	}
	return $url;
}

/**
 * Whether a link points off-site.
 *
 * @param string $url URL.
 * @return bool
 */
function coregas_is_external( $url ) {
	$host = wp_parse_url( $url, PHP_URL_HOST );
	return $host && wp_parse_url( home_url(), PHP_URL_HOST ) !== $host;
}

/**
 * Logo <img> (official file bundled with the theme unless replaced in Theme Settings).
 *
 * @param string $which 'header' or 'footer'.
 * @return string
 */
function coregas_logo( $which = 'header' ) {
	$id = (int) coregas_setting( 'footer' === $which ? 'footer_logo' : 'logo' );
	if ( ! $id && 'footer' === $which ) {
		$id = (int) coregas_setting( 'logo' );
	}
	$alt = coregas_setting( 'company_name' );
	if ( $id ) {
		return wp_get_attachment_image( $id, 'medium', false, array( 'alt' => $alt, 'class' => 'logo-img' ) );
	}
	return sprintf( '<img class="logo-img" src="%s" alt="%s" width="2000" height="833">', esc_url( COREGAS_URI . '/assets/img/core-gas-logo.webp' ), esc_attr( $alt ) );
}

/**
 * Image from an attachment ID with a URL fallback.
 *
 * @param int    $id       Attachment ID.
 * @param string $size     Size.
 * @param string $fallback Fallback URL.
 * @param array  $attr     Attributes.
 * @return string
 */
function coregas_image( $id, $size = 'large', $fallback = '', $attr = array() ) {
	$id = (int) $id;
	if ( $id && wp_attachment_is_image( $id ) ) {
		return wp_get_attachment_image( $id, $size, false, $attr );
	}
	if ( ! $fallback ) {
		return '';
	}
	$html = '<img src="' . esc_url( $fallback ) . '"';
	foreach ( wp_parse_args( $attr, array( 'alt' => '' ) ) as $k => $v ) {
		$html .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
	}
	return $html . '>';
}

/**
 * Flat list of links for a menu location (top level only), with aria-current.
 *
 * @param string $location Menu location.
 * @param string $class    Class for each link.
 * @return string
 */
function coregas_menu_links( $location, $class = '' ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return '';
	}
	$items = wp_get_nav_menu_items( $locations[ $location ] );
	if ( ! $items ) {
		return '';
	}
	_wp_menu_item_classes_by_context( $items );
	$out = '';
	foreach ( $items as $item ) {
		if ( (int) $item->menu_item_parent ) {
			continue;
		}
		if ( function_exists( 'coregas_menu_item_is_empty_list' ) && coregas_menu_item_is_empty_list( $item ) ) {
			continue; // e.g. "Industries" while no industry is published yet.
		}
		$current = in_array( 'current-menu-item', (array) $item->classes, true ) || in_array( 'current_page_item', (array) $item->classes, true );
		$target  = $item->target ? ' target="' . esc_attr( $item->target ) . '" rel="noopener"' : '';
		$arrow   = ( $item->target || coregas_is_external( $item->url ) ) ? ' <span aria-hidden="true">&#8599;</span>' : '';
		$out    .= sprintf(
			'<a href="%s"%s%s%s>%s%s</a>',
			esc_url( $item->url ),
			$class ? ' class="' . esc_attr( $class ) . '"' : '',
			$current ? ' aria-current="page"' : '',
			$target,
			esc_html( $item->title ),
			$arrow
		);
	}
	return $out;
}

/**
 * Name of the menu assigned to a location (used as footer column heading).
 *
 * @param string $location Location.
 * @return string
 */
function coregas_menu_name( $location ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return '';
	}
	$menu = wp_get_nav_menu_object( $locations[ $location ] );
	return $menu ? $menu->name : '';
}

/**
 * Escaped paragraphs from plain text (blank line = new paragraph).
 *
 * @param string $text Text.
 * @return string
 */
function coregas_paragraphs( $text ) {
	$parts = preg_split( '/\R\s*\R/', trim( (string) $text ) );
	$out   = '';
	foreach ( array_filter( $parts ) as $p ) {
		$out .= '<p>' . nl2br( esc_html( $p ) ) . '</p>';
	}
	return $out;
}

/**
 * Social profile links from Theme Settings.
 *
 * @return string
 */
function coregas_social_links() {
	$networks = array(
		'linkedin'  => 'LinkedIn',
		'facebook'  => 'Facebook',
		'x'         => 'X',
		'instagram' => 'Instagram',
		'youtube'   => 'YouTube',
		'whatsapp'  => 'WhatsApp',
	);
	$out = '';
	foreach ( $networks as $key => $label ) {
		$url = coregas_setting( 'social_' . $key );
		if ( $url ) {
			$out .= sprintf( '<a href="%s" target="_blank" rel="noopener" aria-label="%s">%s</a>', esc_url( $url ), esc_attr( $label ), coregas_icon( $key ) );
		}
	}
	return $out;
}

/**
 * Inline SVG icons (fixed markup, safe to echo).
 *
 * @param string $name Icon name.
 * @return string
 */
function coregas_icon( $name ) {
	$paths = array(
		'linkedin'  => 'M4.98 3.5C4.98 4.88 3.87 6 2.5 6S0 4.88 0 3.5 1.12 1 2.5 1 4.98 2.12 4.98 3.5zM.22 8h4.56v14H.22V8zm7.44 0h4.37v1.92h.06c.61-1.15 2.1-2.36 4.32-2.36 4.62 0 5.47 3.04 5.47 7v7.44h-4.56v-6.6c0-1.57-.03-3.6-2.19-3.6-2.2 0-2.53 1.71-2.53 3.48V22H7.66V8z',
		'facebook'  => 'M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12z',
		'x'         => 'M18.24 2.25h3.31l-7.23 8.26 8.5 11.24h-6.66l-5.21-6.82-5.97 6.82H1.67l7.73-8.84L1.25 2.25h6.83l4.71 6.23 5.45-6.23zm-1.16 17.52h1.83L7.08 4.13H5.12l11.96 15.64z',
		'instagram' => 'M12 2.16c3.2 0 3.58.01 4.85.07 3.25.15 4.77 1.69 4.92 4.92.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.15 3.23-1.66 4.77-4.92 4.92-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-3.26-.15-4.77-1.7-4.92-4.92C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85C2.38 3.92 3.9 2.38 7.15 2.23 8.42 2.17 8.8 2.16 12 2.16zM12 0C8.74 0 8.33.01 7.05.07 2.7.27.27 2.69.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.2 4.36 2.62 6.78 6.98 6.98C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c4.35-.2 6.78-2.62 6.98-6.98.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.2-4.35-2.62-6.78-6.98-6.98C15.67.01 15.26 0 12 0zm0 5.84a6.16 6.16 0 1 0 0 12.32 6.16 6.16 0 0 0 0-12.32zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.4-11.85a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88z',
		'youtube'   => 'M23.5 6.19a3.02 3.02 0 0 0-2.12-2.14C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.5A3.02 3.02 0 0 0 .5 6.19 31.6 31.6 0 0 0 0 12a31.6 31.6 0 0 0 .5 5.81 3.02 3.02 0 0 0 2.12 2.14c1.88.5 9.38.5 9.38.5s7.5 0 9.38-.5a3.02 3.02 0 0 0 2.12-2.14A31.6 31.6 0 0 0 24 12a31.6 31.6 0 0 0-.5-5.81zM9.55 15.57V8.43L15.82 12l-6.27 3.57z',
		'whatsapp'  => 'M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.39-1.47-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.6.13-.14.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.07c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.12-.27-.2-.57-.35zM12.05 21.79h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26c0-5.45 4.44-9.88 9.89-9.88 2.64 0 5.12 1.03 6.99 2.9a9.82 9.82 0 0 1 2.89 6.99c0 5.45-4.44 9.88-9.88 9.88zm8.41-18.3A11.82 11.82 0 0 0 12.05 0C5.5 0 .16 5.34.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.88 11.88 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.34 11.89-11.89 0-3.18-1.24-6.17-3.48-8.42z',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="' . $paths[ $name ] . '"/></svg>';
}

/**
 * Pill button markup.
 *
 * @param string $label Label.
 * @param string $url   Link.
 * @param string $style flame|light|maroon.
 * @param string $icon  Icon glyph.
 * @return string
 */
function coregas_button( $label, $url, $style = 'flame', $icon = '↗' ) {
	if ( ! $label || ! $url ) {
		return '';
	}
	$href = coregas_link( $url );
	$ext  = coregas_is_external( $href ) ? ' target="_blank" rel="noopener"' : '';
	return sprintf( '<a class="pill pill--%s" href="%s"%s><span>%s</span><i aria-hidden="true">%s</i></a>', esc_attr( $style ), esc_url( $href ), $ext, esc_html( $label ), esc_html( $icon ) );
}

/**
 * Inner-page / archive banner.
 *
 * @param array $args eyebrow, title, text, image (attachment ID).
 */
function coregas_banner( $args ) {
	get_template_part( 'template-parts/banner', null, $args );
}

/**
 * Placeholder photo for an item with no featured image, so feature rows never
 * show an empty side. Replace by setting a featured image on the item.
 *
 * @param WP_Post $item  Item.
 * @param int     $index Position in the list.
 * @return string URL.
 */
function coregas_placeholder_image( $item, $index ) {
	if ( 'cg_licence' === $item->post_type ) {
		return coregas_stock_photo( 4 );
	}
	return coregas_stock_photo( 'post' === $item->post_type ? $index : max( (int) $item->menu_order, $index ) );
}

/**
 * Placeholder photo pool (industrial / gas imagery) used until real photos are uploaded.
 *
 * @param int $i Index.
 * @return string URL.
 */
function coregas_stock_photo( $i ) {
	// Photos chosen in Theme Settings → Brand → Default photos take priority.
	$custom = array();
	foreach ( (array) coregas_setting( 'stock_images' ) as $row ) {
		$url = ! empty( $row['image'] ) ? wp_get_attachment_image_url( (int) $row['image'], 'large' ) : '';
		if ( $url ) {
			$custom[] = $url;
		}
	}
	if ( $custom ) {
		return $custom[ abs( (int) $i ) % count( $custom ) ];
	}
	$stock = COREGAS_URI . '/assets/img/stock/';
	$pool  = array(
		'https://images.unsplash.com/photo-1509390032417-bd802f3fc669?w=1400&q=80&auto=format&fit=crop', // gas facility from above.
		'https://images.unsplash.com/photo-1627752633728-85ebd86dfe4a?w=1400&q=80&auto=format&fit=crop', // pipework and valves.
		$stock . 'production-floor.jpg',                                                                    // industrial consumer.
		$stock . 'flame.jpg',                                                                               // gas flame.
		'https://images.unsplash.com/photo-1509390288171-ce2088f7d08e?w=1400&q=80&auto=format&fit=crop', // plant at night.
	);
	return $pool[ abs( (int) $i ) % count( $pool ) ];
}

/**
 * Steps strip markup.
 *
 * @param array $steps Each: tag, title, text, image (attachment ID), fallback (URL).
 */
function coregas_render_strip( $steps, $photos = true ) {
	if ( ! $steps ) {
		return;
	}
	echo '<div class="strip' . ( $photos ? '' : ' strip--plain' ) . '" style="--steps:' . (int) count( $steps ) . '">';
	foreach ( array_values( $steps ) as $i => $st ) {
		echo '<article class="step">';
		if ( $photos ) {
			$img = coregas_image( isset( $st['image'] ) ? $st['image'] : 0, 'large', isset( $st['fallback'] ) ? $st['fallback'] : coregas_stock_photo( $i ), array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) );
			echo '<figure class="step__media">' . $img . '</figure>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( ! empty( $st['tag'] ) ) {
			echo '<p class="mono step__tag">' . esc_html( $st['tag'] ) . '</p>';
		}
		echo '<h3>' . esc_html( isset( $st['title'] ) ? $st['title'] : '' ) . '</h3>';
		if ( ! empty( $st['text'] ) ) {
			echo '<p class="step__text">' . esc_html( $st['text'] ) . '</p>';
		}
		echo '</article>';
	}
	echo '</div>';
}

/**
 * Placeholder banner photo per page/type, delivered already black and white by the
 * image CDN. Replaced as soon as a banner photo is set on the page or in Theme Settings.
 *
 * @param string $context Page slug or post type.
 * @return string URL.
 */
function coregas_banner_placeholder( $context ) {
	$u = function ( $id ) {
		return 'https://images.unsplash.com/photo-' . $id . '?w=2000&q=75&auto=format&fit=crop&sat=-100&con=10';
	};
	$map = array(
		'about-us'    => $u( '1509390032417-bd802f3fc669' ), // facility from above at night.
		'gas-supply'  => $u( '1627752633728-85ebd86dfe4a' ), // pipework and valves.
		'what-we-do'  => $u( '1509390288171-ce2088f7d08e' ), // plant at night.
		'cg_service'  => $u( '1627752633728-85ebd86dfe4a' ),
		'regulation'  => $u( '1509390288171-ce2088f7d08e' ), // plant at night.
		'cg_licence'  => $u( '1509390288171-ce2088f7d08e' ),
		'contact-us'  => $u( '1509390032417-bd802f3fc669' ),
		'cg_industry' => $u( '1627752633728-85ebd86dfe4a' ),
	);
	return isset( $map[ $context ] ) ? $map[ $context ] : $u( '1509390288171-ce2088f7d08e' );
}

/**
 * Banner placeholder for the current view.
 *
 * @return string URL.
 */
function coregas_current_banner_placeholder() {
	if ( is_page() ) {
		return coregas_banner_placeholder( get_post_field( 'post_name', get_queried_object_id() ) );
	}
	if ( is_singular() || is_post_type_archive() ) {
		$type = is_singular() ? get_post_type() : get_query_var( 'post_type' );
		return coregas_banner_placeholder( is_array( $type ) ? reset( $type ) : (string) $type );
	}
	return coregas_banner_placeholder( 'default' );
}

/**
 * Front-end wording from Theme Settings → Pages & wording.
 *
 * @param string $key  Label key (see coregas_label_defaults()).
 * @param array  $vars Replacements, e.g. array( 'list' => 'licences' ).
 * @return string Plain text (escape on output).
 */
function coregas_label( $key, $vars = array() ) {
	$defaults = coregas_label_defaults();
	$text     = (string) coregas_setting( 'label_' . $key, isset( $defaults[ $key ] ) ? $defaults[ $key ][1] : '' );
	if ( '' === trim( $text ) && isset( $defaults[ $key ] ) ) {
		$text = $defaults[ $key ][1];
	}
	foreach ( $vars as $k => $v ) {
		$text = str_replace( '{' . $k . '}', $v, $text );
	}
	return $text;
}

/**
 * Banner settings for an automatic list page (archive, news, search).
 *
 * @param string $context Post type, 'news' or 'search'.
 * @param array  $default Defaults: eyebrow, title, text.
 * @return array
 */
function coregas_archive_banner( $context, $default ) {
	$get = function ( $k ) use ( $context ) {
		return trim( (string) coregas_setting( 'arch_' . $context . '_' . $k ) );
	};
	return array(
		'eyebrow'  => $get( 'eyebrow' ) ? $get( 'eyebrow' ) : $default['eyebrow'],
		'title'    => $get( 'title' ) ? $get( 'title' ) : $default['title'],
		'text'     => $get( 'text' ) ? $get( 'text' ) : $default['text'],
		'image'    => (int) $get( 'image' ),
		'fallback' => coregas_current_banner_placeholder(),
	);
}

/**
 * Extra classes on a section: spacing, visibility, custom class.
 *
 * @param int $sid Section ID.
 * @return string
 */
function coregas_section_classes( $sid ) {
	$c   = array();
	$sp  = coregas_meta( $sid, 'sec_spacing' );
	$vis = coregas_meta( $sid, 'sec_visibility' );
	if ( $sp && 'default' !== $sp ) {
		$c[] = 'space-' . sanitize_html_class( $sp );
	}
	if ( 'desktop' === $vis ) {
		$c[] = 'hide-mobile';
	} elseif ( 'mobile' === $vis ) {
		$c[] = 'hide-desktop';
	}
	foreach ( preg_split( '/\s+/', (string) coregas_meta( $sid, 'sec_class' ) ) as $cls ) {
		if ( $cls ) {
			$c[] = sanitize_html_class( $cls );
		}
	}
	return implode( ' ', $c );
}
