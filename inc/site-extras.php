<?php
/**
 * Launch helpers: staging kept out of search engines, email sending (SMTP) with a
 * test button, default favicon, security headers, hidden PHP errors and menu links
 * that hide themselves while their list is empty.
 * All switches live in Theme Settings.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Search engines: only the live domain is indexed.
 * ---------------------------------------------------------------------- */

/**
 * Is this the live website (domain set in Theme Settings → SEO & sharing)?
 *
 * @return bool
 */
function coregas_is_live_site() {
	$live = strtolower( trim( (string) coregas_setting( 'seo_live_domain' ) ) );
	$live = preg_replace( '#^https?://#', '', rtrim( $live, '/' ) );
	$host = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	if ( ! $live ) {
		return true;
	}
	return $host === $live || $host === 'www.' . $live || 'www.' . $host === $live;
}

/**
 * Should search engines be kept away from this copy of the site?
 *
 * @return bool
 */
function coregas_hide_from_search() {
	return '1' === coregas_setting( 'seo_hide_staging' ) && ! coregas_is_live_site();
}

add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( coregas_hide_from_search() ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
			unset( $robots['max-image-preview'] );
		}
		return $robots;
	}
);

add_filter(
	'robots_txt',
	function ( $output ) {
		if ( coregas_hide_from_search() ) {
			return "User-agent: *\nDisallow: /\n";
		}
		return $output;
	},
	20
);

add_action(
	'send_headers',
	function () {
		if ( coregas_hide_from_search() ) {
			header( 'X-Robots-Tag: noindex, nofollow', true );
		}
		if ( '1' === coregas_setting( 'sec_headers' ) && ! is_admin() ) {
			header( 'X-Content-Type-Options: nosniff' );
			header( 'X-Frame-Options: SAMEORIGIN' );
			header( 'Referrer-Policy: strict-origin-when-cross-origin' );
			header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
		}
	}
);

/* -------------------------------------------------------------------------
 * PHP error messages never reach visitors.
 * ---------------------------------------------------------------------- */
