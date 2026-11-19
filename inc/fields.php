<?php
/**
 * Lightweight meta-box field framework.
 *
 * Every editable value in the theme is a post meta field described by a schema
 * (see inc/schemas.php). This file renders those schemas as meta boxes,
 * sanitises and saves them, and reads them back with defaults.
 *
 * Supported field types: text, textarea, html, url, email, number, color,
 * select, checkbox, image, page, code, repeater (one level, any of the above
 * except repeater as sub-fields).
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

const COREGAS_META_PREFIX = '_cg_';

/**
 * All field groups for a post type.
 *
 * @param string $post_type Post type.
 * @return array
 */
function coregas_groups_for( $post_type ) {
	$all = coregas_schemas();
	return isset( $all[ $post_type ] ) ? $all[ $post_type ] : array();
}

/**
 * Find a field definition by key within a post type.
 *
 * @param string $post_type Post type.
 * @param string $key       Field key.
 * @return array|null
 */
function coregas_find_field( $post_type, $key ) {
	foreach ( coregas_groups_for( $post_type ) as $group ) {
		foreach ( $group['fields'] as $field ) {
			if ( $field['key'] === $key ) {
				return $field;
			}
		}
	}
	return null;
}

/**
 * Read a field value, falling back to the schema default when never saved.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Field key.
 * @param mixed  $default Optional override default.
 * @return mixed
 */
function coregas_meta( $post_id, $key, $default = null ) {
	$meta_key = COREGAS_META_PREFIX . $key;
	if ( $post_id && metadata_exists( 'post', $post_id, $meta_key ) ) {
		return get_post_meta( $post_id, $meta_key, true );
	}
	if ( null !== $default ) {
		return $default;
	}
	$field = $post_id ? coregas_find_field( get_post_type( $post_id ), $key ) : null;
	return ( $field && isset( $field['default'] ) ) ? $field['default'] : '';
}

/* -------------------------------------------------------------------------
 * Meta boxes
 * ---------------------------------------------------------------------- */

add_action( 'add_meta_boxes', 'coregas_register_meta_boxes', 10, 2 );
/**
 * Register one meta box per field group.
 *
 * @param string  $post_type Post type.
 * @param WP_Post $post      Post.
 */
function coregas_register_meta_boxes( $post_type, $post ) {
	foreach ( coregas_groups_for( $post_type ) as $group ) {
		add_meta_box(
			'cg-group-' . $group['id'],
			$group['title'],
			'coregas_render_group',
			$post_type,
			isset( $group['context'] ) ? $group['context'] : 'normal',
			isset( $group['priority'] ) ? $group['priority'] : 'high',
			array( 'group' => $group )
		);
	}
}

/**
 * Meta box callback.
 *
 * @param WP_Post $post Post.
 * @param array   $box  Meta box args.
 */
function coregas_render_group( $post, $box ) {
	$group = $box['args']['group'];
	static $nonce_done = false;
	if ( ! $nonce_done ) {
		wp_nonce_field( 'coregas_save_fields', 'coregas_fields_nonce' );
		$nonce_done = true;
	}
	if ( ! empty( $group['description'] ) ) {
		echo '<p class="cg-group-desc">' . wp_kses_post( $group['description'] ) . '</p>';
	}
	echo '<div class="cg-fields">';
	foreach ( $group['fields'] as $field ) {
		$value = coregas_meta( $post->ID, $field['key'], isset( $field['default'] ) ? $field['default'] : '' );
		coregas_render_field_row( $field, $value, 'cg[' . $field['key'] . ']', 'cg-' . $field['key'] );
	}
	echo '</div>';
	if ( 'cg_settings' === $post->post_type ) {
		printf(
			'<p class="cg-reset"><button type="submit" class="button-link" name="cg_reset_group" value="%s" onclick="return confirm(\'%s\');">%s</button></p>',
			esc_attr( $group['id'] ),
			esc_js( __( 'Reset this section to its default values? Other sections stay as they are. The current values are kept in Backup & restore.', 'coregas' ) ),
			esc_html__( 'Reset this section to defaults', 'coregas' )
		);
	}
}

/**
 * Render a labelled field row.
 *
 * @param array  $field Field.
 * @param mixed  $value Value.
 * @param string $name  Input name.
 * @param string $id    Input id.
 */
