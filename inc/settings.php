<?php
/**
 * Theme Settings: a single cg_settings post that stores every global option.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

/**
 * ID of the settings post, creating it if needed (admin/activation only).
 *
 * @param bool $create Create when missing.
 * @return int
 */
function coregas_settings_id( $create = false ) {
	static $id = null;
	if ( $id ) {
		return $id;
	}
	$stored = (int) get_option( 'coregas_settings_id' );
	if ( $stored && 'cg_settings' === get_post_type( $stored ) ) {
		$id = $stored;
		return $id;
	}
	$found = get_posts(
		array(
			'post_type'      => 'cg_settings',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'fields'         => 'ids',
		)
	);
	if ( $found ) {
		$id = (int) $found[0];
	} elseif ( $create ) {
		$id = (int) wp_insert_post(
			array(
				'post_type'   => 'cg_settings',
				'post_status' => 'publish',
				'post_title'  => __( 'Theme Settings', 'coregas' ),
			)
		);
	}
	if ( $id ) {
		update_option( 'coregas_settings_id', $id, true );
	}
	return (int) $id;
}

/**
 * Read a global setting (with schema default).
 *
 * @param string $key     Setting key.
 * @param mixed  $default Optional default override.
 * @return mixed
 */
function coregas_setting( $key, $default = null ) {
	$id = coregas_settings_id();
	if ( $id ) {
		return coregas_meta( $id, $key, $default );
	}
	if ( null !== $default ) {
		return $default;
	}
	$field = coregas_find_field( 'cg_settings', $key );
	return ( $field && isset( $field['default'] ) ) ? $field['default'] : '';
}

add_action( 'admin_init', 'coregas_ensure_settings' );
/**
 * Make sure the settings record exists for administrators.
 */
function coregas_ensure_settings() {
	if ( current_user_can( 'manage_options' ) ) {
		coregas_settings_id( true );
	}
}

add_action( 'admin_menu', 'coregas_settings_menu' );
/**
 * "Theme Settings" menu item that opens the settings record directly.
 */
function coregas_settings_menu() {
	$hook = add_menu_page(
		__( 'Theme Settings', 'coregas' ),
		__( 'Theme Settings', 'coregas' ),
		'manage_options',
		'coregas-settings',
		'__return_null',
		'dashicons-admin-customizer',
		3
	);
	add_action(
		'load-' . $hook,
		function () {
			$id = coregas_settings_id( true );
			wp_safe_redirect( admin_url( 'post.php?post=' . $id . '&action=edit' ) );
			exit;
		}
	);
}

add_filter( 'parent_file', 'coregas_settings_parent_file' );
/**
 * Keep the menu item highlighted while editing settings.
 *
 * @param string $parent Parent file.
 * @return string
 */
function coregas_settings_parent_file( $parent ) {
	$screen = get_current_screen();
	if ( $screen && 'cg_settings' === $screen->post_type ) {
		return 'coregas-settings';
	}
	return $parent;
}

add_filter( 'enter_title_here', 'coregas_settings_title_placeholder', 10, 2 );
/**
 * Placeholder text.
 *
 * @param string  $text Placeholder.
 * @param WP_Post $post Post.
 * @return string
 */
function coregas_settings_title_placeholder( $text, $post ) {
	if ( 'cg_section' === $post->post_type ) {
		return __( 'Section name (only shown in the admin)', 'coregas' );
	}
	return $text;
}

add_filter( 'post_updated_messages', 'coregas_settings_messages' );
/**
 * Friendlier "saved" message.
 *
 * @param array $messages Messages.
 * @return array
 */
function coregas_settings_messages( $messages ) {
	$saved = __( 'Theme settings saved.', 'coregas' );
	$messages['cg_settings'] = array_fill( 0, 11, $saved );
	$messages['cg_settings'][0] = '';
	return $messages;
}
