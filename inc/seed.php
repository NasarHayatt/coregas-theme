<?php
/**
 * Starter content, created once when the theme is first activated.
 *
 * All copy comes from the Core Gas company profile. Nothing is overwritten:
 * existing pages/menus with the same slug/name are reused, and the whole
 * routine runs only once (option `coregas_seeded`).
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_switch_theme', 'coregas_seed' );
/**
 * Create starter content.
 */
function coregas_seed() {
	if ( get_option( 'coregas_seeded' ) ) {
		flush_rewrite_rules();
		return;
	}
	coregas_register_post_types();
	coregas_settings_id( true );

	/* ---------- Pages ---------- */
	$pages = array(
		'home'          => array( 'Home', '', array( 'banner_show' => '0' ) ),
		'company'       => array( 'Company', '', array( 'banner_eyebrow' => 'About Core Gas', 'banner_text' => 'A downstream gas company established to meet the natural gas requirements of industrial consumers in Pakistan.' ) ),
		'gas-supply'    => array( 'Gas supply', '', array( 'banner_eyebrow' => 'Gas supply chain', 'banner_title' => 'Connecting natural gas supply with industrial demand', 'banner_text' => 'Gas sourcing, transportation, delivery and customer service.' ) ),
		'regulation'    => array( 'Regulation', '', array( 'banner_eyebrow' => 'Regulatory approval', 'banner_title' => 'Licensed by OGRA', 'banner_text' => 'Core Gas is licensed by the Oil & Gas Regulatory Authority (OGRA) for natural gas marketing and sales under the applicable regulatory framework.' ) ),
		'contact-us'    => array( 'Contact us', 'Tell us about your site and your gas requirements, and our commercial team will get back to you.', array( 'banner_eyebrow' => 'Get in touch', 'banner_title' => "Let's talk gas" ) ),
		'news'          => array( 'News', '', array() ),
		'legal-notice'  => array( 'Legal notice', 'Legal notice content to be added.', array() ),
		'accessibility' => array( 'Accessibility', 'Accessibility statement to be added.', array() ),
	);
	$ids = array();
	foreach ( $pages as $slug => $p ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			$ids[ $slug ] = $existing->ID;
			continue;
		}
		$ids[ $slug ] = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $p[0],
				'post_name'    => $slug,
				'post_content' => $p[1],
			)
		);
		foreach ( $p[2] as $k => $v ) {
			update_post_meta( $ids[ $slug ], COREGAS_META_PREFIX . $k, $v );
		}
	}
	update_post_meta( $ids['contact-us'], '_wp_page_template', 'page-templates/contact.php' );
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ids['home'] );
	update_option( 'page_for_posts', $ids['news'] );

	/* ---------- Page Sections ---------- */
	coregas_seed_section( 'Home: hero', $ids['home'], 'hero', 0 );
	coregas_seed_section( 'Home: about', $ids['home'], 'about', 1 );

	coregas_seed_section(
		'Company: who we are',
		$ids['company'],
		'text',
		0,
		array( 'sec_eyebrow' => 'Who we are', 'sec_heading' => "Building a dependable natural gas supply platform for Pakistan's industry", 'sec_bg' => 'light' ),
		"<p>Core Gas Private Limited is a downstream gas company established to meet the natural gas requirements of industrial consumers in Pakistan.</p>\n<p>The company is licensed by the Oil & Gas Regulatory Authority (OGRA) for natural gas marketing and sales under the applicable regulatory framework. Our current focus is natural gas supply to industrial consumers in Pakistan.</p>\n<p>We work by six principles: regulation, sourcing, supply, reliability, customer service and partnerships.</p>"
	);
	coregas_seed_section( 'Company: call to action', $ids['company'], 'cta', 1, array( 'sec_heading' => 'Need a dependable gas supply for your facility?', 'sec_intro' => 'Tell us about your site and your gas requirements.', 'sec_bg' => 'maroon' ) );

	coregas_seed_section( 'Gas supply: services', $ids['gas-supply'], 'cards', 0, array( 'sec_eyebrow' => 'How we supply', 'sec_heading' => 'From source to service', 'sec_bg' => 'light', 'cards_source' => 'cg_service' ) );
	coregas_seed_section( 'Gas supply: call to action', $ids['gas-supply'], 'cta', 1, array( 'sec_heading' => 'Talk to us about your gas requirements', 'sec_bg' => 'maroon' ) );

	coregas_seed_section( 'Regulation: licences', $ids['regulation'], 'cards', 0, array( 'sec_eyebrow' => 'Licences & approvals', 'sec_heading' => 'Growth within the regulatory framework', 'sec_bg' => 'light', 'cards_source' => 'cg_licence' ) );

	/* ---------- Content items ---------- */
	$services = array(
		array( 'Source', 'Securing natural gas for marketing and sale under our OGRA licence.' ),
		array( 'Transport', 'Arranging the movement of gas from the point of supply towards our customers.' ),
		array( 'Deliver', 'Delivering gas to industrial consumers where their operations need it.' ),
		array( 'Serve', 'Supporting customers with responsive service and a dependable supply relationship.' ),
	);
	foreach ( $services as $i => $s ) {
		coregas_seed_item( 'cg_service', $s[0], $s[1], $i, array( 'service_tag' => 'Gas supply chain' ) );
	}

	coregas_seed_item(
		'cg_licence',
		'OGRA natural gas marketing & sales licence',
		'Granted by the Oil & Gas Regulatory Authority, enabling Core Gas to undertake natural gas marketing and sales in Pakistan under the applicable regulatory framework.',
		0,
		array( 'issuer' => 'Oil & Gas Regulatory Authority (OGRA)', 'activity' => 'Natural gas marketing & sales', 'status' => 'active' )
	);


	/* ---------- Menus ---------- */
	$privacy = (int) get_option( 'wp_page_for_privacy_policy' );
	$menus   = array(
		'primary'  => array(
			'Main menu',
			array( array( 'page', $ids['home'], 'Home' ), array( 'page', $ids['company'] ), array( 'page', $ids['gas-supply'] ), array( 'page', $ids['regulation'] ), array( 'page', $ids['contact-us'] ) ),
		),
		'footer_1' => array( 'Get in touch', array( array( 'page', $ids['contact-us'] ), array( 'page', $ids['contact-us'], 'Request gas supply' ) ) ),
		'footer_2' => array( 'Company', array( array( 'page', $ids['company'], 'About Core Gas' ), array( 'page', $ids['regulation'] ) ) ),
		'footer_3' => array( 'Gas supply', array( array( 'page', $ids['gas-supply'], 'How we supply' ), array( 'url', get_post_type_archive_link( 'cg_industry' ), 'Industries we serve' ), array( 'page', $ids['news'] ) ) ),
		'footer_4' => array( 'Licence', array( array( 'page', $ids['regulation'], 'Licensed by OGRA' ) ) ),
		'legal'    => array( 'Legal', array_filter( array( array( 'page', $ids['legal-notice'] ), $privacy ? array( 'page', $privacy, 'Privacy notice' ) : null, array( 'page', $ids['accessibility'] ), array( 'page', $ids['contact-us'] ) ) ) ),
	);
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	foreach ( $menus as $location => $menu ) {
		$menu_obj = wp_get_nav_menu_object( $menu[0] );
		if ( $menu_obj ) {
			$locations[ $location ] = $menu_obj->term_id;
			continue;
		}
		$menu_id = wp_create_nav_menu( $menu[0] );
		if ( is_wp_error( $menu_id ) ) {
			continue;
		}
		foreach ( $menu[1] as $pos => $item ) {
			$data = array(
				'menu-item-status'   => 'publish',
				'menu-item-position' => $pos + 1,
			);
			if ( 'page' === $item[0] ) {
				$data['menu-item-type']      = 'post_type';
				$data['menu-item-object']    = 'page';
				$data['menu-item-object-id'] = $item[1];
			} else {
				$data['menu-item-type'] = 'custom';
				$data['menu-item-url']  = $item[1];
			}
			if ( ! empty( $item[2] ) ) {
				$data['menu-item-title'] = $item[2];
			}
			if ( ! empty( $item[3] ) ) {
				$data['menu-item-target'] = $item[3];
			}
			wp_update_nav_menu_item( $menu_id, 0, $data );
		}
		$locations[ $location ] = $menu_id;
	}
	set_theme_mod( 'nav_menu_locations', $locations );

	update_option( 'coregas_seeded', COREGAS_VERSION );
	flush_rewrite_rules();
}

