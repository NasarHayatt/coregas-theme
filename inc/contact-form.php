<?php
/**
 * Enquiry form (no plugin needed), contact details block and the Enquiries log.
 *
 * Every submission is emailed to the recipient set in Theme Settings AND saved as a
 * private "Enquiry" in the dashboard, so nothing is lost if email fails.
 * Protected by a nonce, a honeypot field and a minimum fill time.
 * Also available anywhere as the shortcode [coregas_contact_form].
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enquiry types from Theme Settings.
 *
 * @return string[]
 */
function coregas_form_types() {
	$types = array();
	foreach ( (array) coregas_setting( 'form_types' ) as $row ) {
		if ( ! empty( $row['label'] ) ) {
			$types[] = (string) $row['label'];
		}
	}
	return $types ? $types : array( 'General enquiry' );
}

/**
 * Index of the enquiry type that opens the gas supply application fields
 * (the first type mentioning supply or connection), or -1.
 *
 * @return int
 */
function coregas_supply_type_index() {
	foreach ( coregas_form_types() as $i => $label ) {
		if ( preg_match( '/supply|connection/i', $label ) ) {
			return (int) $i;
		}
	}
	return -1;
}

/**
 * Options of a comma-separated setting.
 *
 * @param string $key Setting key.
 * @return string[]
 */
function coregas_csv_setting( $key ) {
	return array_values( array_filter( array_map( 'trim', explode( ',', (string) coregas_setting( $key ) ) ) ) );
}

/**
 * Gas supply application fields: key => label setting.
 *
 * @return array
 */
function coregas_supply_fields() {
	return array(
		'site'   => 'form_site_label',
		'sector' => 'form_sector_label',
		'volume' => 'form_volume_label',
		'pipe'   => 'form_pipe_label',
		'term'   => 'form_term_label',
	);
}

/**
 * Contact details list (address, phone, email, hours) + social icons.
 */
function coregas_contact_details() {
	$address = coregas_setting( 'contact_address' );
	$phone   = coregas_setting( 'contact_phone' );
	$email   = coregas_setting( 'contact_email' );
	$hours   = coregas_setting( 'contact_hours' );
	echo '<dl class="contact__list">';
	if ( $address ) {
		echo '<div><dt class="mono">' . esc_html( coregas_label( 'office' ) ) . '</dt><dd>' . nl2br( esc_html( $address ) ) . '</dd></div>';
	}
	if ( $phone ) {
		echo '<div><dt class="mono">' . esc_html( coregas_label( 'phone' ) ) . '</dt><dd><a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ) . '">' . esc_html( $phone ) . '</a></dd></div>';
	}
	if ( $email ) {
		echo '<div><dt class="mono">' . esc_html( coregas_label( 'email' ) ) . '</dt><dd><a href="mailto:' . esc_attr( antispambot( $email ) ) . '">' . esc_html( antispambot( $email ) ) . '</a></dd></div>';
	}
	if ( $hours ) {
		echo '<div><dt class="mono">' . esc_html__( 'Hours', 'coregas' ) . '</dt><dd>' . esc_html( $hours ) . '</dd></div>';
	}
	echo '</dl>';
	$social = coregas_social_links();
	if ( $social ) {
		echo '<div class="contact__social">' . $social . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- fixed SVG markup + escaped URLs.
	}
}

/**
 * Render the form (and the result message after a submission).
 */