function coregas_render_field_row( $field, $value, $name, $id ) {
	if ( 'heading' === $field['type'] ) {
		echo '<h4 class="cg-subheading">' . esc_html( $field['label'] ) . '</h4>';
		return;
	}
	$wide = in_array( $field['type'], array( 'repeater', 'html', 'code' ), true ) ? ' cg-field--wide' : '';
	echo '<div class="cg-field cg-field--' . esc_attr( $field['type'] ) . esc_attr( $wide ) . '">';
	if ( 'checkbox' !== $field['type'] ) {
		echo '<label class="cg-field__label" for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label>';
	}
	echo '<div class="cg-field__control">';
	coregas_render_input( $field, $value, $name, $id );
	if ( ! empty( $field['desc'] ) ) {
		echo '<p class="description">' . wp_kses_post( $field['desc'] ) . '</p>';
	}
	echo '</div></div>';
}

/**
 * Render the input element(s) for a field.
 *
 * @param array  $field Field.
 * @param mixed  $value Value.
 * @param string $name  Input name.
 * @param string $id    Input id.
 */
function coregas_render_input( $field, $value, $name, $id ) {
	$ph = isset( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '';
	switch ( $field['type'] ) {
		case 'textarea':
			printf( '<textarea id="%s" name="%s" rows="%d" class="large-text"%s>%s</textarea>', esc_attr( $id ), esc_attr( $name ), isset( $field['rows'] ) ? (int) $field['rows'] : 4, $ph, esc_textarea( (string) $value ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			break;

		case 'code':
			printf( '<textarea id="%s" name="%s" rows="%d" class="large-text code" spellcheck="false"%s>%s</textarea>', esc_attr( $id ), esc_attr( $name ), isset( $field['rows'] ) ? (int) $field['rows'] : 6, $ph, esc_textarea( (string) $value ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			break;

		case 'html':
			// Repeater rows cannot host TinyMCE reliably, so rich text inside repeaters falls back to a textarea.
			if ( false !== strpos( $name, '][' ) ) {
				printf( '<textarea id="%s" name="%s" rows="5" class="large-text">%s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( (string) $value ) );
			} else {
				wp_editor(
					(string) $value,
					sanitize_key( str_replace( '-', '_', $id ) ),
					array(
						'textarea_name' => $name,
						'textarea_rows' => isset( $field['rows'] ) ? (int) $field['rows'] : 8,
						'media_buttons' => false,
						'teeny'         => true,
					)
				);
			}
			break;

		case 'color':
			printf( '<input type="text" id="%s" name="%s" value="%s" class="cg-color" data-default-color="%s">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ), esc_attr( isset( $field['default'] ) ? $field['default'] : '' ) );
			break;

		case 'select':
			printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( $field['choices'] as $k => $label ) {
				printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( (string) $value, (string) $k, false ), esc_html( $label ) );
			}
			echo '</select>';
			break;

		case 'checkbox':
			printf( '<input type="hidden" name="%s" value="0">', esc_attr( $name ) );
			printf( '<label class="cg-check"><input type="checkbox" id="%s" name="%s" value="1"%s> %s</label>', esc_attr( $id ), esc_attr( $name ), checked( (string) $value, '1', false ), esc_html( $field['label'] ) );
			break;

		case 'image':
			$src = $value ? wp_get_attachment_image_url( (int) $value, 'medium' ) : '';
			printf( '<div class="cg-image" data-cg-image><input type="hidden" id="%s" name="%s" value="%s">', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ) );
			printf( '<div class="cg-image__preview">%s</div>', $src ? '<img src="' . esc_url( $src ) . '" alt="">' : '' );
			echo '<button type="button" class="button cg-image__pick">' . esc_html__( 'Choose image', 'coregas' ) . '</button> ';
			echo '<button type="button" class="button-link cg-image__clear">' . esc_html__( 'Remove', 'coregas' ) . '</button></div>';
			break;

		case 'page':
			wp_dropdown_pages(
				array(
					'name'              => esc_attr( $name ),
					'id'                => esc_attr( $id ),
					'selected'          => (int) $value,
					'show_option_none'  => isset( $field['none'] ) ? $field['none'] : __( '— Select —', 'coregas' ),
					'option_none_value' => '0',
				)
			);
			break;

		case 'repeater':
			coregas_render_repeater( $field, is_array( $value ) ? $value : array(), $name, $id );
			break;

		case 'number':
			printf( '<input type="number" id="%s" name="%s" value="%s" step="%s" class="small-text"%s>', esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ), esc_attr( isset( $field['step'] ) ? $field['step'] : '1' ), $ph ); // phpcs:ignore WordPress.Security.EscapeOutput
			break;

		default: // text, url, email.
			$type = in_array( $field['type'], array( 'url', 'email', 'password' ), true ) ? $field['type'] : 'text';
			$auto = 'password' === $type ? ' autocomplete="new-password"' : '';
			printf( '<input type="%s" id="%s" name="%s" value="%s" class="regular-text"%s%s>', esc_attr( $type ), esc_attr( $id ), esc_attr( $name ), esc_attr( (string) $value ), $ph, $auto ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * Render a repeater with a hidden row template for JS.
 *
 * @param array  $field Field.
 * @param array  $rows  Saved rows.
 * @param string $name  Base name.
 * @param string $id    Base id.
 */
function coregas_render_repeater( $field, $rows, $name, $id ) {
	$label = isset( $field['row_label'] ) ? $field['row_label'] : __( 'Item', 'coregas' );
	echo '<div class="cg-repeater" data-cg-repeater data-name="' . esc_attr( $name ) . '" data-id="' . esc_attr( $id ) . '">';
	echo '<div class="cg-repeater__rows">';
	foreach ( array_values( $rows ) as $i => $row ) {
		coregas_render_repeater_row( $field, $row, $name . '[' . $i . ']', $id . '-' . $i, $label );
	}
	echo '</div>';
	echo '<script type="text/template" class="cg-repeater__tpl">';
	ob_start();
	coregas_render_repeater_row( $field, array(), $name . '[__i__]', $id . '-__i__', $label );
	echo str_replace( '</script>', '<\/script>', ob_get_clean() ); // phpcs:ignore WordPress.Security.EscapeOutput -- markup escaped in the row renderer.
	echo '</script>';
	echo '<button type="button" class="button cg-repeater__add">+ ' . esc_html( sprintf( /* translators: %s: row label */ __( 'Add %s', 'coregas' ), strtolower( $label ) ) ) . '</button>';
	echo '</div>';
}

/**
 * Render one repeater row.
 *
 * @param array  $field Repeater field.
 * @param array  $row   Row values.
 * @param string $name  Row name.
 * @param string $id    Row id.
 * @param string $label Row label.
 */
function coregas_render_repeater_row( $field, $row, $name, $id, $label ) {
	echo '<div class="cg-repeater__row">';
	echo '<div class="cg-repeater__bar"><strong>' . esc_html( $label ) . '</strong><span>';
	echo '<button type="button" class="button-link cg-repeater__up" aria-label="' . esc_attr__( 'Move up', 'coregas' ) . '">&uarr;</button> ';
	echo '<button type="button" class="button-link cg-repeater__down" aria-label="' . esc_attr__( 'Move down', 'coregas' ) . '">&darr;</button> ';
	echo '<button type="button" class="button-link cg-repeater__remove">' . esc_html__( 'Remove', 'coregas' ) . '</button></span></div>';
	foreach ( $field['sub'] as $sub ) {
		$val = isset( $row[ $sub['key'] ] ) ? $row[ $sub['key'] ] : ( isset( $sub['default'] ) ? $sub['default'] : '' );
		coregas_render_field_row( $sub, $val, $name . '[' . $sub['key'] . ']', $id . '-' . $sub['key'] );
	}
	echo '</div>';
}

/* -------------------------------------------------------------------------
 * Saving
 * ---------------------------------------------------------------------- */

add_action( 'save_post', 'coregas_save_fields', 10, 2 );
/**
 * Save all schema fields for a post.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function coregas_save_fields( $post_id, $post ) {
	if ( ! isset( $_POST['coregas_fields_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['coregas_fields_nonce'] ) ), 'coregas_save_fields' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	// Only the post this edit form belongs to. Other posts updated during the same request
	// (e.g. sections saved from the page editor) must not receive this form's fields.
	if ( ! isset( $_POST['post_ID'] ) || (int) $_POST['post_ID'] !== (int) $post_id ) {
		return;
	}
	$input = isset( $_POST['cg'] ) && is_array( $_POST['cg'] ) ? wp_unslash( $_POST['cg'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitised per field below.

	foreach ( coregas_groups_for( $post->post_type ) as $group ) {
		foreach ( $group['fields'] as $field ) {
			if ( 'heading' === $field['type'] ) {
				continue;
			}
			$raw = isset( $input[ $field['key'] ] ) ? $input[ $field['key'] ] : ( 'repeater' === $field['type'] ? array() : '' );
			update_post_meta( $post_id, COREGAS_META_PREFIX . $field['key'], coregas_sanitize_field( $field, $raw ) );
		}
	}
}

/**
 * Sanitise a single value according to its field type.
 *
 * @param array $field Field.
 * @param mixed $raw   Raw value.
 * @return mixed
 */
function coregas_sanitize_field( $field, $raw ) {
	switch ( $field['type'] ) {
		case 'textarea':
			return sanitize_textarea_field( (string) $raw );
		case 'html':
			return wp_kses_post( (string) $raw );
		case 'code':
			// Analytics/custom scripts: only users allowed to post unfiltered HTML may store raw markup.
			return current_user_can( 'unfiltered_html' ) ? (string) $raw : wp_kses_post( (string) $raw );
		case 'url':
			return esc_url_raw( trim( (string) $raw ) );
		case 'email':
			return sanitize_email( (string) $raw );
		case 'number':
			return '' === $raw ? '' : (string) ( 0 + $raw );
		case 'color':
			$c = sanitize_hex_color( (string) $raw );
			return $c ? $c : ( isset( $field['default'] ) ? $field['default'] : '' );
		case 'select':
			return array_key_exists( (string) $raw, $field['choices'] ) ? (string) $raw : ( isset( $field['default'] ) ? $field['default'] : '' );
		case 'checkbox':
			return '1' === (string) $raw ? '1' : '0';
		case 'image':
		case 'page':
			return (string) absint( $raw );
		case 'repeater':
			$out = array();
			foreach ( (array) $raw as $i => $row ) {
				if ( '__i__' === (string) $i || ! is_array( $row ) ) {
					continue; // skip the JS template row.
				}
				$clean = array();
				foreach ( $field['sub'] as $sub ) {
					$clean[ $sub['key'] ] = coregas_sanitize_field( $sub, isset( $row[ $sub['key'] ] ) ? $row[ $sub['key'] ] : '' );
				}
				if ( implode( '', array_map( 'strval', $clean ) ) !== '' ) {
					$out[] = $clean;
				}
			}
			return $out;
		default:
			return sanitize_text_field( (string) $raw );
	}
}

/* -------------------------------------------------------------------------
 * Admin assets
 * ---------------------------------------------------------------------- */

add_action( 'admin_enqueue_scripts', 'coregas_admin_assets' );
/**
 * Load field UI assets on edit screens of post types that have schemas.
 *
 * @param string $hook Admin page hook.
 */
function coregas_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! coregas_groups_for( $screen->post_type ) ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'coregas-admin', COREGAS_URI . '/assets/css/admin.css', array(), coregas_asset_ver( 'assets/css/admin.css' ) );
	wp_enqueue_script( 'coregas-admin', COREGAS_URI . '/assets/js/admin-fields.js', array( 'jquery', 'wp-color-picker' ), coregas_asset_ver( 'assets/js/admin-fields.js' ), true );

	// Which meta boxes belong to which section type (Page Sections only).
	$conditions = array();
	$tabs       = array();
	foreach ( coregas_groups_for( $screen->post_type ) as $group ) {
		if ( ! empty( $group['show_if'] ) ) {
			$conditions[ 'cg-group-' . $group['id'] ] = $group['show_if'];
		}
		if ( ! empty( $group['tab'] ) ) {
			$tabs[ 'cg-group-' . $group['id'] ] = $group['tab'];
		}
	}
	wp_localize_script(
		'coregas-admin',
		'coregasFields',
		array(
			'conditions' => $conditions,
			'tabs'       => $tabs,
			'tabsHelp'   => __( 'Choose a tab, change what you need, then press Update (right). All tabs are saved together.', 'coregas' ),
			'chooseText' => __( 'Use this image', 'coregas' ),
		)
	);
}
