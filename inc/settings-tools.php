<?php
/**
 * Theme Settings tools: version history with restore, export / import,
 * reset a section to defaults, branded login page, security switches.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

const COREGAS_HISTORY_OPTION = 'coregas_settings_history';
const COREGAS_HISTORY_MAX    = 20;

/**
 * Every storable Theme Settings field, keyed by field key.
 *
 * @return array
 */
function coregas_settings_fields() {
	$out = array();
	foreach ( coregas_groups_for( 'cg_settings' ) as $group ) {
		foreach ( $group['fields'] as $field ) {
			if ( 'heading' !== $field['type'] ) {
				$out[ $field['key'] ] = $field;
			}
		}
	}
	return $out;
}

/**
 * Current saved values (only keys that have been saved).
 *
 * @return array
 */
function coregas_settings_snapshot() {
	$id   = coregas_settings_id();
	$vals = array();
	if ( ! $id ) {
		return $vals;
	}
	foreach ( array_keys( coregas_settings_fields() ) as $key ) {
		$meta = COREGAS_META_PREFIX . $key;
		if ( metadata_exists( 'post', $id, $meta ) ) {
			$vals[ $key ] = get_post_meta( $id, $meta, true );
		}
	}
	return $vals;
}

/**
 * Replace all saved values with a snapshot (unknown keys ignored, values re-sanitised).
 *
 * @param array $values Values.
 * @return int Number of fields written.
 */
function coregas_settings_apply( $values ) {
	$id     = coregas_settings_id( true );
	$fields = coregas_settings_fields();
	$n      = 0;
	foreach ( $fields as $key => $field ) {
		$meta = COREGAS_META_PREFIX . $key;
		if ( array_key_exists( $key, $values ) ) {
			update_post_meta( $id, $meta, coregas_sanitize_field( $field, $values[ $key ] ) );
			$n++;
		} else {
			delete_post_meta( $id, $meta ); // back to its default.
		}
	}
	return $n;
}

/**
 * Store the current values as a history entry.
 *
 * @param string $note What is about to happen.
 */
function coregas_settings_remember( $note ) {
	$snap = coregas_settings_snapshot();
	if ( ! $snap ) {
		return;
	}
	$history = get_option( COREGAS_HISTORY_OPTION, array() );
	$history = is_array( $history ) ? $history : array();
	array_unshift(
		$history,
		array(
			'time'   => time(),
			'user'   => wp_get_current_user()->display_name,
			'note'   => $note,
			'values' => $snap,
		)
	);
	update_option( COREGAS_HISTORY_OPTION, array_slice( $history, 0, COREGAS_HISTORY_MAX ), false );
}

add_action( 'pre_post_update', 'coregas_settings_before_save', 10, 1 );
/**
 * Keep the previous version every time Theme Settings is saved.
 *
 * @param int $post_id Post being updated.
 */
function coregas_settings_before_save( $post_id ) {
	if ( 'cg_settings' === get_post_type( $post_id ) && isset( $_POST['coregas_fields_nonce'] ) && current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- verified again on save; this only reads current values.
		coregas_settings_remember( __( 'Before saving Theme Settings', 'coregas' ) );
	}
}

// Runs on `save_post` after the normal field save (priority 10): `save_post_cg_settings`
// would fire before it, and the form's values would then overwrite the reset.
add_action( 'save_post', 'coregas_settings_reset_group', 20 );
/**
 * "Reset this section to defaults" buttons on the settings screen.
 *
 * @param int $post_id Settings post.
 */
