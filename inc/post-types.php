<?php
/**
 * Custom post types.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build a full label set.
 *
 * @param string $singular Singular name.
 * @param string $plural   Plural name.
 * @return array
 */
function coregas_labels( $singular, $plural ) {
	return array(
		'name'               => $plural,
		'singular_name'      => $singular,
		'menu_name'          => $plural,
		'add_new'            => __( 'Add New', 'coregas' ),
		/* translators: %s: post type singular name */
		'add_new_item'       => sprintf( __( 'Add New %s', 'coregas' ), $singular ),
		/* translators: %s: post type singular name */
		'edit_item'          => sprintf( __( 'Edit %s', 'coregas' ), $singular ),
		/* translators: %s: post type singular name */
		'new_item'           => sprintf( __( 'New %s', 'coregas' ), $singular ),
		/* translators: %s: post type singular name */
		'view_item'          => sprintf( __( 'View %s', 'coregas' ), $singular ),
		/* translators: %s: post type plural name */
		'search_items'       => sprintf( __( 'Search %s', 'coregas' ), $plural ),
		'not_found'          => __( 'Nothing found.', 'coregas' ),
		'not_found_in_trash' => __( 'Nothing found in Trash.', 'coregas' ),
		'all_items'          => $plural,
	);
}

add_action( 'init', 'coregas_register_post_types' );
/**
 * Register all theme post types.
 */
function coregas_register_post_types() {

	// Theme Settings: a single record edited by administrators only.
	register_post_type(
		'cg_settings',
		array(
			'labels'          => coregas_labels( __( 'Theme Settings', 'coregas' ), __( 'Theme Settings', 'coregas' ) ),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => false, // reached through the "Theme Settings" menu item (inc/settings.php).
			'show_in_rest'    => false,
			'supports'        => array( 'title', 'revisions' ),
			'map_meta_cap'    => false,
			'capabilities'    => array(
				'edit_post'          => 'manage_options',
				'read_post'          => 'manage_options',
				'delete_post'        => 'do_not_allow',
				'edit_posts'         => 'manage_options',
				'edit_others_posts'  => 'manage_options',
				'edit_published_posts' => 'manage_options',
				'publish_posts'      => 'manage_options',
				'read_private_posts' => 'manage_options',
				'create_posts'       => 'do_not_allow',
				'delete_posts'       => 'do_not_allow',
			),
		)
	);

	// Page Sections: building blocks placed on pages.
	register_post_type(
		'cg_section',
		array(
			'labels'        => array_merge( coregas_labels( __( 'Page Section', 'coregas' ), __( 'Page Sections', 'coregas' ) ), array( 'all_items' => __( 'All sections (advanced)', 'coregas' ) ) ),
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => 'edit.php?post_type=page', // sections are normally edited inside each page.
			'show_in_rest'  => false,
			'menu_position' => 21,
			'menu_icon'     => 'dashicons-layout',
			'supports'      => array( 'title', 'editor', 'page-attributes', 'revisions' ),
		)
	);

	$content_types = array(
		'cg_industry' => array( __( 'Industry', 'coregas' ), __( 'Industries', 'coregas' ), 'industries', 'dashicons-building', 22 ),
		'cg_service'  => array( __( 'Supply Service', 'coregas' ), __( 'Supply Services', 'coregas' ), 'services', 'dashicons-randomize', 23 ),
		'cg_licence'  => array( __( 'Licence', 'coregas' ), __( 'Licences', 'coregas' ), 'licences', 'dashicons-awards', 24 ),
	);
	foreach ( $content_types as $type => $c ) {
		register_post_type(
			$type,
			array(
				'labels'        => coregas_labels( $c[0], $c[1] ),
				'public'        => true,
				'has_archive'   => $c[2],
				'rewrite'       => array( 'slug' => $c[2], 'with_front' => false ),
				'show_in_rest'  => true,
				'menu_position' => $c[4],
				'menu_icon'     => $c[3],
				'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'revisions' ),
			)
		);
	}
}

/* Sections list: show type, page and order columns. */
add_filter( 'manage_cg_section_posts_columns', 'coregas_section_columns' );
/**
 * Columns.
 *
 * @param array $cols Columns.
 * @return array
 */
function coregas_section_columns( $cols ) {
	$out = array();
	foreach ( $cols as $k => $v ) {
		$out[ $k ] = $v;
		if ( 'title' === $k ) {
			$out['cg_type']  = __( 'Type', 'coregas' );
			$out['cg_page']  = __( 'Page', 'coregas' );
			$out['cg_order'] = __( 'Order', 'coregas' );
		}
	}
	return $out;
}

add_action( 'manage_cg_section_posts_custom_column', 'coregas_section_column_values', 10, 2 );
/**
 * Column values.
 *
 * @param string $col     Column.
 * @param int    $post_id Post ID.
 */
function coregas_section_column_values( $col, $post_id ) {
	if ( 'cg_type' === $col ) {
		echo esc_html( ucfirst( (string) coregas_meta( $post_id, 'section_type' ) ) );
	} elseif ( 'cg_page' === $col ) {
		$page = (int) coregas_meta( $post_id, 'page' );
		echo $page ? '<a href="' . esc_url( get_edit_post_link( $page ) ) . '">' . esc_html( get_the_title( $page ) ) . '</a>' : '&mdash;';
	} elseif ( 'cg_order' === $col ) {
		echo (int) get_post_field( 'menu_order', $post_id );
	}
}

add_action( 'pre_get_posts', 'coregas_admin_section_order' );
/**
 * Order sections by page order in the admin list.
 *
 * @param WP_Query $q Query.
 */
function coregas_admin_section_order( $q ) {
	if ( is_admin() && $q->is_main_query() && 'cg_section' === $q->get( 'post_type' ) && ! $q->get( 'orderby' ) ) {
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'ASC' ) );
	}
}

/* Front-end archives: respect the manual order. */
add_action( 'pre_get_posts', 'coregas_archive_order' );
/**
 * Order content archives by "Order".
 *
 * @param WP_Query $q Query.
 */
function coregas_archive_order( $q ) {
	if ( ! is_admin() && $q->is_main_query() && $q->is_post_type_archive( array( 'cg_industry', 'cg_service', 'cg_licence' ) ) ) {
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		$q->set( 'posts_per_page', 50 );
	}
}