add_action(
	'init',
	function () {
		if ( '1' === coregas_setting( 'sec_hide_errors' ) && ! ( is_user_logged_in() && current_user_can( 'manage_options' ) ) ) {
			@ini_set( 'display_errors', '0' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors, WordPress.PHP.IniSet
		}
	},
	0
);

/* -------------------------------------------------------------------------
 * Email: send through a real mail server (SMTP) when configured.
 * ---------------------------------------------------------------------- */
add_action(
	'phpmailer_init',
	function ( $mailer ) {
		if ( '1' !== coregas_setting( 'smtp_on' ) || ! coregas_setting( 'smtp_host' ) ) {
			return;
		}
		$mailer->isSMTP();
		$mailer->Host       = (string) coregas_setting( 'smtp_host' ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$mailer->Port       = (int) coregas_setting( 'smtp_port' ) ? (int) coregas_setting( 'smtp_port' ) : 587; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$secure             = (string) coregas_setting( 'smtp_secure' );
		$mailer->SMTPSecure = in_array( $secure, array( 'tls', 'ssl' ), true ) ? $secure : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		$mailer->SMTPAutoTLS = 'none' !== $secure; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		if ( coregas_setting( 'smtp_user' ) ) {
			$mailer->SMTPAuth = true; // phpcs:ignore WordPress.NamingConventions.ValidVariableName
			$mailer->Username = (string) coregas_setting( 'smtp_user' ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
			$mailer->Password = (string) coregas_setting( 'smtp_pass' ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName
		}
	}
);

add_filter(
	'wp_mail_from',
	function ( $from ) {
		$custom = coregas_setting( 'smtp_from_email' );
		return is_email( $custom ) ? $custom : $from;
	}
);

add_filter(
	'wp_mail_from_name',
	function ( $name ) {
		$custom = trim( (string) coregas_setting( 'smtp_from_name' ) );
		return $custom ? $custom : $name;
	}
);

add_action( 'add_meta_boxes_cg_settings', 'coregas_email_test_box' );
/**
 * "Send a test email" box on the Theme Settings screen.
 */
function coregas_email_test_box() {
	add_meta_box(
		'cg-email-test',
		__( 'Test email sending', 'coregas' ),
		function () {
			$to  = coregas_setting( 'form_recipient' );
			$to  = is_email( $to ) ? $to : get_option( 'admin_email' );
			$url = wp_nonce_url( admin_url( 'admin-post.php?action=coregas_test_email' ), 'coregas_test_email' );
			$res = isset( $_GET['cg_mail'] ) ? sanitize_key( wp_unslash( $_GET['cg_mail'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			if ( 'ok' === $res ) {
				echo '<p style="color:#0a7a2f"><strong>' . esc_html__( 'Test email sent. Check the inbox (and spam folder).', 'coregas' ) . '</strong></p>';
			} elseif ( 'fail' === $res ) {
				$err = get_transient( 'coregas_mail_error' );
				echo '<p style="color:#b32d2e"><strong>' . esc_html__( 'Sending failed.', 'coregas' ) . '</strong> ' . esc_html( (string) $err ) . '</p>';
			}
			/* translators: %s: email address */
			echo '<p>' . esc_html( sprintf( __( 'Sends a short test message to %s (the enquiry form recipient). Save your settings first.', 'coregas' ), $to ) ) . '</p>';
			echo '<p><a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Send a test email', 'coregas' ) . '</a></p>';
		},
		'cg_settings',
		'side',
		'low'
	);
}

add_action( 'admin_post_coregas_test_email', 'coregas_send_test_email' );
/**
 * Send the test email and return to Theme Settings.
 */
function coregas_send_test_email() {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'coregas_test_email' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'coregas' ) );
	}
	$to = coregas_setting( 'form_recipient' );
	$to = is_email( $to ) ? $to : get_option( 'admin_email' );
	add_action(
		'wp_mail_failed',
		function ( $error ) {
			set_transient( 'coregas_mail_error', $error->get_error_message(), 5 * MINUTE_IN_SECONDS );
		}
	);
	$ok   = wp_mail( $to, __( 'Test email from your website', 'coregas' ), __( 'If you can read this, the website can send email (enquiry form notifications will arrive).', 'coregas' ) );
	$back = get_edit_post_link( coregas_settings_id(), 'url' );
	wp_safe_redirect( add_query_arg( 'cg_mail', $ok ? 'ok' : 'fail', $back ? $back : admin_url() ) );
	exit;
}

/* -------------------------------------------------------------------------
 * Favicon: bundled icon until one is chosen in Theme Settings.
 * ---------------------------------------------------------------------- */
add_action(
	'wp_head',
	function () {
		if ( get_option( 'site_icon' ) ) {
			return; // WordPress prints the chosen icon.
		}
		$icon = COREGAS_URI . '/assets/img/favicon-512.png';
		echo '<link rel="icon" href="' . esc_url( COREGAS_URI . '/assets/img/favicon-32.png' ) . '" sizes="32x32">' . "\n";
		echo '<link rel="icon" href="' . esc_url( $icon ) . '" sizes="512x512">' . "\n";
		echo '<link rel="apple-touch-icon" href="' . esc_url( COREGAS_URI . '/assets/img/favicon-180.png' ) . '">' . "\n";
	},
	2
);
add_action(
	'login_head',
	function () {
		if ( ! get_option( 'site_icon' ) ) {
			echo '<link rel="icon" href="' . esc_url( COREGAS_URI . '/assets/img/favicon-32.png' ) . '" sizes="32x32">' . "\n";
		}
	}
);

/* -------------------------------------------------------------------------
 * Menu links to empty lists hide themselves (Industries, News).
 * ---------------------------------------------------------------------- */

/**
 * Does this menu item point at a list that has nothing published yet?
 *
 * @param WP_Post $item Menu item.
 * @return bool
 */
function coregas_menu_item_is_empty_list( $item ) {
	if ( '1' !== coregas_setting( 'menu_hide_empty' ) ) {
		return false;
	}
	if ( 'post_type' === $item->type && 'page' === $item->object && (int) $item->object_id === (int) get_option( 'page_for_posts' ) ) {
		return 0 === (int) wp_count_posts( 'post' )->publish;
	}
	$archives = array( 'cg_industry', 'cg_service', 'cg_licence' );
	if ( 'post_type_archive' === $item->type && in_array( $item->object, $archives, true ) ) {
		return 0 === (int) wp_count_posts( $item->object )->publish;
	}
	if ( 'custom' === $item->type ) {
		foreach ( $archives as $pt ) {
			$link = get_post_type_archive_link( $pt );
			if ( $link && untrailingslashit( $link ) === untrailingslashit( $item->url ) ) {
				return 0 === (int) wp_count_posts( $pt )->publish;
			}
		}
	}
	return false;
}
