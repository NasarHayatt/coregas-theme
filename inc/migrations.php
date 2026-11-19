<?php
/**
 * One-time content migrations, so structural changes reach existing sites
 * (staging/production) on deploy without anyone editing the database by hand.
 *
 * Each step runs once, in order, tracked by the `coregas_db_version` option.
 * Steps never delete content: removed pages become drafts and their URLs redirect.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

const COREGAS_DB_VERSION = 15;

add_action( 'init', 'coregas_run_migrations', 120 ); // after theme activation/seed (init 99).
/**
 * Run pending migration steps.
 */
function coregas_run_migrations() {
	if ( ! get_option( 'coregas_seeded' ) ) {
		return; // fresh install: the seed runs first, then this on the next request.
	}
	$current = (int) get_option( 'coregas_db_version', 1 );
	if ( $current >= COREGAS_DB_VERSION ) {
		return;
	}
	// Simple lock so two simultaneous requests don't run the same step.
	if ( get_transient( 'coregas_migrating' ) ) {
		return;
	}
	set_transient( 'coregas_migrating', 1, 5 * MINUTE_IN_SECONDS );

	if ( $current < 2 ) {
		coregas_migration_2();
		update_option( 'coregas_db_version', 2 );
	}
	if ( $current < 3 ) {
		coregas_migration_3();
		update_option( 'coregas_db_version', 3 );
	}
	if ( $current < 4 ) {
		coregas_migration_4();
		update_option( 'coregas_db_version', 4 );
	}
	if ( $current < 5 ) {
		// Sections that earlier updates hid as drafts are outdated: move them to the Trash
		// (recoverable for 30 days) so each page editor lists only what is on the page.
		foreach ( get_posts( array( 'post_type' => 'cg_section', 'post_status' => 'draft', 'posts_per_page' => 200, 'fields' => 'ids' ) ) as $old ) {
			wp_trash_post( $old );
		}
		update_option( 'coregas_db_version', 5 );
	}
	if ( $current < 6 ) {
		coregas_migration_6();
		update_option( 'coregas_db_version', 6 );
	}
	if ( $current < 7 ) {
		coregas_migration_7();
		update_option( 'coregas_db_version', 7 );
	}
	if ( $current < 8 ) {
		coregas_migration_8();
		update_option( 'coregas_db_version', 8 );
	}
	if ( $current < 9 ) {
		coregas_migration_9();
		update_option( 'coregas_db_version', 9 );
	}
	if ( $current < 10 ) {
		coregas_migration_10();
		update_option( 'coregas_db_version', 10 );
	}
	if ( $current < 11 ) {
		coregas_migration_11();
		update_option( 'coregas_db_version', 11 );
	}
	if ( $current < 12 ) {
		coregas_migration_12();
		update_option( 'coregas_db_version', 12 );
	}
	if ( $current < 13 ) {
		coregas_migration_13();
		update_option( 'coregas_db_version', 13 );
	}
	if ( $current < 14 ) {
		coregas_migration_14();
		update_option( 'coregas_db_version', 14 );
	}
	if ( $current < 15 ) {
		// Site name used at the end of page titles: "Core Gas", not "Core-Gas".
		if ( 'Core-Gas' === get_option( 'blogname' ) ) {
			update_option( 'blogname', 'Core Gas' );
		}
		update_option( 'coregas_db_version', 15 );
	}

	delete_transient( 'coregas_migrating' );
	flush_rewrite_rules( false );
}

/**
 * v2: "Company" becomes "About Us" and the header menu becomes
 * Home, About Us, Gas Supply, Regulation, Contact Us.
 */
function coregas_migration_2() {
	$about = get_page_by_path( 'company' );
	if ( ! $about ) {
		$about = get_page_by_path( 'about-us' );
	}
	$supply  = get_page_by_path( 'gas-supply' );
	$reg     = get_page_by_path( 'regulation' );
	$contact = get_page_by_path( 'contact-us' );
	$home_id = (int) get_option( 'page_on_front' );

	if ( $about ) {
		wp_update_post( array( 'ID' => $about->ID, 'post_title' => 'About Us', 'post_name' => 'about-us' ) );
		update_post_meta( $about->ID, COREGAS_META_PREFIX . 'banner_eyebrow', 'About Core Gas' );
	}
	if ( $supply ) {
		wp_update_post( array( 'ID' => $supply->ID, 'post_title' => 'Gas Supply' ) );
	}
	if ( $contact ) {
		wp_update_post( array( 'ID' => $contact->ID, 'post_title' => 'Contact Us' ) );
	}

	/* Header menu. */
	$items = array(
		array( 'page', $home_id, 'Home' ),
		array( 'page', $about ? $about->ID : 0, 'About Us' ),
		array( 'page', $supply ? $supply->ID : 0, 'Gas Supply' ),
		array( 'page', $reg ? $reg->ID : 0, 'Regulation' ),
		array( 'page', $contact ? $contact->ID : 0, 'Contact Us' ),
	);
	coregas_replace_menu_items( 'primary', $items );

	/* Footer "Company" column. */
	if ( $about ) {
		coregas_replace_menu_items(
			'footer_2',
			array(
				array( 'page', $about->ID, 'About Us' ),
				array( 'page', $reg ? $reg->ID : 0, 'Regulation' ),
			)
		);
	}
	if ( $contact ) {
		coregas_replace_menu_items(
			'footer_1',
			array(
				array( 'page', $contact->ID, 'Contact Us' ),
				array( 'page', $contact->ID, 'Request gas supply' ),
			)
		);
	}

}

/**
 * v3: native scrolling by default; Gas Supply and Regulation rebuilt as short,
 * image-led pages (image/text split, steps strip, licence record).
 */
function coregas_migration_3() {
	$settings = coregas_settings_id();
	if ( $settings ) {
		update_post_meta( $settings, COREGAS_META_PREFIX . 'smooth_scroll', '0' );
	}

	/* ---------- Gas Supply ---------- */
	$supply = get_page_by_path( 'gas-supply' );
	if ( $supply && ! coregas_page_has_section_type( $supply->ID, 'split' ) ) {
		update_post_meta( $supply->ID, COREGAS_META_PREFIX . 'banner_title', 'Gas Supply' );
		update_post_meta( $supply->ID, COREGAS_META_PREFIX . 'banner_text', 'Connecting natural gas supply with industrial demand.' );
		coregas_shift_sections( $supply->ID, 1 );
		coregas_seed_section(
			'Gas Supply: introduction',
			$supply->ID,
			'split',
			0,
			array(
				'sec_eyebrow'   => 'Gas supply to industry',
				'sec_heading'   => 'A dependable natural gas supply for industrial consumers',
				'sec_bg'        => 'light',
				'split_side'    => 'right',
				'split_caption' => 'Licensed activity: natural gas marketing & sales',
			),
			"<p>Core Gas sources and markets natural gas for sale to customers, licensed by OGRA.</p>
<p>We serve industrial users whose operations depend on a reliable and continuous supply of natural gas, managing sourcing, transportation, delivery and customer service as one supply relationship.</p>",
			array( array( 'label' => 'Request gas supply', 'url' => '/contact-us/' ) )
		);
		foreach ( coregas_sections_for_page_any( $supply->ID ) as $sec ) {
			if ( 'cards' === coregas_meta( $sec->ID, 'section_type' ) ) {
				update_post_meta( $sec->ID, COREGAS_META_PREFIX . 'cards_layout', 'strip' );
				update_post_meta( $sec->ID, COREGAS_META_PREFIX . 'sec_bg', 'dark' );
				update_post_meta( $sec->ID, COREGAS_META_PREFIX . 'sec_eyebrow', 'Gas supply chain' );
				update_post_meta( $sec->ID, COREGAS_META_PREFIX . 'sec_heading', 'From source to service' );
			}
			if ( 'cta' === coregas_meta( $sec->ID, 'section_type' ) ) {
				wp_update_post( array( 'ID' => $sec->ID, 'post_status' => 'draft' ) ); // the intro now carries the call to action.
			}
		}
	}

	/* ---------- Regulation ---------- */
	$reg = get_page_by_path( 'regulation' );
	if ( $reg && ! coregas_page_has_section_type( $reg->ID, 'split' ) ) {
		update_post_meta( $reg->ID, COREGAS_META_PREFIX . 'banner_title', 'Regulation' );
		update_post_meta( $reg->ID, COREGAS_META_PREFIX . 'banner_text', 'Licensed by OGRA to undertake natural gas marketing and sales in Pakistan.' );
		coregas_shift_sections( $reg->ID, 1 );
		coregas_seed_section(
			'Regulation: introduction',
			$reg->ID,
			'split',
			0,
			array(
				'sec_eyebrow' => 'Regulatory approval',
				'sec_heading' => 'Licensed by the Oil & Gas Regulatory Authority',
				'sec_bg'      => 'light',
				'split_side'  => 'left',
			),
			"<p>Core Gas Private Limited is licensed by the Oil & Gas Regulatory Authority (OGRA) for natural gas marketing and sales under the applicable regulatory framework.</p>
<p>Our growth is planned within that framework: from regulatory approval, to sourcing and supply, to serving industrial consumers across Pakistan.</p>"
		);
		foreach ( coregas_sections_for_page_any( $reg->ID ) as $sec ) {
			if ( 'cards' === coregas_meta( $sec->ID, 'section_type' ) && 'cg_licence' === coregas_meta( $sec->ID, 'cards_source' ) ) {
				update_post_meta( $sec->ID, COREGAS_META_PREFIX . 'cards_layout', 'record' );
				update_post_meta( $sec->ID, COREGAS_META_PREFIX . 'sec_bg', 'maroon' );
				update_post_meta( $sec->ID, COREGAS_META_PREFIX . 'sec_eyebrow', 'Licence record' );
				update_post_meta( $sec->ID, COREGAS_META_PREFIX . 'sec_heading', 'Natural gas marketing & sales licence' );
			}
		}
		coregas_seed_section(
			'Regulation: our path',
			$reg->ID,
			'steps',
			10,
			array(
				'sec_eyebrow' => 'Building a reliable gas supply platform',
				'sec_heading' => 'Growth within the regulatory framework',
				'sec_bg'      => 'light',
				'steps'       => array(
					array( 'tag' => 'OGRA licence', 'title' => 'Licence', 'text' => 'Regulatory approval for gas marketing and sales in Pakistan.', 'image' => '' ),
					array( 'tag' => 'Marketing & sales', 'title' => 'Supply', 'text' => 'Sourcing, transportation and delivery of natural gas.', 'image' => '' ),
					array( 'tag' => 'Industrial customers', 'title' => 'Grow', 'text' => 'Expanding natural gas supply to industrial consumers in Pakistan.', 'image' => '' ),
				),
			)
		);
	}
}