function coregas_contact_form() {
	$status = isset( $_GET['cg_sent'] ) ? sanitize_key( wp_unslash( $_GET['cg_sent'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	if ( 'ok' === $status ) {
		echo '<div class="form-note form-note--ok" role="status">' . esc_html( coregas_setting( 'form_success' ) ) . '</div>';
	} elseif ( 'error' === $status ) {
		echo '<div class="form-note form-note--error" role="alert">' . esc_html( coregas_setting( 'form_error' ) ) . '</div>';
	}
	$heading = coregas_setting( 'form_heading' );
	$return  = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );
	$supply  = coregas_supply_type_index();
	$want    = isset( $_GET['enquiry'] ) ? sanitize_key( wp_unslash( $_GET['enquiry'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$chosen  = ( 'supply' === $want && $supply >= 0 ) ? $supply : 0;
	?>
	<?php if ( $heading ) : ?>
		<h3 class="cg-form__title"><?php echo esc_html( $heading ); ?></h3>
	<?php endif; ?>
	<form class="cg-form" id="enquiry" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-supply="<?php echo (int) $supply; ?>">
		<input type="hidden" name="action" value="coregas_contact">
		<input type="hidden" name="cg_return" value="<?php echo esc_url( $return ); ?>">
		<input type="hidden" name="cg_time" value="<?php echo esc_attr( time() ); ?>">
		<?php wp_nonce_field( 'coregas_contact', 'cg_contact_nonce' ); ?>
		<div class="cg-form__hp" aria-hidden="true"><label>Website <input type="text" name="cg_website" tabindex="-1" autocomplete="off"></label></div>

		<div class="cg-form__grid">
			<label><span><?php echo esc_html( coregas_setting( 'form_name_label' ) ); ?> *</span><input type="text" name="cg_name" required maxlength="120" autocomplete="name"></label>
			<label><span><?php echo esc_html( coregas_setting( 'form_company_label' ) ); ?></span><input type="text" name="cg_company" maxlength="160" autocomplete="organization"></label>
			<label><span><?php echo esc_html( coregas_setting( 'form_email_label' ) ); ?> *</span><input type="email" name="cg_email" required maxlength="160" autocomplete="email"></label>
			<label><span><?php echo esc_html( coregas_setting( 'form_phone_label' ) ); ?></span><input type="tel" name="cg_phone" maxlength="40" autocomplete="tel"></label>
			<label class="cg-form__full"><span><?php echo esc_html( coregas_setting( 'form_type_label' ) ); ?></span>
				<select name="cg_type">
					<?php foreach ( coregas_form_types() as $i => $label ) : ?>
						<option value="<?php echo (int) $i; ?>"<?php selected( $chosen, $i ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php if ( $supply >= 0 ) : ?>
				<fieldset class="cg-form__supply"<?php echo $chosen === $supply ? '' : ' hidden'; ?>>
					<legend><?php echo esc_html( coregas_setting( 'form_supply_heading' ) ); ?></legend>
					<label><span><?php echo esc_html( coregas_setting( 'form_site_label' ) ); ?></span><input type="text" name="cg_site" maxlength="160"></label>
					<label><span><?php echo esc_html( coregas_setting( 'form_sector_label' ) ); ?></span><input type="text" name="cg_sector" maxlength="120"></label>
					<label><span><?php echo esc_html( coregas_setting( 'form_volume_label' ) ); ?></span><input type="text" name="cg_volume" maxlength="80"></label>
					<label><span><?php echo esc_html( coregas_setting( 'form_pipe_label' ) ); ?></span>
						<select name="cg_pipe"><option value=""></option><?php foreach ( coregas_csv_setting( 'form_pipe_options' ) as $opt ) : ?><option><?php echo esc_html( $opt ); ?></option><?php endforeach; ?></select>
					</label>
					<label><span><?php echo esc_html( coregas_setting( 'form_term_label' ) ); ?></span>
						<select name="cg_term"><option value=""></option><?php foreach ( coregas_csv_setting( 'form_term_options' ) as $opt ) : ?><option><?php echo esc_html( $opt ); ?></option><?php endforeach; ?></select>
					</label>
				</fieldset>
			<?php endif; ?>
			<label class="cg-form__full"><span><?php echo esc_html( coregas_setting( 'form_message_label' ) ); ?> *</span><textarea name="cg_message" rows="6" required maxlength="5000"></textarea></label>
		</div>
		<button type="submit" class="pill pill--maroon"><span><?php echo esc_html( coregas_setting( 'form_button' ) ); ?></span><i aria-hidden="true">↗</i></button>
	</form>
	<?php
}

add_shortcode(
	'coregas_contact_form',
	function () {
		ob_start();
		coregas_contact_form();
		return ob_get_clean();
	}
);

add_action( 'admin_post_nopriv_coregas_contact', 'coregas_handle_contact' );
add_action( 'admin_post_coregas_contact', 'coregas_handle_contact' );
/**
 * Validate, save and email a submission, then redirect back.
 */
function coregas_handle_contact() {
	$return = isset( $_POST['cg_return'] ) ? esc_url_raw( wp_unslash( $_POST['cg_return'] ) ) : home_url( '/' );
	$return = wp_validate_redirect( $return, home_url( '/' ) );
	$back   = function ( $state ) use ( $return ) {
		wp_safe_redirect( add_query_arg( 'cg_sent', $state, $return ) . '#main' );
		exit;
	};

	if ( ! isset( $_POST['cg_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cg_contact_nonce'] ) ), 'coregas_contact' ) ) {
		$back( 'error' );
	}
	// Honeypot filled or submitted faster than a human could: pretend success, store/send nothing.
	$started = isset( $_POST['cg_time'] ) ? (int) $_POST['cg_time'] : 0;
	if ( ! empty( $_POST['cg_website'] ) || ( time() - $started ) < 3 ) {
		$back( 'ok' );
	}

	$name    = isset( $_POST['cg_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cg_name'] ) ) : '';
	$company = isset( $_POST['cg_company'] ) ? sanitize_text_field( wp_unslash( $_POST['cg_company'] ) ) : '';
	$email   = isset( $_POST['cg_email'] ) ? sanitize_email( wp_unslash( $_POST['cg_email'] ) ) : '';
	$phone   = isset( $_POST['cg_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['cg_phone'] ) ) : '';
	$types   = coregas_form_types();
	$ti      = isset( $_POST['cg_type'] ) ? (int) $_POST['cg_type'] : 0;
	$type    = isset( $types[ $ti ] ) ? $types[ $ti ] : $types[0];
	$message = isset( $_POST['cg_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cg_message'] ) ) : '';

	if ( ! $name || ! is_email( $email ) || ! $message ) {
		$back( 'error' );
	}

	// Gas supply application details (only when that enquiry type was chosen).
	$details = array();
	if ( $ti === coregas_supply_type_index() ) {
		foreach ( coregas_supply_fields() as $k => $label_key ) {
			$v = isset( $_POST[ 'cg_' . $k ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'cg_' . $k ] ) ) : '';
			if ( '' !== $v ) {
				$details[ $k ] = $v;
			}
		}
	}

	// 1. Save it in the dashboard (Enquiries).
	$enquiry = wp_insert_post(
		array(
			'post_type'    => 'cg_enquiry',
			'post_status'  => 'private',
			'post_title'   => $name . ( $company ? ' (' . $company . ')' : '' ),
			'post_content' => $message,
		),
		true
	);
	if ( ! is_wp_error( $enquiry ) ) {
		foreach ( array( 'name' => $name, 'company' => $company, 'email' => $email, 'phone' => $phone, 'type' => $type, 'page' => $return ) as $k => $v ) {
			update_post_meta( $enquiry, '_cg_enq_' . $k, $v );
		}
		foreach ( $details as $k => $v ) {
			update_post_meta( $enquiry, '_cg_enq_' . $k, $v );
		}
	}

	// 2. Email it.
	$to      = coregas_setting( 'form_recipient' );
	$to      = is_email( $to ) ? $to : get_option( 'admin_email' );
	$subject = trim( coregas_setting( 'form_subject' ) . ' ' . $type . ' from ' . $name );
	$body    = "Name: {$name}\nCompany: {$company}\nEmail: {$email}\nPhone: {$phone}\nType: {$type}\nPage: {$return}\n";
	foreach ( $details as $k => $v ) {
		$fields = coregas_supply_fields();
		$body  .= coregas_setting( $fields[ $k ] ) . ': ' . $v . "\n";
	}
	$body .= "\n{$message}\n";
	$sent    = wp_mail( $to, $subject, $body, array( 'Reply-To: ' . $name . ' <' . $email . '>' ) );
	if ( ! is_wp_error( $enquiry ) ) {
		update_post_meta( $enquiry, '_cg_enq_emailed', $sent ? 'yes' : 'no' );
	}

	// Saved enquiries count as success even if the mail server fails.
	$back( ( $sent || ! is_wp_error( $enquiry ) ) ? 'ok' : 'error' );
}

/* -------------------------------------------------------------------------
 * Enquiries log (private post type, admins and editors only)
 * ---------------------------------------------------------------------- */
add_action( 'init', 'coregas_register_enquiries' );
/**
 * Register the Enquiries post type.
 */
function coregas_register_enquiries() {
	register_post_type(
		'cg_enquiry',
		array(
			'labels'          => array_merge( coregas_labels( __( 'Enquiry', 'coregas' ), __( 'Enquiries', 'coregas' ) ), array( 'menu_name' => __( 'Enquiries', 'coregas' ) ) ),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_position'   => 4,
			'menu_icon'       => 'dashicons-email-alt',
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		)
	);
}

add_filter(
	'manage_cg_enquiry_posts_columns',
	function () {
		return array(
			'cb'          => '<input type="checkbox">',
			'title'       => __( 'From', 'coregas' ),
			'cg_type'     => __( 'Type', 'coregas' ),
			'cg_email'    => __( 'Email', 'coregas' ),
			'cg_phone'    => __( 'Phone', 'coregas' ),
			'cg_emailed'  => __( 'Emailed', 'coregas' ),
			'date'        => __( 'Received', 'coregas' ),
		);
	}
);
add_action(
	'manage_cg_enquiry_posts_custom_column',
	function ( $col, $id ) {
		$map = array( 'cg_type' => 'type', 'cg_email' => 'email', 'cg_phone' => 'phone', 'cg_emailed' => 'emailed' );
		if ( isset( $map[ $col ] ) ) {
			$v = (string) get_post_meta( $id, '_cg_enq_' . $map[ $col ], true );
			if ( 'cg_email' === $col && $v ) {
				echo '<a href="mailto:' . esc_attr( $v ) . '">' . esc_html( $v ) . '</a>';
			} else {
				echo esc_html( $v ? $v : '—' );
			}
		}
	},
	10,
	2
);

add_action( 'add_meta_boxes_cg_enquiry', 'coregas_enquiry_box' );
/**
 * Read-only view of an enquiry.
 */
function coregas_enquiry_box() {
	add_meta_box(
		'cg-enquiry',
		__( 'Enquiry', 'coregas' ),
		function ( $post ) {
			$rows = array(
				__( 'Name', 'coregas' )    => get_post_meta( $post->ID, '_cg_enq_name', true ),
				__( 'Company', 'coregas' ) => get_post_meta( $post->ID, '_cg_enq_company', true ),
				__( 'Email', 'coregas' )   => get_post_meta( $post->ID, '_cg_enq_email', true ),
				__( 'Phone', 'coregas' )   => get_post_meta( $post->ID, '_cg_enq_phone', true ),
				__( 'Type', 'coregas' )    => get_post_meta( $post->ID, '_cg_enq_type', true ),
				coregas_setting( 'form_site_label' )   => get_post_meta( $post->ID, '_cg_enq_site', true ),
				coregas_setting( 'form_sector_label' ) => get_post_meta( $post->ID, '_cg_enq_sector', true ),
				coregas_setting( 'form_volume_label' ) => get_post_meta( $post->ID, '_cg_enq_volume', true ),
				coregas_setting( 'form_pipe_label' )   => get_post_meta( $post->ID, '_cg_enq_pipe', true ),
				coregas_setting( 'form_term_label' )   => get_post_meta( $post->ID, '_cg_enq_term', true ),
				__( 'Sent from', 'coregas' ) => get_post_meta( $post->ID, '_cg_enq_page', true ),
				__( 'Received', 'coregas' )  => get_the_date( 'j F Y, H:i', $post ),
			);
			echo '<table class="widefat striped"><tbody>';
			foreach ( $rows as $label => $val ) {
				if ( '' === (string) $val ) {
					continue;
				}
				echo '<tr><th style="width:160px">' . esc_html( $label ) . '</th><td>' . esc_html( (string) $val ) . '</td></tr>';
			}
			echo '<tr><th>' . esc_html__( 'Requirements', 'coregas' ) . '</th><td>' . nl2br( esc_html( $post->post_content ) ) . '</td></tr>';
			echo '</tbody></table>';
			$email = get_post_meta( $post->ID, '_cg_enq_email', true );
			if ( $email ) {
				echo '<p><a class="button button-primary" href="mailto:' . esc_attr( $email ) . '">' . esc_html__( 'Reply by email', 'coregas' ) . '</a></p>';
			}
		},
		'cg_enquiry',
		'normal',
		'high'
	);
}
