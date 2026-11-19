<?php
/**
 * Dashboard: "Edit your website" guide, launch checklist and latest enquiries.
 * Also a calmer admin menu and styles for the guide.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_dashboard_setup', 'coregas_dashboard_widgets' );
/**
 * Register the theme's dashboard widgets and put them first.
 */
function coregas_dashboard_widgets() {
	wp_add_dashboard_widget( 'coregas_guide', __( 'Edit your website', 'coregas' ), 'coregas_render_dashboard_guide' );
	wp_add_dashboard_widget( 'coregas_checklist', __( 'Website checklist', 'coregas' ), 'coregas_render_checklist' );
	wp_add_dashboard_widget( 'coregas_enquiries', __( 'Latest enquiries', 'coregas' ), 'coregas_render_enquiries_widget' );

	global $wp_meta_boxes;
	$normal = $wp_meta_boxes['dashboard']['normal']['core'];
	$first  = array();
	foreach ( array( 'coregas_guide', 'coregas_checklist', 'coregas_enquiries' ) as $id ) {
		if ( isset( $normal[ $id ] ) ) {
			$first[ $id ] = $normal[ $id ];
			unset( $normal[ $id ] );
		}
	}
	// Guide on the left, checklist and enquiries on the right.
	$wp_meta_boxes['dashboard']['normal']['core'] = array_merge( array( 'coregas_guide' => $first['coregas_guide'] ), $normal ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	$side = isset( $wp_meta_boxes['dashboard']['side']['core'] ) ? $wp_meta_boxes['dashboard']['side']['core'] : array();
	$wp_meta_boxes['dashboard']['side']['core'] = array_merge( array( 'coregas_checklist' => $first['coregas_checklist'], 'coregas_enquiries' => $first['coregas_enquiries'] ), $side ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
}

/**
 * Button link.
 *
 * @param string $url   URL.
 * @param string $label Label.
 * @param string $class Extra class.
 * @return string
 */
function coregas_admin_button( $url, $label, $class = '' ) {
	return '<a class="button ' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
}

/**
 * Guide content.
 */
function coregas_render_dashboard_guide() {
	$front     = (int) get_option( 'page_on_front' );
	$pages     = array();
	$locations = get_nav_menu_locations();
	if ( ! empty( $locations['primary'] ) ) {
		foreach ( (array) wp_get_nav_menu_items( $locations['primary'] ) as $item ) {
			if ( 'page' === $item->object && ! isset( $pages[ (int) $item->object_id ] ) ) {
				$pages[ (int) $item->object_id ] = get_post( (int) $item->object_id );
			}
		}
	}
	foreach ( get_pages( array( 'sort_column' => 'post_title', 'post_status' => 'publish' ) ) as $p ) {
		if ( ! isset( $pages[ $p->ID ] ) ) {
			$pages[ $p->ID ] = $p;
		}
	}
	$pages = array_filter( $pages );
	$count = function ( $type ) {
		$c = wp_count_posts( $type );
		return isset( $c->publish ) ? (int) $c->publish : 0;
	};
	?>
	<div class="cg-guide">
		<p class="cg-guide__lead"><?php esc_html_e( 'To change anything you see on a page, open that page: its banner, every section, text, photos and buttons are on one screen. You can also add new sections there.', 'coregas' ); ?></p>

		<h3><?php esc_html_e( 'Pages', 'coregas' ); ?></h3>
		<table class="cg-guide__table">
			<?php
			foreach ( $pages as $p ) :
				if ( in_array( $p->post_name, array( 'sample-page' ), true ) ) {
					continue;
				}
				$n    = count( coregas_sections_for_page_any( $p->ID ) );
				$name = (int) $p->ID === $front ? __( 'Home page', 'coregas' ) : $p->post_title;
				?>
				<tr>
					<td><?php echo coregas_admin_button( get_edit_post_link( $p->ID ), $name ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
					<td>
						<?php
						/* translators: %d: number of sections */
						echo esc_html( $n ? sprintf( _n( '%d section', '%d sections', $n, 'coregas' ), $n ) : __( 'Text page', 'coregas' ) );
						?>
						· <a href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>" target="_blank"><?php esc_html_e( 'View', 'coregas' ); ?></a>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
		<p><?php echo coregas_admin_button( admin_url( 'post-new.php?post_type=page' ), __( '+ Create a new page', 'coregas' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>

		<h3><?php esc_html_e( 'Whole website', 'coregas' ); ?></h3>
		<table class="cg-guide__table">
			<tr><td><?php echo coregas_admin_button( admin_url( 'admin.php?page=coregas-settings' ), __( 'Theme Settings', 'coregas' ), 'button-primary' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td><td><?php esc_html_e( 'Logo, favicon, colours, fonts, layout, header, footer, social links, contact details, enquiry form, SEO, wording, 404 page, analytics, motion.', 'coregas' ); ?></td></tr>
			<tr><td><?php echo coregas_admin_button( admin_url( 'admin.php?page=coregas-backup' ), __( 'Backup & restore', 'coregas' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td><td><?php esc_html_e( 'Download or import Theme Settings, and restore any earlier version.', 'coregas' ); ?></td></tr>
			<tr><td><?php echo coregas_admin_button( admin_url( 'nav-menus.php' ), __( 'Menus', 'coregas' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td><td><?php esc_html_e( 'Header menu, the four footer columns (column heading = menu name) and the legal links.', 'coregas' ); ?></td></tr>
			<tr><td><?php echo coregas_admin_button( admin_url( 'upload.php' ), __( 'Media Library', 'coregas' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td><td><?php esc_html_e( 'Upload photos, then pick them anywhere with "Choose image".', 'coregas' ); ?></td></tr>
			<tr><td><?php echo coregas_admin_button( admin_url( 'edit.php?post_type=cg_enquiry' ), __( 'Enquiries', 'coregas' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td><td><?php esc_html_e( 'Every message sent through the website form.', 'coregas' ); ?></td></tr>
		</table>

		<h3><?php esc_html_e( 'Lists shown on pages', 'coregas' ); ?></h3>
		<table class="cg-guide__table">
			<tr><td><?php echo coregas_admin_button( admin_url( 'edit.php?post_type=cg_service' ), __( 'Supply Services', 'coregas' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td><td><?php echo esc_html( sprintf( /* translators: %d: count */ __( '%d published. Source, Transport, Deliver, Serve on the Gas Supply page. Photo = "Featured image".', 'coregas' ), $count( 'cg_service' ) ) ); ?></td></tr>
			<tr><td><?php echo coregas_admin_button( admin_url( 'edit.php?post_type=cg_licence' ), __( 'Licences', 'coregas' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td><td><?php echo esc_html( sprintf( /* translators: %d: count */ __( '%d published. Licence record on the Regulation page.', 'coregas' ), $count( 'cg_licence' ) ) ); ?></td></tr>
			<tr><td><?php echo coregas_admin_button( admin_url( 'edit.php?post_type=cg_industry' ), __( 'Industries', 'coregas' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td><td><?php echo esc_html( sprintf( /* translators: %d: count */ __( '%d published. Show them on any page with a "List of items" section.', 'coregas' ), $count( 'cg_industry' ) ) ); ?></td></tr>
			<tr><td><?php echo coregas_admin_button( admin_url( 'edit.php' ), __( 'News', 'coregas' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td><td><?php echo esc_html( sprintf( /* translators: %d: count */ __( '%d published news posts.', 'coregas' ), $count( 'post' ) ) ); ?></td></tr>
		</table>

		<h3><?php esc_html_e( 'Section types you can add to any page', 'coregas' ); ?></h3>
		<p class="cg-guide__types">
			<?php
			echo esc_html( implode( ' · ', array_map( 'wp_strip_all_tags', coregas_section_type_labels() ) ) );
			?>
		</p>
		<p class="description"><?php esc_html_e( 'Tip: while viewing the website logged in, use "Edit Page" in the black top bar to jump straight to that page.', 'coregas' ); ?></p>
	</div>
	<?php
}

/**
 * Launch / health checklist.
 */
function coregas_render_checklist() {
	$settings = admin_url( 'admin.php?page=coregas-settings' );
	$home     = (int) get_option( 'page_on_front' );
	$hero_img = false;
	foreach ( coregas_get_sections( $home ) as $s ) {
		if ( 'hero' === coregas_meta( $s->ID, 'section_type' ) && (int) coregas_meta( $s->ID, 'hero_image' ) ) {
			$hero_img = true;
		}
	}
	$no_photo = get_posts(
		array(
			'post_type'      => 'cg_service',
			'posts_per_page' => 50,
			'fields'         => 'ids',
			'meta_query'     => array( array( 'key' => '_thumbnail_id', 'compare' => 'NOT EXISTS' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	$legal_todo = 0;
	foreach ( array( 'legal-notice', 'accessibility' ) as $slug ) {
		$p = get_page_by_path( $slug );
		if ( $p && false !== stripos( $p->post_content, 'to be added' ) ) {
			$legal_todo++;
		}
	}
	$privacy = (int) get_option( 'wp_page_for_privacy_policy' );

	$checks = array(
		array( coregas_setting( 'contact_phone' ) && coregas_setting( 'contact_email' ) && coregas_setting( 'contact_address' ), __( 'Contact details (address, phone, email)', 'coregas' ), $settings ),
		array( (bool) is_email( coregas_setting( 'form_recipient' ) ), __( 'Enquiry form email recipient', 'coregas' ), $settings ),
		array( coregas_setting( 'ga4_id' ) || coregas_setting( 'gtm_id' ), __( 'Google Analytics or Tag Manager', 'coregas' ), $settings ),
		array( (bool) get_option( 'site_icon' ), __( 'Favicon (browser tab icon)', 'coregas' ), $settings ),
		array( (bool) coregas_setting( 'seo_image' ), __( 'Image for links shared on WhatsApp / LinkedIn', 'coregas' ), $settings ),
		array( coregas_setting( 'social_linkedin' ) || coregas_setting( 'social_facebook' ) || coregas_setting( 'social_x' ), __( 'Social media profiles', 'coregas' ), $settings ),
		array( $hero_img, __( 'Own photo in the home hero (placeholder in use)', 'coregas' ), $home ? get_edit_post_link( $home ) : admin_url( 'edit.php?post_type=page' ) ),
		array( (bool) coregas_setting( 'banner_default' ), __( 'Default page banner photo (placeholders in use)', 'coregas' ), $settings ),
		array( ! $no_photo, sprintf( /* translators: %d: count */ __( 'Photos for supply services (%d without a photo)', 'coregas' ), count( $no_photo ) ), admin_url( 'edit.php?post_type=cg_service' ) ),
		array( 0 === $legal_todo, __( 'Legal notice and accessibility text written', 'coregas' ), admin_url( 'edit.php?post_type=page' ) ),
		array( $privacy && 'publish' === get_post_status( $privacy ), __( 'Privacy policy page published', 'coregas' ), admin_url( 'options-privacy.php' ) ),
		array( (bool) get_option( 'permalink_structure' ), __( 'Readable page addresses (permalinks)', 'coregas' ), admin_url( 'options-permalink.php' ) ),
	);
	$done = count( array_filter( wp_list_pluck( $checks, 0 ) ) );
	?>
	<div class="cg-check-list">
		<p><strong><?php echo esc_html( sprintf( /* translators: 1: done, 2: total */ __( '%1$d of %2$d done', 'coregas' ), $done, count( $checks ) ) ); ?></strong></p>
		<div class="cg-progress"><span style="width:<?php echo (int) round( 100 * $done / count( $checks ) ); ?>%"></span></div>
		<ul>
			<?php foreach ( $checks as $c ) : ?>
				<li class="<?php echo $c[0] ? 'is-done' : 'is-todo'; ?>">
					<span class="dashicons <?php echo $c[0] ? 'dashicons-yes-alt' : 'dashicons-marker'; ?>"></span>
					<?php echo esc_html( $c[1] ); ?>
					<?php if ( ! $c[0] ) : ?>
						<a href="<?php echo esc_url( $c[2] ); ?>"><?php esc_html_e( 'Fix', 'coregas' ); ?></a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
			<li class="<?php echo get_option( 'blog_public' ) ? 'is-done' : 'is-info'; ?>">
				<span class="dashicons <?php echo get_option( 'blog_public' ) ? 'dashicons-visibility' : 'dashicons-hidden'; ?>"></span>
				<?php echo esc_html( get_option( 'blog_public' ) ? __( 'Search engines may index this site', 'coregas' ) : __( 'Hidden from search engines (right for staging)', 'coregas' ) ); ?>
				<a href="<?php echo esc_url( admin_url( 'options-reading.php' ) ); ?>"><?php esc_html_e( 'Change', 'coregas' ); ?></a>
			</li>
		</ul>
	</div>
	<?php
}

/**
 * Latest enquiries.
 */
function coregas_render_enquiries_widget() {
	$items = get_posts( array( 'post_type' => 'cg_enquiry', 'post_status' => 'private', 'posts_per_page' => 6 ) );
	$month = new WP_Query(
		array(
			'post_type'      => 'cg_enquiry',
			'post_status'    => 'private',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'date_query'     => array( array( 'after' => '1 month ago' ) ),
		)
	);
	echo '<p>' . esc_html( sprintf( /* translators: %d: count */ _n( '%d enquiry in the last 30 days.', '%d enquiries in the last 30 days.', (int) $month->found_posts, 'coregas' ), (int) $month->found_posts ) ) . '</p>';
	if ( ! $items ) {
		echo '<p class="description">' . esc_html__( 'Nothing yet. Messages sent through the website form appear here.', 'coregas' ) . '</p>';
		return;
	}
	echo '<ul class="cg-enq">';
	foreach ( $items as $e ) {
		printf(
			'<li><a href="%s"><strong>%s</strong></a><span>%s · %s</span></li>',
			esc_url( get_edit_post_link( $e->ID ) ),
			esc_html( $e->post_title ),
			esc_html( (string) get_post_meta( $e->ID, '_cg_enq_type', true ) ),
			esc_html( get_the_date( 'j M, H:i', $e ) )
		);
	}
	echo '</ul><p><a href="' . esc_url( admin_url( 'edit.php?post_type=cg_enquiry' ) ) . '">' . esc_html__( 'All enquiries →', 'coregas' ) . '</a></p>';
}

add_action( 'admin_menu', 'coregas_tidy_admin_menu', 99 );
/**
 * Hide menu items not used by this site.
 */
function coregas_tidy_admin_menu() {
	remove_menu_page( 'edit-comments.php' ); // comments are not used on this site.
}

add_action( 'admin_head', 'coregas_admin_guide_css' );
/**
 * Styles for the dashboard widgets.
 */
function coregas_admin_guide_css() {
	$screen = get_current_screen();
	if ( ! $screen || 'dashboard' !== $screen->id ) {
		return;
	}
	?>
	<style>
		.cg-guide__lead { font-size: 14px; }
		.cg-guide h3 { margin: 20px 0 8px; font-size: 14px; }
		.cg-guide__table { width: 100%; border-collapse: collapse; }
		.cg-guide__table td { padding: 6px 10px 6px 0; vertical-align: middle; border-top: 1px solid #f0f0f1; }
		.cg-guide__table td:first-child { width: 160px; }
		.cg-guide__table .button { width: 100%; text-align: center; }
		.cg-guide__types { color: #50575e; }
		.cg-check-list ul { margin: 10px 0 0; }
		.cg-check-list li { display: flex; align-items: center; gap: 8px; padding: 5px 0; border-top: 1px solid #f0f0f1; }
		.cg-check-list li a { margin-left: auto; }
		.cg-check-list .is-done .dashicons { color: #00a32a; }
		.cg-check-list .is-todo .dashicons { color: #dba617; }
		.cg-check-list .is-info .dashicons { color: #72aee6; }
		.cg-progress { height: 8px; border-radius: 4px; background: #f0f0f1; overflow: hidden; }
		.cg-progress span { display: block; height: 100%; background: #48050b; }
		.cg-enq li { display: flex; flex-direction: column; padding: 6px 0; border-top: 1px solid #f0f0f1; }
		.cg-enq span { color: #646970; }
	</style>
	<?php
}