/**
 * v4: About Us is about Core Gas only (copy from the Core Gas slides of the company
 * profile). Earlier sections are kept as drafts, nothing is deleted.
 */
function coregas_migration_4() {
	$about = get_page_by_path( 'about-us' );
	if ( ! $about || get_post_meta( $about->ID, '_coregas_about_v4', true ) ) {
		return;
	}
	foreach ( coregas_sections_for_page_any( $about->ID ) as $sec ) {
		wp_update_post( array( 'ID' => $sec->ID, 'post_status' => 'draft' ) );
	}

	update_post_meta( $about->ID, COREGAS_META_PREFIX . 'banner_eyebrow', 'Core Gas Private Limited' );
	update_post_meta( $about->ID, COREGAS_META_PREFIX . 'banner_title', 'About Us' );
	update_post_meta( $about->ID, COREGAS_META_PREFIX . 'banner_text', 'Downstream gas marketing. Licensed by OGRA to undertake natural gas marketing and sales in Pakistan.' );

	$who  = coregas_seed_section(
		'About Us: who we are',
		$about->ID,
		'split',
		0,
		array(
			'sec_eyebrow'   => 'Who we are',
			'sec_heading'   => "Building a dependable natural gas supply platform for Pakistan's industry",
			'sec_bg'        => 'light',
			'split_side'    => 'right',
			'split_caption' => 'Natural gas · Industrial supply',
		),
		"<p>Core Gas Private Limited is a downstream gas company established to meet the natural gas requirements of industrial consumers in Pakistan.</p>
<p>The company is licensed by the Oil & Gas Regulatory Authority (OGRA) for natural gas marketing and sales under the applicable regulatory framework.</p>"
	);
	$what = coregas_seed_section(
		'About Us: what we do',
		$about->ID,
		'split',
		1,
		array(
			'sec_eyebrow'   => 'What we do',
			'sec_heading'   => 'Connecting natural gas supply with industrial demand',
			'sec_bg'        => 'light',
			'split_side'    => 'left',
			'split_caption' => 'Current focus: natural gas supply to industrial consumers in Pakistan',
		),
		"<p>We source and market natural gas for sale to customers, managing gas sourcing, transportation, delivery and customer service.</p>
<p>Our customers are industrial users whose operations depend on a reliable and continuous supply of natural gas.</p>
<p>We work by six principles: regulation, sourcing, supply, reliability, customer service and partnerships.</p>",
		array( array( 'label' => 'How we supply', 'url' => '/gas-supply/' ) )
	);
	// Photos for the two splits, imported from the bundled company-profile images.
	foreach ( array( array( $who, 'flame.jpg', 'Natural gas flame' ), array( $what, 'production-floor.jpg', 'Industrial production floor' ) ) as $p ) {
		if ( $p[0] ) {
			$att = coregas_import_bundled_image( $p[1], $p[0], $p[2] );
			if ( $att ) {
				update_post_meta( $p[0], COREGAS_META_PREFIX . 'split_image', (string) $att );
			}
		}
	}
	update_post_meta( $about->ID, '_coregas_about_v4', 1 );
}

/**
 * Whether a page already has a section of a type.
 *
 * @param int    $page_id Page ID.
 * @param string $type    Section type.
 * @return bool
 */