/**
 * Create one Page Section with its defaults written to meta.
 *
 * @param string $title     Admin title.
 * @param int    $page_id   Page.
 * @param string $type      Section type.
 * @param int    $order     Menu order.
 * @param array  $overrides Field values.
 * @param string $content   Editor content.
 * @param array  $links     Buttons repeater.
 * @return int Section ID.
 */
function coregas_seed_section( $title, $page_id, $type, $order, $overrides = array(), $content = '', $links = array() ) {
	$id = wp_insert_post(
		array(
			'post_type'    => 'cg_section',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_content' => $content,
			'menu_order'   => $order,
		)
	);
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	$values = array_merge(
		array(
			'section_type' => $type,
			'page'         => (string) $page_id,
		),
		$overrides
	);
	// Store the schema defaults of every group relevant to this type, so content no longer depends on code defaults.
	foreach ( coregas_groups_for( 'cg_section' ) as $group ) {
		if ( ! empty( $group['show_if'] ) && ! in_array( $type, $group['show_if']['section_type'], true ) ) {
			continue;
		}
		foreach ( $group['fields'] as $field ) {
			$val = array_key_exists( $field['key'], $values ) ? $values[ $field['key'] ] : ( isset( $field['default'] ) ? $field['default'] : '' );
			update_post_meta( $id, COREGAS_META_PREFIX . $field['key'], $val );
		}
	}
	if ( $links ) {
		update_post_meta( $id, COREGAS_META_PREFIX . 'sec_links', $links );
	}
	return (int) $id;
}

/**
 * Create one content item unless a post with that title exists.
 *
 * @param string $type    Post type.
 * @param string $title   Title.
 * @param string $excerpt Excerpt and content.
 * @param int    $order   Menu order.
 * @param array  $meta    Field values.
 */
function coregas_seed_item( $type, $title, $excerpt, $order, $meta ) {
	$exists = get_posts(
		array(
			'post_type'      => $type,
			'title'          => $title,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( $exists ) {
		return;
	}
	$id = wp_insert_post(
		array(
			'post_type'    => $type,
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_excerpt' => $excerpt,
			'post_content' => '<p>' . esc_html( $excerpt ) . '</p>',
			'menu_order'   => $order,
		)
	);
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, COREGAS_META_PREFIX . $k, $v );
	}
}