function coregas_settings_reset_group( $post_id ) {
	if ( 'cg_settings' !== get_post_type( $post_id ) || empty( $_POST['cg_reset_group'] ) || ! isset( $_POST['coregas_fields_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['coregas_fields_nonce'] ) ), 'coregas_save_fields' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$group_id = sanitize_key( wp_unslash( $_POST['cg_reset_group'] ) );
	foreach ( coregas_groups_for( 'cg_settings' ) as $group ) {
		if ( $group['id'] === $group_id ) {
			foreach ( $group['fields'] as $field ) {
				delete_post_meta( $post_id, COREGAS_META_PREFIX . $field['key'] );
			}
		}
	}
}

/* -------------------------------------------------------------------------
 * Backup & restore screen
 * ---------------------------------------------------------------------- */

add_action( 'admin_menu', 'coregas_settings_tools_menu', 20 );
/**
 * Submenus under Theme Settings.
 */
function coregas_settings_tools_menu() {
	add_submenu_page( 'coregas-settings', __( 'Theme Settings', 'coregas' ), __( 'All settings', 'coregas' ), 'manage_options', 'coregas-settings', '__return_null' );
	add_submenu_page( 'coregas-settings', __( 'Backup & restore', 'coregas' ), __( 'Backup & restore', 'coregas' ), 'manage_options', 'coregas-backup', 'coregas_render_backup_page' );
}

/**
 * Backup & restore screen.
 */
function coregas_render_backup_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$history = get_option( COREGAS_HISTORY_OPTION, array() );
	$notice  = isset( $_GET['cg_done'] ) ? sanitize_key( wp_unslash( $_GET['cg_done'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$msgs    = array(
		'restored' => __( 'Settings restored. The version before the restore was kept in the history below.', 'coregas' ),
		'imported' => __( 'Settings imported. The previous settings were kept in the history below.', 'coregas' ),
		'badfile'  => __( 'That file is not a Core Gas settings export.', 'coregas' ),
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Theme Settings: backup & restore', 'coregas' ); ?></h1>
		<?php if ( isset( $msgs[ $notice ] ) ) : ?>
			<div class="notice <?php echo 'badfile' === $notice ? 'notice-error' : 'notice-success'; ?> is-dismissible"><p><?php echo esc_html( $msgs[ $notice ] ); ?></p></div>
		<?php endif; ?>

		<div class="card" style="max-width:none">
			<h2><?php esc_html_e( 'Download a backup', 'coregas' ); ?></h2>
			<p><?php esc_html_e( 'Saves every Theme Settings value (colours, fonts, header, footer, contact, form, SEO, wording, analytics…) to a file. Use it as a backup, or to copy settings from staging to the live site.', 'coregas' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="coregas_export_settings">
				<?php wp_nonce_field( 'coregas_export_settings' ); ?>
				<?php submit_button( __( 'Download settings file', 'coregas' ), 'primary', 'submit', false ); ?>
			</form>
		</div>

		<div class="card" style="max-width:none">
			<h2><?php esc_html_e( 'Import a settings file', 'coregas' ); ?></h2>
			<p><?php esc_html_e( 'Replaces the current Theme Settings with the file. The current settings are kept in the history first, so this can be undone. Photos are stored by Media Library ID: when importing to a different site, choose the logo and photos again afterwards.', 'coregas' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="coregas_import_settings">
				<?php wp_nonce_field( 'coregas_import_settings' ); ?>
				<input type="file" name="cg_settings_file" accept=".json,application/json" required>
				<?php submit_button( __( 'Import', 'coregas' ), 'secondary', 'submit', false, array( 'onclick' => "return confirm('" . esc_js( __( 'Replace the current Theme Settings with this file?', 'coregas' ) ) . "');" ) ); ?>
			</form>
		</div>

		<h2><?php esc_html_e( 'Version history', 'coregas' ); ?></h2>
		<p><?php echo esc_html( sprintf( /* translators: %d: count */ __( 'A copy is kept automatically every time Theme Settings is saved, imported or restored (latest %d).', 'coregas' ), COREGAS_HISTORY_MAX ) ); ?></p>
		<?php if ( ! $history ) : ?>
			<p class="description"><?php esc_html_e( 'No earlier versions yet. They appear here after the first save.', 'coregas' ); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead><tr><th><?php esc_html_e( 'Saved', 'coregas' ); ?></th><th><?php esc_html_e( 'By', 'coregas' ); ?></th><th><?php esc_html_e( 'Kept because', 'coregas' ); ?></th><th></th></tr></thead>
				<tbody>
				<?php foreach ( $history as $i => $h ) : ?>
					<tr>
						<td><?php echo esc_html( wp_date( 'j M Y, H:i', (int) $h['time'] ) ); ?></td>
						<td><?php echo esc_html( $h['user'] ); ?></td>
						<td><?php echo esc_html( $h['note'] ); ?></td>
						<td>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
								<input type="hidden" name="action" value="coregas_restore_settings">
								<input type="hidden" name="cg_index" value="<?php echo (int) $i; ?>">
								<?php wp_nonce_field( 'coregas_restore_settings' ); ?>
								<button class="button" onclick="return confirm('<?php echo esc_js( __( 'Restore Theme Settings to this version?', 'coregas' ) ); ?>');"><?php esc_html_e( 'Restore', 'coregas' ); ?></button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}

add_action( 'admin_post_coregas_export_settings', 'coregas_export_settings' );
/**
 * Download all settings as JSON.
 */
function coregas_export_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'coregas' ) );
	}
	check_admin_referer( 'coregas_export_settings' );
	$data = array(
		'theme'    => 'coregas',
		'version'  => COREGAS_VERSION,
		'exported' => gmdate( 'c' ),
		'site'     => home_url( '/' ),
		'settings' => coregas_settings_snapshot(),
	);
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=core-gas-settings-' . gmdate( 'Y-m-d-His' ) . '.json' );
	echo wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); // phpcs:ignore WordPress.Security.EscapeOutput -- JSON download.
	exit;
}

add_action( 'admin_post_coregas_import_settings', 'coregas_import_settings' );
/**
 * Import a JSON export.
 */
function coregas_import_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'coregas' ) );
	}
	check_admin_referer( 'coregas_import_settings' );
	$back = admin_url( 'admin.php?page=coregas-backup' );
	$file = isset( $_FILES['cg_settings_file']['tmp_name'] ) ? $_FILES['cg_settings_file']['tmp_name'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- uploaded temp path, only read.
	$json = ( $file && is_uploaded_file( $file ) && filesize( $file ) < 2 * MB_IN_BYTES ) ? json_decode( (string) file_get_contents( $file ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! is_array( $json ) || ( isset( $json['theme'] ) ? $json['theme'] : '' ) !== 'coregas' || ! isset( $json['settings'] ) || ! is_array( $json['settings'] ) ) {
		wp_safe_redirect( add_query_arg( 'cg_done', 'badfile', $back ) );
		exit;
	}
	coregas_settings_remember( __( 'Before importing a settings file', 'coregas' ) );
	coregas_settings_apply( $json['settings'] );
	wp_safe_redirect( add_query_arg( 'cg_done', 'imported', $back ) );
	exit;
}

add_action( 'admin_post_coregas_restore_settings', 'coregas_restore_settings' );
/**
 * Restore a history entry.
 */
function coregas_restore_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'coregas' ) );
	}
	check_admin_referer( 'coregas_restore_settings' );
	$history = get_option( COREGAS_HISTORY_OPTION, array() );
	$i       = isset( $_POST['cg_index'] ) ? (int) $_POST['cg_index'] : -1;
	if ( isset( $history[ $i ]['values'] ) ) {
		$values = $history[ $i ]['values'];
		coregas_settings_remember( __( 'Before restoring an earlier version', 'coregas' ) );
		coregas_settings_apply( $values );
	}
	wp_safe_redirect( add_query_arg( 'cg_done', 'restored', admin_url( 'admin.php?page=coregas-backup' ) ) );
	exit;
}