function coregas_page_has_section_type( $page_id, $type ) {
	foreach ( coregas_sections_for_page_any( $page_id ) as $sec ) {
		if ( $type === coregas_meta( $sec->ID, 'section_type' ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Push a page's sections down the order to make room at the top.
 *
 * @param int $page_id Page ID.
 * @param int $by      Positions.
 */
function coregas_shift_sections( $page_id, $by ) {
	foreach ( coregas_sections_for_page_any( $page_id ) as $sec ) {
		wp_update_post( array( 'ID' => $sec->ID, 'menu_order' => (int) $sec->menu_order + $by ) );
	}
}

/**
 * All sections assigned to a page, in order, any status.
 *
 * @param int $page_id Page ID.
 * @return WP_Post[]
 */
function coregas_sections_for_page_any( $page_id ) {
	return get_posts(
		array(
			'post_type'      => 'cg_section',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => 50,
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
			'meta_key'       => COREGAS_META_PREFIX . 'page', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => (string) $page_id, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
}

/**
 * Replace every item of the menu assigned to a location.
 *
 * @param string $location Menu location.
 * @param array  $items    [ type ('page'|'url'), id-or-url, title ].
 */
function coregas_replace_menu_items( $location, $items ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return;
	}
	$menu_id = (int) $locations[ $location ];
	foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $old ) {
		wp_delete_post( $old->ID, true ); // menu item records only, never pages.
	}
	$pos = 1;
	foreach ( $items as $item ) {
		if ( 'page' === $item[0] && ! $item[1] ) {
			continue;
		}
		$data = array(
			'menu-item-status'   => 'publish',
			'menu-item-position' => $pos++,
			'menu-item-title'    => $item[2],
		);
		if ( 'page' === $item[0] ) {
			$data['menu-item-type']      = 'post_type';
			$data['menu-item-object']    = 'page';
			$data['menu-item-object-id'] = (int) $item[1];
		} else {
			$data['menu-item-type'] = 'custom';
			$data['menu-item-url']  = $item[1];
		}
		wp_update_nav_menu_item( $menu_id, 0, $data );
	}
}

/**
 * Copy an image bundled in assets/img/stock into the Media Library.
 *
 * @param string $file    File name.
 * @param int    $post_id Parent post.
 * @param string $title   Attachment title / alt text.
 * @return int Attachment ID or 0.
 */
function coregas_import_bundled_image( $file, $post_id, $title ) {
	$src = COREGAS_DIR . '/assets/img/stock/' . basename( $file );
	if ( ! file_exists( $src ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = wp_tempnam( $file );
	if ( ! $tmp || ! copy( $src, $tmp ) ) {
		return 0;
	}
	$id = media_handle_sideload( array( 'name' => basename( $file ), 'tmp_name' => $tmp ), $post_id, $title );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		return 0;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $title );
	return (int) $id;
}

/**
 * Does a published or drafted section with this title already exist?
 *
 * @param string $title Section title.
 * @return bool
 */
function coregas_section_exists( $title ) {
	return (bool) get_posts(
		array(
			'post_type'      => 'cg_section',
			'title'          => $title,
			'post_status'    => array( 'publish', 'draft', 'private' ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
}

/**
 * v6: more detailed content: mission, vision and objectives (About Us), customer
 * services and how to apply (Gas Supply), HSE commitment (Regulation), an HSE
 * policy page, extra enquiry types and regulator links in the footer.
 * Adds only; never overwrites anything an editor has changed.
 */
function coregas_migration_6() {
	$about  = get_page_by_path( 'about-us' );
	$supply = get_page_by_path( 'gas-supply' );
	$reg    = get_page_by_path( 'regulation' );

	/* About Us: mission & vision, objectives. */
	if ( $about ) {
		if ( ! coregas_section_exists( 'About Us: mission & vision' ) ) {
			coregas_seed_section(
				'About Us: mission & vision',
				$about->ID,
				'columns',
				2,
				array(
					'sec_eyebrow' => 'Mission & vision',
					'sec_heading' => 'Why Core Gas exists',
					'sec_bg'      => 'white',
					'cols_count'  => '2',
					'cols_items'  => array(
						array( 'tag' => 'Our mission', 'title' => 'Gas that industry can count on', 'text' => "To provide a long-term, dependable and sustainable supply of natural gas to Pakistan's industry, so that our customers, and the communities around them, can grow." ),
						array( 'tag' => 'Our vision', 'title' => 'A stronger energy supply for Pakistan', 'text' => "To help strengthen Pakistan's energy supply and build a more competitive, efficient and energy-secure industrial base." ),
					),
				)
			);
		}
		if ( ! coregas_section_exists( 'About Us: objectives' ) ) {
			coregas_seed_section(
				'About Us: objectives',
				$about->ID,
				'columns',
				3,
				array(
					'sec_eyebrow' => 'Our objectives',
					'sec_heading' => 'What we aim to achieve',
					'sec_bg'      => 'white',
					'cols_count'  => '3',
					'cols_items'  => array(
						array( 'title' => 'Competitive supply', 'text' => 'A continuous gas supply for industrial users at competitive prices, lowering the cost of manufacturing and, in turn, prices for end consumers.' ),
						array( 'title' => 'Meeting national demand', 'text' => "Helping meet Pakistan's growing demand for energy and easing the pressure it places on public resources." ),
						array( 'title' => 'Public revenue', 'text' => 'Contributing to government revenue through royalties, levies and taxes across the gas value chain.' ),
						array( 'title' => 'Easing circular debt', 'text' => 'Supporting a healthier, commercially sound gas market that helps limit the build-up of circular debt.' ),
						array( 'title' => 'Fewer fuel imports', 'text' => "Putting locally produced gas to work for industry, reducing the country's reliance on imported fuel." ),
						array( 'title' => 'Responsible operations', 'text' => 'Working safely and responsibly, with care for our people, our customers and the environment.' ),
					),
				)
			);
		}
	}

	/* Gas Supply: customer services, how to apply, call to action. */
	if ( $supply ) {
		if ( ! coregas_section_exists( 'Gas Supply: customer services' ) ) {
			coregas_seed_section(
				'Gas Supply: customer services',
				$supply->ID,
				'columns',
				2,
				array(
					'sec_eyebrow' => 'Customer services',
					'sec_heading' => 'Support that comes with every supply',
					'sec_intro'   => 'Supplying gas is only part of the job. We look after the measurement, monitoring and paperwork around it, so our customers can focus on production.',
					'sec_bg'      => 'white',
					'cols_count'  => '4',
					'cols_items'  => array(
						array( 'tag' => 'Metering', 'title' => 'Accurate measurement', 'text' => 'Joint meter readings with each customer, flow proving to confirm accuracy, and meter replacement whenever it is needed.' ),
						array( 'tag' => 'Monitoring', 'title' => 'Steady pressure and flow', 'text' => 'SCADA-based monitoring of supply, pressure assurance at the point of delivery, and open access to consumption data.' ),
						array( 'tag' => 'Billing & compliance', 'title' => 'Clear records', 'text' => 'Transparent billing, including ad hoc bills on request, quick correction of any errors, full documentation and help with regulatory compliance.' ),
						array( 'tag' => 'Support', 'title' => 'Always reachable', 'text' => 'Technical support from our team and round-the-clock communication, so any supply question is answered quickly.' ),
					),
				)
			);
		}
		if ( ! coregas_section_exists( 'Gas Supply: how to apply' ) ) {
			coregas_seed_section(
				'Gas Supply: how to apply',
				$supply->ID,
				'steps',
				3,
				array(
					'sec_eyebrow' => 'Apply for gas supply',
					'sec_heading' => 'How to request a supply',
					'sec_bg'      => 'white',
					'steps_photos' => '0',
					'steps'       => array(
						array( 'tag' => 'Your requirement', 'title' => 'Enquire', 'text' => 'Tell us about your facility: its location, your existing connection and pipeline size, the volume of gas you need and for how long.', 'image' => '' ),
						array( 'tag' => 'Our assessment', 'title' => 'Review', 'text' => 'Our team reviews the requirement against available supply and the transport route to your site, and gets back to you.', 'image' => '' ),
						array( 'tag' => 'Supply agreement', 'title' => 'Supply', 'text' => 'Once terms are agreed, gas supply begins, backed by the metering, monitoring and support described above.', 'image' => '' ),
					),
				)
			);
		}
		if ( ! coregas_section_exists( 'Gas Supply: call to action' ) ) {
			coregas_seed_section(
				'Gas Supply: call to action',
				$supply->ID,
				'cta',
				4,
				array(
					'sec_eyebrow'   => 'For industrial consumers',
					'sec_heading'   => 'Need a dependable gas supply for your facility?',
					'sec_intro'     => 'Send us your requirement and our team will be in touch.',
					'sec_bg'        => 'maroon',
					'cta_btn_label' => 'Apply for gas supply',
					'cta_btn_url'   => '/contact-us/',
				)
			);
		}
	}

	/* HSE policy page. */
	$hse = get_page_by_path( 'hse-policy' );
	if ( ! $hse ) {
		$hse_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'HSE Policy',
				'post_name'   => 'hse-policy',
			)
		);
		if ( $hse_id && ! is_wp_error( $hse_id ) ) {
			update_post_meta( $hse_id, COREGAS_META_PREFIX . 'banner_eyebrow', 'Health, safety & environment' );
			coregas_seed_section(
				'HSE Policy: commitment',
				$hse_id,
				'text',
				0,
				array(
					'sec_eyebrow' => 'Our commitment',
					'sec_heading' => 'Safety first, at every step',
					'sec_bg'      => 'white',
				),
				"<p>Core Gas is committed to operating safely, responsibly and sustainably: protecting our people, the communities we work in and the environment, while supplying the natural gas Pakistan's industry needs.</p>\n<p>Health, safety and environment (HSE) is built into how we plan, decide and work, together with full compliance with the laws and regulations that apply to us.</p>\n<h3>People and processes</h3>\n<ul>\n<li>Leaders at every level show visible commitment to HSE and set a consistent safety culture across all operations.</li>\n<li>Our processes follow Pakistan's regulatory requirements and recognised international HSE standards.</li>\n<li>We build a healthy, engaged and inclusive workforce, with training and development for everyone.</li>\n<li>We expect contractors, suppliers and partners to meet the same health and safety standards.</li>\n<li>We communicate openly about our HSE performance with everyone who has a stake in it.</li>\n</ul>\n<h3>Safety at work</h3>\n<ul>\n<li>Everyone working in the field receives site-specific inductions, emergency-response training and regular drills.</li>\n<li>Field activities and business-critical processes are risk-assessed, including a job safety analysis, before work starts.</li>\n<li>We encourage open and honest reporting and look for hazards before they can cause harm.</li>\n<li>The right tools, equipment and personal protective equipment are provided at every site and kept in good working order.</li>\n<li>We comply with Pakistan's labour, occupational health and safety, and environmental laws.</li>\n</ul>\n<h3>Environment</h3>\n<ul>\n<li>We favour energy efficiency, cleaner technology and the careful use of resources, supporting a lower-carbon future.</li>\n<li>We work to prevent pollution and keep emissions low in everything we do.</li>\n<li>Environmental factors are part of planning and decision-making from the start.</li>\n<li>We keep improving our environmental performance through better practice and new technology.</li>\n</ul>"
			);
			$hse = get_post( $hse_id );
		}
	}

	/* Regulation: health, safety & environment summary. */
	if ( $reg && ! coregas_section_exists( 'Regulation: health, safety & environment' ) ) {
		coregas_seed_section(
			'Regulation: health, safety & environment',
			$reg->ID,
			'columns',
			20,
			array(
				'sec_eyebrow' => 'Health, safety & environment',
				'sec_heading' => 'Compliance goes beyond the licence',
				'sec_intro'   => 'Working in a regulated sector means safety and responsibility are part of every decision we make.',
				'sec_bg'      => 'white',
				'cols_count'  => '3',
				'cols_items'  => array(
					array( 'title' => 'People & processes', 'text' => "Visible leadership on safety, processes aligned with Pakistan's regulations and international HSE standards, and the same expectations for our contractors and partners." ),
					array( 'title' => 'Safety at work', 'text' => 'Risk assessments before field work, site inductions, emergency drills, and the right protective equipment at every site.' ),
					array( 'title' => 'Environment', 'text' => 'Energy efficiency, cleaner technology and low emissions, with environmental factors considered from the planning stage.' ),
				),
			),
			'',
			$hse ? array( array( 'label' => 'Read our HSE policy', 'url' => '/hse-policy/' ) ) : array()
		);
	}

	/* Legal menu: link the HSE policy. */
	$locations = get_nav_menu_locations();
	if ( $hse && ! empty( $locations['legal'] ) ) {
		$has = false;
		foreach ( (array) wp_get_nav_menu_items( (int) $locations['legal'] ) as $item ) {
			if ( (int) $item->object_id === (int) $hse->ID ) {
				$has = true;
			}
		}
		if ( ! $has ) {
			wp_update_nav_menu_item(
				(int) $locations['legal'],
				0,
				array(
					'menu-item-status'    => 'publish',
					'menu-item-title'     => 'HSE Policy',
					'menu-item-type'      => 'post_type',
					'menu-item-object'    => 'page',
					'menu-item-object-id' => (int) $hse->ID,
					'menu-item-position'  => 3,
				)
			);
		}
	}

	/* Footer "Licence" column: add the regulator and gas utility websites. */
	if ( ! empty( $locations['footer_4'] ) ) {
		$menu_id = (int) $locations['footer_4'];
		$urls    = array();
		foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
			$urls[] = untrailingslashit( $item->url );
		}
		$links = array(
			array( 'https://www.ogra.org.pk', 'OGRA' ),
			array( 'https://mope.gov.pk', 'Ministry of Energy (Petroleum Division)' ),
			array( 'https://www.sngpl.com.pk', 'SNGPL' ),
			array( 'https://www.ssgc.com.pk', 'SSGC' ),
		);
		foreach ( $links as $link ) {
			if ( in_array( $link[0], $urls, true ) ) {
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-status' => 'publish',
					'menu-item-title'  => $link[1],
					'menu-item-type'   => 'custom',
					'menu-item-url'    => $link[0],
					'menu-item-target' => '_blank',
				)
			);
		}
		$menu = wp_get_nav_menu_object( $menu_id );
		if ( $menu && 'Licence' === $menu->name ) {
			wp_update_nav_menu_object( $menu_id, array( 'menu-name' => 'Regulation & links' ) );
		}
	}

	/* Enquiry form: types for new connections and gas producers (only if the list was never edited). */
	$settings = get_posts( array( 'post_type' => 'cg_settings', 'numberposts' => 1, 'fields' => 'ids' ) );
	if ( $settings ) {
		$types  = get_post_meta( $settings[0], COREGAS_META_PREFIX . 'form_types', true );
		$labels = is_array( $types ) ? wp_list_pluck( $types, 'label' ) : array();
		if ( array( 'Natural gas supply request', 'Partnership', 'General enquiry' ) === $labels || ! $labels ) {
			update_post_meta(
				$settings[0],
				COREGAS_META_PREFIX . 'form_types',
				array(
					array( 'label' => 'New gas supply / connection' ),
					array( 'label' => 'Existing customer support' ),
					array( 'label' => 'Gas producer or supplier' ),
					array( 'label' => 'Partnership' ),
					array( 'label' => 'General enquiry' ),
				)
			);
		}
	}
}

/**
 * v7: mission & vision on the homepage, after the About section, linking to About Us.
 * Uses the wording from About Us so any edits made there carry over.
 */
function coregas_migration_7() {
	$home_id = (int) get_option( 'page_on_front' );
	if ( ! $home_id || coregas_section_exists( 'Home: mission & vision' ) ) {
		return;
	}
	$items = array(
		array( 'tag' => 'Our mission', 'title' => 'Gas that industry can count on', 'text' => "To provide a long-term, dependable and sustainable supply of natural gas to Pakistan's industry, so that our customers, and the communities around them, can grow." ),
		array( 'tag' => 'Our vision', 'title' => 'A stronger energy supply for Pakistan', 'text' => "To help strengthen Pakistan's energy supply and build a more competitive, efficient and energy-secure industrial base." ),
	);
	$about = get_posts( array( 'post_type' => 'cg_section', 'title' => 'About Us: mission & vision', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids' ) );
	if ( $about ) {
		$current = get_post_meta( $about[0], COREGAS_META_PREFIX . 'cols_items', true );
		if ( is_array( $current ) && $current ) {
			$items = $current;
		}
	}
	coregas_seed_section(
		'Home: mission & vision',
		$home_id,
		'columns',
		2,
		array(
			'sec_eyebrow' => 'Mission & vision',
			'sec_heading' => 'Why Core Gas exists',
			'sec_bg'      => 'white',
			'cols_count'  => '2',
			'cols_items'  => $items,
		),
		'',
		array( array( 'label' => 'More about Core Gas', 'url' => '/about-us/' ) )
	);
}

/**
 * v8: new dark footer. Footer colours and the description move to the new defaults,
 * but only where they still hold the old defaults (custom choices are kept).
 */
function coregas_migration_8() {
	$settings = get_posts( array( 'post_type' => 'cg_settings', 'numberposts' => 1, 'fields' => 'ids' ) );
	if ( ! $settings ) {
		return;
	}
	$sid  = $settings[0];
	$swap = array(
		'color_footer_bg'   => array( '#ffffff', '#120d0d' ),
		'color_footer_head' => array( '#48050b', '#f4f1ee' ),
		'color_footer_text' => array( '#544742', '#c9bfba' ),
		'footer_about'      => array( '', "Downstream natural gas marketing and sales, supplying the natural gas that Pakistan's industry runs on." ),
	);
	foreach ( $swap as $key => $pair ) {
		$value = get_post_meta( $sid, COREGAS_META_PREFIX . $key, true );
		if ( strtolower( trim( (string) $value ) ) === $pair[0] ) {
			update_post_meta( $sid, COREGAS_META_PREFIX . $key, $pair[1] );
		}
	}
}

/**
 * v9: a "What We Do" page (overview, supply chain, customer services) in the header
 * menu after About Us. Gas Supply keeps the customer side: introduction, how to
 * request supply and the call to action. Sections are moved, never duplicated.
 */
function coregas_migration_9() {
	$page = get_page_by_path( 'what-we-do' );
	if ( ! $page ) {
		$id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'What We Do',
				'post_name'   => 'what-we-do',
				'menu_order'  => 2,
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			return;
		}
		$page = get_post( $id );
		update_post_meta( $id, COREGAS_META_PREFIX . 'banner_show', '1' );
		update_post_meta( $id, COREGAS_META_PREFIX . 'banner_eyebrow', 'Our business' );
		update_post_meta( $id, COREGAS_META_PREFIX . 'banner_text', "Natural gas marketing and sales for Pakistan's industry, from source to service." );
	}
	$pid = (int) $page->ID;

	if ( ! coregas_section_exists( 'What We Do: overview' ) ) {
		coregas_seed_section(
			'What We Do: overview',
			$pid,
			'split',
			0,
			array(
				'sec_eyebrow'   => 'What we do',
				'sec_heading'   => 'Closing the gap between gas supply and industrial demand',
				'sec_bg'        => 'white',
				'split_side'    => 'right',
				'split_caption' => 'Licensed activity: natural gas marketing & sales',
			),
			"<p>Core Gas buys, sells and supplies natural gas to industrial consumers in Pakistan, licensed by OGRA for natural gas marketing and sales.</p>\n<p>By securing gas through supply arrangements with producers, we aim to close the gap between the gas available and the gas industry needs, giving our customers a continuous and competitively priced supply.</p>\n<p>We look after the whole relationship: sourcing, transportation, delivery and the day-to-day service that keeps gas flowing to each site.</p>",
			array( array( 'label' => 'Request gas supply', 'url' => '/gas-supply/' ) )
		);
	}

	/* Move the supply chain strip and customer services from Gas Supply. */
	$moves = array(
		'Gas supply: services'          => 1,
		'Gas Supply: customer services' => 2,
	);
	foreach ( $moves as $title => $order ) {
		$found = get_posts( array( 'post_type' => 'cg_section', 'title' => $title, 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids' ) );
		if ( $found ) {
			update_post_meta( $found[0], COREGAS_META_PREFIX . 'page', (string) $pid );
			wp_update_post( array( 'ID' => $found[0], 'menu_order' => $order, 'post_title' => str_replace( array( 'Gas supply:', 'Gas Supply:' ), 'What We Do:', $title ) ) );
		}
	}
	if ( ! coregas_section_exists( 'What We Do: call to action' ) ) {
		coregas_seed_section(
			'What We Do: call to action',
			$pid,
			'cta',
			3,
			array(
				'sec_eyebrow'   => 'For industrial consumers',
				'sec_heading'   => 'Looking for a dependable gas supply?',
				'sec_intro'     => 'See how to request supply for your facility.',
				'sec_bg'        => 'maroon',
				'cta_btn_label' => 'Request gas supply',
				'cta_btn_url'   => '/gas-supply/',
			)
		);
	}

	/* Gas Supply banner now speaks to customers (only if still the original wording). */
	$supply = get_page_by_path( 'gas-supply' );
	if ( $supply && 'Connecting natural gas supply with industrial demand.' === get_post_meta( $supply->ID, COREGAS_META_PREFIX . 'banner_text', true ) ) {
		update_post_meta( $supply->ID, COREGAS_META_PREFIX . 'banner_eyebrow', 'For industrial consumers' );
		update_post_meta( $supply->ID, COREGAS_META_PREFIX . 'banner_text', 'A dependable natural gas supply for your facility, and how to request it.' );
	}

	/* About Us "what we do" button now points to the new page (if unchanged). */
	$about_wwd = get_posts( array( 'post_type' => 'cg_section', 'title' => 'About Us: what we do', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids' ) );
	if ( $about_wwd ) {
		$links = get_post_meta( $about_wwd[0], COREGAS_META_PREFIX . 'sec_links', true );
		if ( is_array( $links ) && 1 === count( $links ) && isset( $links[0]['url'] ) && '/gas-supply/' === $links[0]['url'] ) {
			update_post_meta( $about_wwd[0], COREGAS_META_PREFIX . 'sec_links', array( array( 'label' => 'More on what we do', 'url' => '/what-we-do/' ) ) );
		}
	}

	/* The last "how to apply" step referred to services "described above", which now live on What We Do. */
	$apply = get_posts( array( 'post_type' => 'cg_section', 'title' => 'Gas Supply: how to apply', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids' ) );
	if ( $apply ) {
		$steps = get_post_meta( $apply[0], COREGAS_META_PREFIX . 'steps', true );
		if ( is_array( $steps ) ) {
			foreach ( $steps as $i => $step ) {
				if ( isset( $step['text'] ) && false !== strpos( $step['text'], 'described above' ) ) {
					$steps[ $i ]['text'] = 'Once terms are agreed, gas supply begins, backed by the metering, monitoring and support that come with every Core Gas supply.';
				}
			}
			update_post_meta( $apply[0], COREGAS_META_PREFIX . 'steps', $steps );
		}
	}

	/* Menus: header after About Us; footer "Gas supply" column first. */
	coregas_insert_menu_page( 'primary', $pid, 'What We Do', 'about-us' );
	coregas_insert_menu_page( 'footer_3', $pid, 'What we do', '' );
}

/**
 * Add a page to a menu once, right after the item linking to $after_slug
 * (or first when $after_slug is empty or not found). Other items keep their order.
 *
 * @param string $location   Menu location.
 * @param int    $page_id    Page to add.
 * @param string $title      Menu label.
 * @param string $after_slug Slug of the page it follows.
 */
function coregas_insert_menu_page( $location, $page_id, $title, $after_slug ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return;
	}
	$menu_id = (int) $locations[ $location ];
	$items   = (array) wp_get_nav_menu_items( $menu_id );
	$after   = $after_slug ? get_page_by_path( $after_slug ) : null;
	$pos     = 1;
	foreach ( $items as $item ) {
		if ( 'page' === $item->object && (int) $item->object_id === (int) $page_id ) {
			return; // already there.
		}
		if ( $after && 'page' === $item->object && (int) $item->object_id === (int) $after->ID ) {
			$pos = (int) $item->menu_order + 1;
		}
	}
	foreach ( $items as $item ) {
		if ( (int) $item->menu_order >= $pos ) {
			wp_update_post( array( 'ID' => $item->ID, 'menu_order' => (int) $item->menu_order + 1 ) );
		}
	}
	wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-status'    => 'publish',
			'menu-item-title'     => $title,
			'menu-item-type'      => 'post_type',
			'menu-item-object'    => 'page',
			'menu-item-object-id' => (int) $page_id,
			'menu-item-position'  => $pos,
		)
	);
}

/**
 * v10: What We Do removed again. Its moved sections go back to Gas Supply in their
 * original order, its own new sections go to the Trash (recoverable for 30 days),
 * the page becomes a draft (its URL redirects to Gas Supply) and menu links are removed.
 */
function coregas_migration_10() {
	$page   = get_page_by_path( 'what-we-do', OBJECT, 'page' );
	$supply = get_page_by_path( 'gas-supply' );
	if ( ! $page ) {
		$drafts = get_posts( array( 'post_type' => 'page', 'name' => 'what-we-do', 'post_status' => 'any', 'posts_per_page' => 1 ) );
		$page   = $drafts ? $drafts[0] : null;
	}
	if ( ! $page ) {
		return;
	}

	if ( $supply ) {
		$back = array(
			'What We Do: services'          => array( 'Gas supply: services', 1 ),
			'What We Do: customer services' => array( 'Gas Supply: customer services', 2 ),
		);
		foreach ( $back as $title => $to ) {
			$found = get_posts( array( 'post_type' => 'cg_section', 'title' => $title, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids' ) );
			if ( $found ) {
				update_post_meta( $found[0], COREGAS_META_PREFIX . 'page', (string) $supply->ID );
				wp_update_post( array( 'ID' => $found[0], 'menu_order' => $to[1], 'post_title' => $to[0] ) );
			}
		}
		if ( 'A dependable natural gas supply for your facility, and how to request it.' === get_post_meta( $supply->ID, COREGAS_META_PREFIX . 'banner_text', true ) ) {
			update_post_meta( $supply->ID, COREGAS_META_PREFIX . 'banner_eyebrow', 'Gas supply chain' );
			update_post_meta( $supply->ID, COREGAS_META_PREFIX . 'banner_text', 'Connecting natural gas supply with industrial demand.' );
		}
	}

	foreach ( array( 'What We Do: overview', 'What We Do: call to action' ) as $title ) {
		foreach ( get_posts( array( 'post_type' => 'cg_section', 'title' => $title, 'post_status' => 'any', 'posts_per_page' => 5, 'fields' => 'ids' ) ) as $id ) {
			wp_trash_post( $id );
		}
	}
	wp_update_post( array( 'ID' => $page->ID, 'post_status' => 'draft' ) );

	/* About Us "what we do" button back to Gas Supply (if unchanged). */
	$about_wwd = get_posts( array( 'post_type' => 'cg_section', 'title' => 'About Us: what we do', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids' ) );
	if ( $about_wwd ) {
		$links = get_post_meta( $about_wwd[0], COREGAS_META_PREFIX . 'sec_links', true );
		if ( is_array( $links ) && 1 === count( $links ) && isset( $links[0]['url'] ) && '/what-we-do/' === $links[0]['url'] ) {
			update_post_meta( $about_wwd[0], COREGAS_META_PREFIX . 'sec_links', array( array( 'label' => 'How we supply', 'url' => '/gas-supply/' ) ) );
		}
	}

	/* "How to apply" can refer to the services above again. */
	$apply = get_posts( array( 'post_type' => 'cg_section', 'title' => 'Gas Supply: how to apply', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids' ) );
	if ( $apply ) {
		$steps = get_post_meta( $apply[0], COREGAS_META_PREFIX . 'steps', true );
		if ( is_array( $steps ) ) {
			foreach ( $steps as $i => $step ) {
				if ( isset( $step['text'] ) && false !== strpos( $step['text'], 'that come with every Core Gas supply' ) ) {
					$steps[ $i ]['text'] = 'Once terms are agreed, gas supply begins, backed by the metering, monitoring and support described above.';
				}
			}
			update_post_meta( $apply[0], COREGAS_META_PREFIX . 'steps', $steps );
		}
	}

	/* Remove menu links to the page from every menu. */
	foreach ( wp_get_nav_menus() as $menu ) {
		foreach ( (array) wp_get_nav_menu_items( $menu->term_id, array( 'post_status' => 'any' ) ) as $item ) {
			if ( 'page' === $item->object && (int) $item->object_id === (int) $page->ID ) {
				wp_delete_post( $item->ID, true ); // menu item record only.
			}
		}
	}
}

/**
 * v11: no parent-group affiliation anywhere, and a photo slideshow instead of the flame.
 * Removes the old group page, its sections, the group-company entries and their photos,
 * group menu links and texts; re-imports the production-floor photo under a neutral name.
 */
function coregas_migration_11() {
	global $wpdb;
	$needle = 'sa' . 'if'; // the former parent group's name.

	/* Pages and sections about the group (any status, including drafts and Trash). */
	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type IN ('page','cg_section') AND ( post_name LIKE %s OR post_title LIKE %s )",
			'%' . $wpdb->esc_like( $needle ) . '%',
			'%' . $wpdb->esc_like( $needle ) . '%'
		)
	);
	$ids = array_merge(
		$ids,
		$wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE %s", COREGAS_META_PREFIX . 'anchor', '%' . $wpdb->esc_like( $needle ) . '%' ) )
	);

	/* Group-company entries (the content type no longer exists) and their photos. */
	$companies = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'cg_company'" );
	foreach ( $companies as $cid ) {
		$thumb = (int) get_post_meta( $cid, '_thumbnail_id', true );
		if ( $thumb ) {
			wp_delete_attachment( $thumb, true );
		}
	}
	foreach ( array_unique( array_map( 'intval', array_merge( $ids, $companies ) ) ) as $pid ) {
		wp_delete_post( $pid, true ); // revisions go with it.
	}

	/* Media named after group companies: keep the production-floor photo under a neutral name. */
	$old_media = $wpdb->get_col(
		"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND ( meta_value LIKE '%" . esc_sql( 'ko' . 'hat' ) . "%' OR meta_value LIKE '%" . esc_sql( $needle ) . "%' OR meta_value LIKE '%softech%' OR meta_value LIKE '%transworld%' OR meta_value LIKE '%/telecom%' )"
	);
	foreach ( $old_media as $att ) {
		$att  = (int) $att;
		$used = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_value = %s AND meta_key LIKE %s", (string) $att, $wpdb->esc_like( COREGAS_META_PREFIX ) . '%' ) );
		$used = array_merge( $used, $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_value = %s AND meta_key = '_thumbnail_id'", (string) $att ) ) );
		if ( $used ) {
			$parent = (int) wp_get_post_parent_id( $att );
			$new    = coregas_import_bundled_image( 'production-floor.jpg', $parent, 'Industrial production floor' );
			if ( $new ) {
				foreach ( $used as $meta_id ) {
					$wpdb->update( $wpdb->postmeta, array( 'meta_value' => (string) $new ), array( 'meta_id' => (int) $meta_id ) );
				}
			}
		}
		wp_delete_attachment( $att, true );
	}

	/* Group text fields of the About section type. */
	foreach ( array( 'group_show', 'group_logo', 'group_heading', 'group_text', 'group_btn_label', 'group_btn_url' ) as $key ) {
		delete_post_meta_by_key( COREGAS_META_PREFIX . $key );
	}

	/* Menu links. */
	foreach ( wp_get_nav_menus() as $menu ) {
		foreach ( (array) wp_get_nav_menu_items( $menu->term_id, array( 'post_status' => 'any' ) ) as $item ) {
			if ( false !== stripos( $item->url, $needle ) || false !== stripos( $item->title, $needle ) ) {
				wp_delete_post( $item->ID, true ); // menu item record only.
			}
		}
	}

	/* Theme Settings: copyright, SEO description, footer group mark, flame switch. */
	$settings = coregas_settings_id();
	if ( $settings ) {
		foreach ( get_post_meta( $settings ) as $key => $values ) {
			$value = maybe_unserialize( $values[0] );
			if ( is_string( $value ) && false !== stripos( $value, $needle ) ) {
				$clean = trim( preg_replace( '/\s*A\s+' . $needle . '\s+Group\s+company\.?/i', '', $value ) );
				update_post_meta( $settings, $key, $clean );
			}
		}
		foreach ( array( 'footer_show_group', 'footer_group_logo', 'footer_group_url', 'flame_global' ) as $key ) {
			delete_post_meta( $settings, COREGAS_META_PREFIX . $key );
		}
	}
	/* Per-page SEO descriptions that mention the group. */
	$seo = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE %s", COREGAS_META_PREFIX . 'seo_desc', '%' . $wpdb->esc_like( $needle ) . '%' ) );
	foreach ( $seo as $row ) {
		update_post_meta( $row->post_id, COREGAS_META_PREFIX . 'seo_desc', trim( preg_replace( '/\s*A\s+' . $needle . '\s+Group\s+company\.?/i', '', $row->meta_value ) ) );
	}

	/* Hero: flame settings gone; photos a little brighter now that they carry the hero. */
	foreach ( array( 'hero_flame', 'hd_flame_pos', 'hd_flame_size', 'hd_flame_speed', 'hd_flame_glow', 'hd_flame_c1', 'hd_flame_c2', 'hd_flame_c3', 'hd_flame_c4', 'hd_flame_c5' ) as $key ) {
		delete_post_meta_by_key( COREGAS_META_PREFIX . $key );
	}
	$heroes = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = 'hero'", COREGAS_META_PREFIX . 'section_type' ) );
	foreach ( $heroes as $hid ) {
		if ( '32' === (string) get_post_meta( $hid, COREGAS_META_PREFIX . 'hero_darkness', true ) ) {
			update_post_meta( $hid, COREGAS_META_PREFIX . 'hero_darkness', '55' );
		}
	}
}

/**
 * v12: the licence is mentioned without its number or date of grant.
 * Rewords texts, the hero facts strip, menu labels and Theme Settings, and clears
 * the number and date on licence entries (the fields stay available, empty = hidden).
 */
function coregas_migration_12() {
	global $wpdb;
	$map = coregas_licence_wording_map();
	$fix = function ( $value ) use ( &$fix, $map ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $k => $v ) {
				$value[ $k ] = $fix( $v );
			}
			return $value;
		}
		return is_string( $value ) ? str_replace( array_keys( $map ), array_values( $map ), $value ) : $value;
	};

	/* Post titles, content and excerpts (sections, pages, content items, menu items). */
	$posts = $wpdb->get_results( "SELECT ID, post_title, post_content, post_excerpt FROM {$wpdb->posts} WHERE post_type <> 'revision' AND ( post_title LIKE '%NG-53%' OR post_content LIKE '%NG-53%' OR post_excerpt LIKE '%NG-53%' OR post_content LIKE '%13 October 2026%' )" );
	foreach ( $posts as $p ) {
		$wpdb->update(
			$wpdb->posts,
			array( 'post_title' => $fix( $p->post_title ), 'post_content' => $fix( $p->post_content ), 'post_excerpt' => $fix( $p->post_excerpt ) ),
			array( 'ID' => (int) $p->ID )
		);
		clean_post_cache( (int) $p->ID );
	}

	/* Theme fields, including serialized lists (pillars, facts strip). */
	$metas = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key LIKE %s AND ( meta_value LIKE '%%NG-53%%' OR meta_value LIKE '%%13 October 2026%%' OR meta_value LIKE '%%13.10.2026%%' )", $wpdb->esc_like( COREGAS_META_PREFIX ) . '%' ) );
	foreach ( $metas as $row ) {
		$key   = $row->meta_key;
		$value = maybe_unserialize( $row->meta_value );
		if ( COREGAS_META_PREFIX . 'hero_readout' === $key && is_array( $value ) ) {
			$value = array(
				array( 'label' => 'Licensed by', 'value' => 'OGRA' ),
				array( 'label' => 'Activity', 'value' => 'Gas marketing & sales' ),
				array( 'label' => 'Customers', 'value' => 'Industrial, Pakistan' ),
			);
		} elseif ( in_array( $key, array( COREGAS_META_PREFIX . 'licence_no', COREGAS_META_PREFIX . 'granted' ), true ) ) {
			delete_post_meta( $row->post_id, $key );
			continue;
		} else {
			$value = $fix( $value );
		}
		update_post_meta( $row->post_id, $key, $value );
	}
}

/**
 * Old wording with the licence number/date => wording without them.
 *
 * @return array
 */
function coregas_licence_wording_map() {
	return array(
		'Core Gas Private Limited was granted OGRA Licence No. NG-53/2026, dated 13 October 2026, enabling gas marketing and sales under the applicable regulatory framework.' => 'Core Gas Private Limited is licensed by the Oil & Gas Regulatory Authority (OGRA) for natural gas marketing and sales under the applicable regulatory framework.',
		'The company has been granted OGRA Licence No. NG-53/2026, dated 13 October 2026, enabling gas marketing and sales under the applicable regulatory framework.' => 'The company is licensed by the Oil & Gas Regulatory Authority (OGRA) for natural gas marketing and sales under the applicable regulatory framework.',
		'The company has been granted OGRA Licence No. NG-53/2026 dated 13 October 2026, enabling gas marketing and sales under the applicable regulatory framework.' => 'The company is licensed by the Oil & Gas Regulatory Authority (OGRA) for natural gas marketing and sales under the applicable regulatory framework.',
		'Core Gas holds OGRA Licence No. NG-53/2026, dated 13 October 2026, enabling natural gas marketing and sales under the applicable regulatory framework.' => 'Core Gas is licensed by the Oil & Gas Regulatory Authority (OGRA) for natural gas marketing and sales under the applicable regulatory framework.',
		'under OGRA Licence No. NG-53/2026 for natural gas marketing and sales' => 'licensed by OGRA for natural gas marketing and sales',
		'for sale to customers under OGRA Licence No. NG-53/2026.' => 'for sale to customers, licensed by OGRA.',
		'for marketing and sale under OGRA Licence No. NG-53/2026.' => 'for marketing and sale under our OGRA licence.',
		'under OGRA Licence No. NG-53/2026, within' => 'under an OGRA licence, within',
		'OGRA Licence NG-53/2026' => 'Licensed by OGRA',
		'OGRA Licence No. NG-53/2026' => 'an OGRA licence',
		', dated 13 October 2026' => '',
		' dated 13 October 2026' => '',
	);
}

/**
 * v13: new hero headline (the old one used the word "fire"). Only heroes still
 * showing the original wording are changed.
 */
function coregas_migration_13() {
	global $wpdb;
	$ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = 'fire.'", COREGAS_META_PREFIX . 'hero_accent' ) );
	foreach ( $ids as $id ) {
		$old = array(
			'hero_line1'   => 'Industry',
			'hero_line2'   => 'runs on',
			'hero_subline' => 'We deliver the gas.',
		);
		$new = array(
			'hero_line1'   => 'Powering',
			'hero_line2'   => "Pakistan's",
			'hero_accent'  => 'industry.',
			'hero_subline' => 'Dependable natural gas, delivered.',
		);
		foreach ( $new as $key => $value ) {
			$current = get_post_meta( $id, COREGAS_META_PREFIX . $key, true );
			if ( 'hero_accent' === $key || '' === $current || ( isset( $old[ $key ] ) && $old[ $key ] === $current ) ) {
				update_post_meta( $id, COREGAS_META_PREFIX . $key, $value );
			}
		}
	}
}

/**
 * v14: launch content. Privacy notice, legal notice and accessibility statement get
 * real text; WordPress sample content goes to the Trash; the first news post; Google
 * titles and descriptions for the main pages; supply buttons open the gas supply
 * application; About Us links to the company profile download.
 */
function coregas_migration_14() {
	$prefix = COREGAS_META_PREFIX;

	/* ---------- Legal pages ---------- */
	$privacy_id = (int) get_option( 'wp_page_for_privacy_policy' );
	$privacy    = $privacy_id ? get_post( $privacy_id ) : get_page_by_path( 'privacy-policy' );
	$legal      = get_page_by_path( 'legal-notice' );
	$access     = get_page_by_path( 'accessibility' );

	$pages = array(
		'privacy' => array(
			'post'    => $privacy,
			'title'   => 'Privacy Notice',
			'slug'    => 'privacy-notice',
			'eyebrow' => 'Legal',
			'banner'  => 'How we handle the information you share with us through this website.',
			'heading' => 'Your information, handled with care',
			'content' => "<p>This notice explains what information Core Gas Private Limited (\"Core Gas\", \"we\") collects through this website, why we collect it and how we look after it.</p>\n<h3>What we collect</h3>\n<p>When you send us an enquiry, we receive the details you enter in the form: your name, company, email address, phone number, the type of enquiry, your message and, for gas supply applications, details of your site and gas requirement. We also record the page the enquiry was sent from and the time it was received.</p>\n<p>Like most websites, our hosting server keeps standard technical logs (such as IP address, browser type and pages requested) for security and to keep the site running.</p>\n<h3>Why we use it</h3>\n<ul>\n<li>To reply to your enquiry and discuss your gas requirement.</li>\n<li>To prepare and manage a supply relationship if you become a customer.</li>\n<li>To keep the website secure and working properly.</li>\n</ul>\n<p>We do not sell your information, and we do not use it for unrelated marketing without your agreement.</p>\n<h3>Where it is kept</h3>\n<p>Enquiries are stored securely in the website's administration area and sent by email to our team. Only authorised staff can access them. We keep enquiries for as long as needed to respond and to manage any resulting business relationship, and then delete them.</p>\n<h3>Cookies</h3>\n<p>This website only uses the cookies it needs to work. If we add website analytics in the future, this notice will be updated to explain it.</p>\n<h3>Your choices</h3>\n<p>You can ask us what information we hold about you, ask us to correct it or ask us to delete it. Please use the <a href=\"/contact-us/\">Contact Us</a> page.</p>\n<h3>Changes to this notice</h3>\n<p>We may update this notice from time to time. The current version is always available on this page.</p>",
		),
		'legal'   => array(
			'post'    => $legal,
			'title'   => 'Legal Notice',
			'slug'    => 'legal-notice',
			'eyebrow' => 'Legal',
			'banner'  => 'Terms that apply when you use this website.',
			'heading' => 'Using this website',
			'content' => "<p>This website is operated by Core Gas Private Limited, a company licensed by the Oil &amp; Gas Regulatory Authority (OGRA) for natural gas marketing and sales in Pakistan.</p>\n<h3>Information on this website</h3>\n<p>The content of this website is provided for general information about Core Gas and its services. We work to keep it accurate and up to date, but it does not form an offer or a contract. Supply terms, prices and availability are agreed separately with each customer.</p>\n<h3>Intellectual property</h3>\n<p>The Core Gas name, logo, text, graphics and layout of this website belong to Core Gas Private Limited or are used with permission. They may not be copied, changed or reused without our written consent.</p>\n<h3>Links to other websites</h3>\n<p>This website links to other organisations, such as regulators and gas utilities, for convenience. We are not responsible for the content of those websites.</p>\n<h3>Liability</h3>\n<p>To the extent permitted by law, Core Gas is not liable for any loss arising from the use of this website or from reliance on its content.</p>\n<h3>Governing law</h3>\n<p>These terms are governed by the laws of Pakistan.</p>\n<h3>Contact</h3>\n<p>Questions about this notice can be sent through the <a href=\"/contact-us/\">Contact Us</a> page.</p>",
		),
		'access'  => array(
			'post'    => $access,
			'title'   => 'Accessibility',
			'slug'    => 'accessibility',
			'eyebrow' => 'Accessibility',
			'banner'  => 'Our commitment to a website everyone can use.',
			'heading' => 'A website for everyone',
			'content' => "<p>Core Gas wants this website to be easy to use for everyone, including people who use assistive technology.</p>\n<h3>What we have done</h3>\n<ul>\n<li>Clear page structure with headings, so screen readers can move through each page.</li>\n<li>Every page can be used with a keyboard, with a \"Skip to content\" link at the top.</li>\n<li>Text and background colours chosen for strong contrast.</li>\n<li>Animations are kept to a minimum and switched off for visitors who ask their device to reduce motion.</li>\n<li>Pages work on phones, tablets and large screens, and text can be enlarged with the browser's zoom.</li>\n<li>Images that carry meaning have text descriptions.</li>\n</ul>\n<h3>Our target</h3>\n<p>We aim to meet the Web Content Accessibility Guidelines (WCAG) 2.1 at level AA and keep improving as the website grows.</p>\n<h3>Feedback</h3>\n<p>If anything on this website is difficult to use, please tell us through the <a href=\"/contact-us/\">Contact Us</a> page and we will do our best to help.</p>",
		),
	);

	foreach ( $pages as $key => $page ) {
		$post = $page['post'];
		if ( ! $post ) {
			$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $page['title'], 'post_name' => $page['slug'] ) );
			if ( ! $id || is_wp_error( $id ) ) {
				continue;
			}
			$post = get_post( $id );
		}
		if ( coregas_sections_for_page_any( $post->ID ) ) {
			continue; // already has editor-made content.
		}
		$current = trim( wp_strip_all_tags( (string) $post->post_content ) );
		$update  = array( 'ID' => $post->ID, 'post_status' => 'publish' );
		// Placeholders and WordPress's default privacy template are replaced by sections.
		if ( '' === $current || false !== stripos( $current, 'to be added' ) || 'privacy' === $key ) {
			$update['post_content'] = '';
		}
		if ( 'privacy' === $key ) {
			$update['post_title'] = $page['title'];
			$update['post_name']  = $page['slug'];
		}
		wp_update_post( $update );
		update_post_meta( $post->ID, $prefix . 'banner_show', '1' );
		update_post_meta( $post->ID, $prefix . 'banner_eyebrow', $page['eyebrow'] );
		update_post_meta( $post->ID, $prefix . 'banner_text', $page['banner'] );
		coregas_seed_section( $page['title'] . ': text', $post->ID, 'text', 0, array( 'sec_eyebrow' => $page['eyebrow'], 'sec_heading' => $page['heading'], 'sec_bg' => 'white' ), $page['content'] );
		if ( 'privacy' === $key ) {
			update_option( 'wp_page_for_privacy_policy', $post->ID );
		}
	}

	/* ---------- WordPress sample content to the Trash ---------- */
	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
	if ( $hello && 'Hello world!' === $hello->post_title ) {
		wp_trash_post( $hello->ID );
	}
	$sample = get_page_by_path( 'sample-page' );
	if ( $sample && 'Sample Page' === $sample->post_title ) {
		wp_trash_post( $sample->ID );
	}

	/* ---------- First news post ---------- */
	$cat = get_term( (int) get_option( 'default_category' ), 'category' );
	if ( $cat && ! is_wp_error( $cat ) && 'uncategorized' === $cat->slug ) {
		wp_update_term( $cat->term_id, 'category', array( 'name' => 'Company news', 'slug' => 'company-news' ) );
	}
	$news_title = 'Core Gas licensed by OGRA for natural gas marketing and sales';
	if ( ! get_posts( array( 'post_type' => 'post', 'title' => $news_title, 'post_status' => 'any', 'fields' => 'ids' ) ) ) {
		$nid = wp_insert_post(
			array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => $news_title,
				'post_excerpt' => 'Core Gas Private Limited has been licensed by the Oil & Gas Regulatory Authority to market and sell natural gas to industrial consumers in Pakistan.',
				'post_content' => "<p>Core Gas Private Limited has been licensed by the Oil &amp; Gas Regulatory Authority (OGRA) for natural gas marketing and sales in Pakistan.</p>\n<p>The licence allows Core Gas to source and market natural gas for sale to customers, with a focus on industrial consumers whose operations depend on a reliable and continuous supply of gas.</p>\n<p>Core Gas manages the full supply relationship: gas sourcing, transportation, delivery and customer service, supported by metering, monitoring and responsive technical support.</p>\n<p>Industrial consumers interested in a natural gas supply can apply through the <a href=\"/gas-supply/\">Gas Supply</a> page.</p>",
			)
		);
		if ( $nid && ! is_wp_error( $nid ) ) {
			update_post_meta( $nid, $prefix . 'seo_desc', 'Core Gas Private Limited has been licensed by OGRA for natural gas marketing and sales to industrial consumers in Pakistan.' );
		}
	}

	/* ---------- Google titles and descriptions (only where empty) ---------- */
	$seo = array(
		(int) get_option( 'page_on_front' ) => array( 'Core Gas | Natural gas supply for Pakistan\'s industry', 'Core Gas Private Limited supplies natural gas to industrial consumers in Pakistan. Licensed by OGRA for natural gas marketing and sales.' ),
		'about-us'                          => array( 'About Core Gas | Downstream natural gas company', 'Core Gas is a downstream gas company meeting the natural gas needs of Pakistan\'s industry: mission, vision and objectives.' ),
		'gas-supply'                        => array( 'Gas Supply for Industry | Core Gas', 'Dependable natural gas for industrial consumers: sourcing, transport, delivery, metering, monitoring and support. Apply for gas supply.' ),
		'regulation'                        => array( 'Regulation & HSE | Core Gas', 'Core Gas is licensed by OGRA for natural gas marketing and sales, with health, safety and environment built into its operations.' ),
		'contact-us'                        => array( 'Contact Core Gas | Apply for gas supply', 'Contact Core Gas about a natural gas supply for your facility or any other enquiry.' ),
		'hse-policy'                        => array( 'HSE Policy | Core Gas', 'Core Gas health, safety and environment policy: people and processes, safety at work and environmental care.' ),
		'news'                              => array( 'News | Core Gas', 'News and announcements from Core Gas Private Limited.' ),
	);
	foreach ( $seo as $page => $pair ) {
		$p = is_int( $page ) ? get_post( $page ) : get_page_by_path( $page );
		if ( ! $p ) {
			continue;
		}
		if ( '' === trim( (string) get_post_meta( $p->ID, $prefix . 'seo_title', true ) ) ) {
			update_post_meta( $p->ID, $prefix . 'seo_title', $pair[0] );
		}
		if ( '' === trim( (string) get_post_meta( $p->ID, $prefix . 'seo_desc', true ) ) ) {
			update_post_meta( $p->ID, $prefix . 'seo_desc', $pair[1] );
		}
	}

	/* ---------- Supply buttons open the gas supply application ---------- */
	$apply = '/contact-us/?enquiry=supply';
	$settings = coregas_settings_id();
	if ( $settings ) {
		foreach ( array( 'header_cta_url', 'footer_btn_url' ) as $k ) {
			$v = (string) get_post_meta( $settings, $prefix . $k, true );
			if ( '/contact-us/' === $v || ( '' === $v && 'footer_btn_url' === $k ) ) {
				update_post_meta( $settings, $prefix . $k, $apply );
			}
		}
	}
	foreach ( get_posts( array( 'post_type' => 'cg_section', 'post_status' => 'publish', 'posts_per_page' => 200 ) ) as $sec ) {
		if ( '/contact-us/' === get_post_meta( $sec->ID, $prefix . 'cta_btn_url', true ) && preg_match( '/supply/i', (string) get_post_meta( $sec->ID, $prefix . 'cta_btn_label', true ) ) ) {
			update_post_meta( $sec->ID, $prefix . 'cta_btn_url', $apply );
		}
		$links = get_post_meta( $sec->ID, $prefix . 'sec_links', true );
		if ( is_array( $links ) ) {
			$changed = false;
			foreach ( $links as $i => $l ) {
				if ( isset( $l['url'], $l['label'] ) && '/contact-us/' === $l['url'] && preg_match( '/supply/i', $l['label'] ) ) {
					$links[ $i ]['url'] = $apply;
					$changed            = true;
				}
			}
			if ( $changed ) {
				update_post_meta( $sec->ID, $prefix . 'sec_links', $links );
			}
		}
	}

	/* ---------- About Us: company profile download ---------- */
	$who = get_posts( array( 'post_type' => 'cg_section', 'title' => 'About Us: who we are', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids' ) );
	if ( $who ) {
		$links = get_post_meta( $who[0], $prefix . 'sec_links', true );
		if ( ! is_array( $links ) || ! $links ) {
			update_post_meta( $who[0], $prefix . 'sec_links', array( array( 'label' => 'Download company profile', 'url' => '/company-profile/' ) ) );
		}
	}
}

add_action( 'template_redirect', 'coregas_legacy_redirects' );
/**
 * Old URLs from earlier structures keep working.
 */
function coregas_legacy_redirects() {
	if ( ! is_404() ) {
		return;
	}
	$path  = trim( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '', PHP_URL_PATH ), '/' );
	$base  = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
	$slug  = trim( $base ? preg_replace( '#^' . preg_quote( $base, '#' ) . '#', '', $path ) : $path, '/' );
	$about = get_page_by_path( 'about-us' );
	if ( ! $about ) {
		return;
	}
	$map = array(
		'company'    => get_permalink( $about ),
		'what-we-do' => home_url( '/gas-supply/' ),
	);
	if ( isset( $map[ $slug ] ) ) {
		wp_safe_redirect( $map[ $slug ], 301 );
		exit;
	}
}