/* -------------------------------------------------------------------------
 * Branded login page
 * ---------------------------------------------------------------------- */

add_action( 'login_enqueue_scripts', 'coregas_login_brand' );
/**
 * Core Gas logo and colours on wp-login.php.
 */
function coregas_login_brand() {
	if ( '1' !== coregas_setting( 'login_brand' ) ) {
		return;
	}
	$logo_id = (int) coregas_setting( 'logo' );
	$logo    = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : COREGAS_URI . '/assets/img/core-gas-logo.webp';
	$maroon  = sanitize_hex_color( (string) coregas_setting( 'color_maroon' ) );
	$accent  = sanitize_hex_color( (string) coregas_setting( 'color_primary' ) );
	$maroon  = $maroon ? $maroon : '#48050b';
	$accent  = $accent ? $accent : '#e36f00';
	?>
	<style>
		body.login { background: <?php echo esc_html( $maroon ); ?>; }
		#login h1 a { background: #fff url('<?php echo esc_url( $logo ); ?>') center / 80% auto no-repeat; width: 280px; height: 120px; border-radius: 14px; }
		.login form { border: 0; border-radius: 14px; box-shadow: 0 20px 50px rgba(0,0,0,.25); }
		.login #backtoblog a, .login #nav a, .login .privacy-policy-link { color: #f4f1ee !important; }
		.wp-core-ui .button-primary { background: <?php echo esc_html( $maroon ); ?>; border-color: <?php echo esc_html( $maroon ); ?>; }
		.wp-core-ui .button-primary:hover, .wp-core-ui .button-primary:focus { background: <?php echo esc_html( $accent ); ?>; border-color: <?php echo esc_html( $accent ); ?>; color: #120d0d; }
		.login input[type=text]:focus, .login input[type=password]:focus { border-color: <?php echo esc_html( $accent ); ?>; box-shadow: 0 0 0 1px <?php echo esc_html( $accent ); ?>; }
	</style>
	<?php
}
add_filter(
	'login_headerurl',
	function ( $url ) {
		return '1' === coregas_setting( 'login_brand' ) ? home_url( '/' ) : $url;
	}
);
add_filter(
	'login_headertext',
	function ( $text ) {
		return '1' === coregas_setting( 'login_brand' ) ? coregas_setting( 'company_name' ) : $text;
	}
);

/* -------------------------------------------------------------------------
 * Security switches (Theme Settings → Security)
 * ---------------------------------------------------------------------- */

add_filter(
	'xmlrpc_enabled',
	function ( $on ) {
		return '1' === coregas_setting( 'sec_xmlrpc_off' ) ? false : $on;
	}
);
add_filter(
	'wp_headers',
	function ( $headers ) {
		if ( '1' === coregas_setting( 'sec_xmlrpc_off' ) ) {
			unset( $headers['X-Pingback'] );
		}
		return $headers;
	}
);
add_action(
	'init',
	function () {
		if ( '1' === coregas_setting( 'sec_hide_version' ) ) {
			remove_action( 'wp_head', 'wp_generator' );
			add_filter( 'the_generator', '__return_empty_string' );
		}
	}
);
add_filter(
	'map_meta_cap',
	function ( $caps, $cap ) {
		if ( in_array( $cap, array( 'edit_themes', 'edit_plugins', 'edit_files' ), true ) && '1' === coregas_setting( 'sec_lock_editor' ) ) {
			return array( 'do_not_allow' );
		}
		return $caps;
	},
	10,
	2
);
