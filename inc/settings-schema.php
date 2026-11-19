<?php
/**
 * Theme Settings fields. Every group carries a `tab`; the settings screen shows
 * one tab at a time (see inc/admin-guide.php).
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Archive-type banners editable from Theme Settings.
 *
 * @return array key => label
 */
function coregas_archive_contexts() {
	return array(
		'cg_service'  => __( 'Supply Services list', 'coregas' ),
		'cg_industry' => __( 'Industries list', 'coregas' ),
		'cg_licence'  => __( 'Licences list', 'coregas' ),
		'news'        => __( 'News', 'coregas' ),
		'search'      => __( 'Search results', 'coregas' ),
	);
}

/**
 * Editable front-end wording. key => [ label in admin, default text ].
 *
 * @return array
 */
function coregas_label_defaults() {
	return array(
		'read_more'       => array( __( '"Read more" link', 'coregas' ), 'Read more' ),
		'visit_website'   => array( __( 'Company "Visit website" button', 'coregas' ), 'Visit website' ),
		'back_to_list'    => array( __( 'Back link on detail pages ({list} = list name)', 'coregas' ), 'All {list}' ),
		'empty_list'      => array( __( 'Message when a list has no items', 'coregas' ), 'Nothing published here yet.' ),
		'search_title'    => array( __( 'Search results title ({query} = search words)', 'coregas' ), 'Results for "{query}"' ),
		'licence_no'      => array( __( 'Licence: "Licence number"', 'coregas' ), 'Licence number' ),
		'licensed'        => array( __( 'Licence panel: big word shown when no licence number is set', 'coregas' ), 'Licensed' ),
		'issued_by'       => array( __( 'Licence: "Issued by"', 'coregas' ), 'Issued by' ),
		'date_of_grant'   => array( __( 'Licence: "Date of grant"', 'coregas' ), 'Date of grant' ),
		'activity'        => array( __( 'Licence: "Licensed activity"', 'coregas' ), 'Licensed activity' ),
		'licensee'        => array( __( 'Licence: "Licensee"', 'coregas' ), 'Licensee' ),
		'status'          => array( __( 'Licence: "Status"', 'coregas' ), 'Status' ),
		'office'          => array( __( 'Contact: "Office"', 'coregas' ), 'Office' ),
		'phone'           => array( __( 'Contact: "Phone"', 'coregas' ), 'Phone' ),
		'email'           => array( __( 'Contact: "Email"', 'coregas' ), 'Email' ),
		'skip'            => array( __( 'Keyboard "Skip to content" link', 'coregas' ), 'Skip to content' ),
		'menu'            => array( __( 'Mobile menu button (screen readers)', 'coregas' ), 'Menu' ),
	);
}

/**
 * Settings groups.
 *
 * @return array
 */
function coregas_settings_schema() {
	$archive_fields = array();
	foreach ( coregas_archive_contexts() as $key => $label ) {
		$archive_fields[] = array( 'key' => 'arch_' . $key . '_heading', 'type' => 'heading', 'label' => $label );
		$archive_fields[] = array( 'key' => 'arch_' . $key . '_eyebrow', 'type' => 'text', 'label' => __( 'Small label', 'coregas' ) );
		$archive_fields[] = array( 'key' => 'arch_' . $key . '_title', 'type' => 'text', 'label' => __( 'Title', 'coregas' ), 'desc' => __( 'Leave empty for the default.', 'coregas' ) );
		$archive_fields[] = array( 'key' => 'arch_' . $key . '_text', 'type' => 'textarea', 'label' => __( 'Intro text', 'coregas' ), 'rows' => 2 );
		$archive_fields[] = array( 'key' => 'arch_' . $key . '_image', 'type' => 'image', 'label' => __( 'Banner photo', 'coregas' ) );
	}
	$label_fields = array();
	foreach ( coregas_label_defaults() as $key => $l ) {
		$label_fields[] = array( 'key' => 'label_' . $key, 'type' => 'text', 'label' => $l[0], 'default' => $l[1] );
	}

	return array(
		/* ---------------- Brand ---------------- */
		array(
			'id'          => 'brand',
			'tab'         => __( 'Brand', 'coregas' ),
			'title'       => __( 'Logo & identity', 'coregas' ),
			'description' => __( 'The official logo is used exactly as supplied.', 'coregas' ),
			'fields'      => array(
				array( 'key' => 'company_name', 'type' => 'text', 'label' => __( 'Company name', 'coregas' ), 'default' => 'Core Gas Private Limited' ),
				array( 'key' => 'logo', 'type' => 'image', 'label' => __( 'Header logo', 'coregas' ), 'desc' => __( 'Leave empty to use the bundled official Core Gas logo.', 'coregas' ) ),
				array( 'key' => 'logo_height', 'type' => 'number', 'label' => __( 'Header logo height (px)', 'coregas' ), 'default' => '38' ),
				array( 'key' => 'logo_plate', 'type' => 'checkbox', 'label' => __( 'Show the logo on a white plate in the dark header', 'coregas' ), 'default' => '1' ),
				array( 'key' => 'footer_logo', 'type' => 'image', 'label' => __( 'Footer logo', 'coregas' ), 'desc' => __( 'Leave empty to reuse the header logo.', 'coregas' ) ),
				array( 'key' => 'favicon', 'type' => 'image', 'label' => __( 'Browser tab icon (favicon)', 'coregas' ), 'desc' => __( 'Square image, at least 512 × 512 px.', 'coregas' ) ),
				array( 'key' => 'login_brand', 'type' => 'checkbox', 'label' => __( 'Show the Core Gas logo and colours on the WordPress login page', 'coregas' ), 'default' => '1' ),
			),
		),
		array(
			'id'          => 'images',
			'tab'         => __( 'Brand', 'coregas' ),
			'title'       => __( 'Default photos', 'coregas' ),
			'description' => __( 'Used wherever a page, item or section has no photo of its own. Until you add photos here, placeholder industrial photos are used.', 'coregas' ),
			'fields'      => array(
				array( 'key' => 'banner_default', 'type' => 'image', 'label' => __( 'Default page banner photo', 'coregas' ), 'desc' => __( 'Shown in black and white at the top of inner pages without their own banner photo.', 'coregas' ) ),
				array(
					'key'       => 'stock_images',
					'type'      => 'repeater',
					'label'     => __( 'Fallback photos (used in rotation)', 'coregas' ),
					'row_label' => __( 'Photo', 'coregas' ),
					'sub'       => array( array( 'key' => 'image', 'type' => 'image', 'label' => __( 'Photo', 'coregas' ) ) ),
				),
			),
		),

		/* ---------------- Colours ---------------- */
		array(
			'id'          => 'colors',
			'tab'         => __( 'Colours', 'coregas' ),
			'title'       => __( 'Brand colours', 'coregas' ),
			'description' => __( 'Defaults are the exact colours sampled from the Core Gas logo. Changes apply across the whole site.', 'coregas' ),
			'fields'      => array(
				array( 'key' => 'color_maroon', 'type' => 'color', 'label' => __( 'Brand maroon (headings, panels)', 'coregas' ), 'default' => '#48050b' ),
				array( 'key' => 'color_primary', 'type' => 'color', 'label' => __( 'Accent (buttons, highlights, labels)', 'coregas' ), 'default' => '#e36f00' ),
				array( 'key' => 'color_grey', 'type' => 'color', 'label' => __( 'Body text on light backgrounds', 'coregas' ), 'default' => '#544742' ),
				array( 'key' => 'color_crimson', 'type' => 'color', 'label' => __( 'Logo crimson / button hover', 'coregas' ), 'default' => '#800024' ),
				array( 'key' => 'color_red', 'type' => 'color', 'label' => __( 'Logo red', 'coregas' ), 'default' => '#d20600' ),
				array( 'key' => 'color_amber', 'type' => 'color', 'label' => __( 'Logo amber / licence number', 'coregas' ), 'default' => '#f1ad00' ),
				array( 'key' => 'color_yellow', 'type' => 'color', 'label' => __( 'Logo yellow', 'coregas' ), 'default' => '#fcef00' ),
			),
		),
		array(
			'id'     => 'colors_areas',
			'tab'    => __( 'Colours', 'coregas' ),
			'title'  => __( 'Backgrounds & areas', 'coregas' ),
			'fields' => array(
				array( 'key' => 'color_surface', 'type' => 'color', 'label' => __( 'Content section background', 'coregas' ), 'default' => '#ffffff' ),
				array( 'key' => 'color_dark', 'type' => 'color', 'label' => __( 'Dark sections / hero background', 'coregas' ), 'default' => '#120d0d' ),
				array( 'key' => 'color_light', 'type' => 'color', 'label' => __( 'Text on dark areas', 'coregas' ), 'default' => '#f4f1ee' ),
				array( 'key' => 'color_header', 'type' => 'color', 'label' => __( 'Header background (after scrolling)', 'coregas' ), 'default' => '#48050b' ),
				array( 'key' => 'color_btn_text', 'type' => 'color', 'label' => __( 'Text on accent buttons', 'coregas' ), 'default' => '#120d0d' ),
				array( 'key' => 'color_footer_bg', 'type' => 'color', 'label' => __( 'Footer background', 'coregas' ), 'default' => '#120d0d' ),
				array( 'key' => 'color_footer_head', 'type' => 'color', 'label' => __( 'Footer column headings', 'coregas' ), 'default' => '#f4f1ee' ),
				array( 'key' => 'color_footer_text', 'type' => 'color', 'label' => __( 'Footer links & text', 'coregas' ), 'default' => '#c9bfba' ),
			),
		),

		/* ---------------- Typography & layout ---------------- */
		array(
			'id'     => 'typography',
			'tab'    => __( 'Typography & layout', 'coregas' ),
			'title'  => __( 'Typography', 'coregas' ),
			'fields' => array(
				array( 'key' => 'font_heading', 'type' => 'select', 'label' => __( 'Heading font', 'coregas' ), 'choices' => coregas_font_choices( coregas_heading_fonts() ), 'default' => 'barlow' ),
				array( 'key' => 'font_body', 'type' => 'select', 'label' => __( 'Body font', 'coregas' ), 'choices' => coregas_font_choices( coregas_body_fonts() ), 'default' => 'geist' ),
				array( 'key' => 'font_base_size', 'type' => 'number', 'label' => __( 'Body text size (px)', 'coregas' ), 'default' => '17' ),
				array( 'key' => 'heading_scale', 'type' => 'number', 'label' => __( 'Heading size (%)', 'coregas' ), 'default' => '100', 'desc' => __( '100 = default. 85 makes all headings smaller, 115 larger.', 'coregas' ) ),
				array(
					'key'     => 'heading_case',
					'type'    => 'select',
					'label'   => __( 'Heading capitals', 'coregas' ),
					'default' => 'font',
					'choices' => array( 'font' => __( 'Font default', 'coregas' ), 'none' => __( 'Normal (Sentence case)', 'coregas' ), 'uppercase' => __( 'ALL CAPITALS', 'coregas' ) ),
				),
			),
		),
		array(
			'id'     => 'layout',
			'tab'    => __( 'Typography & layout', 'coregas' ),
			'title'  => __( 'Layout', 'coregas' ),
			'fields' => array(
				array( 'key' => 'content_width', 'type' => 'number', 'label' => __( 'Maximum content width (px)', 'coregas' ), 'default' => '1500' ),
				array(
					'key'     => 'section_spacing',
					'type'    => 'select',
					'label'   => __( 'Space between sections', 'coregas' ),
					'default' => 'normal',
					'choices' => array( 'compact' => __( 'Compact', 'coregas' ), 'normal' => __( 'Normal', 'coregas' ), 'spacious' => __( 'Spacious', 'coregas' ) ),
				),
				array( 'key' => 'radius', 'type' => 'number', 'label' => __( 'Photo corner roundness (px)', 'coregas' ), 'default' => '20', 'desc' => __( '0 = square corners.', 'coregas' ) ),
				array( 'key' => 'hero_height', 'type' => 'number', 'label' => __( 'Home hero height (% of screen)', 'coregas' ), 'default' => '80' ),
			),
		),

		/* ---------------- Header ---------------- */
		array(
			'id'          => 'header',
			'tab'         => __( 'Header', 'coregas' ),
			'title'       => __( 'Header', 'coregas' ),
			'description' => sprintf( /* translators: %s: link */ __( 'Menu links are edited in %s (location "Primary").', 'coregas' ), '<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . __( 'Appearance → Menus', 'coregas' ) . '</a>' ),
			'fields'      => array(
				array( 'key' => 'header_cta_show', 'type' => 'checkbox', 'label' => __( 'Show the header button', 'coregas' ), 'default' => '1' ),
				array( 'key' => 'header_cta_label', 'type' => 'text', 'label' => __( 'Header button label', 'coregas' ), 'default' => 'Request supply' ),
				array( 'key' => 'header_cta_url', 'type' => 'text', 'label' => __( 'Header button link', 'coregas' ), 'default' => '/contact-us/?enquiry=supply' ),
				array( 'key' => 'header_cta_mobile', 'type' => 'checkbox', 'label' => __( 'Also show the header button on phones', 'coregas' ), 'default' => '0' ),
				array( 'key' => 'header_hide_on_scroll', 'type' => 'checkbox', 'label' => __( 'Hide the header while scrolling down (it returns when scrolling up)', 'coregas' ), 'default' => '1' ),
			),
		),

		/* ---------------- Footer ---------------- */
		array(
			'id'          => 'footer',
			'tab'         => __( 'Footer', 'coregas' ),
			'title'       => __( 'Footer', 'coregas' ),
			'description' => sprintf( /* translators: %s: link */ __( 'The four link columns and the legal row are menus in %s (locations "Footer column 1–4" and "Footer legal row"). Each column heading is the menu name.', 'coregas' ), '<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . __( 'Appearance → Menus', 'coregas' ) . '</a>' ),
			'fields'      => array(
				array( 'key' => 'footer_h_intro', 'type' => 'heading', 'label' => __( 'Left column', 'coregas' ) ),
				array( 'key' => 'footer_show_logo', 'type' => 'checkbox', 'label' => __( 'Show the logo', 'coregas' ), 'default' => '1' ),
				array( 'key' => 'footer_about', 'type' => 'textarea', 'label' => __( 'Short description under the logo (optional)', 'coregas' ), 'rows' => 2, 'default' => "Downstream natural gas marketing and sales, supplying the natural gas that Pakistan's industry runs on." ),
				array( 'key' => 'footer_badge', 'type' => 'text', 'label' => __( 'Licence tag (optional)', 'coregas' ), 'default' => 'Licensed by OGRA' ),
				array( 'key' => 'footer_badge_url', 'type' => 'text', 'label' => __( 'Licence tag link (optional)', 'coregas' ), 'default' => '/regulation/' ),
				array( 'key' => 'footer_btn_label', 'type' => 'text', 'label' => __( 'Button label (empty = no button)', 'coregas' ), 'default' => 'Request gas supply' ),
				array( 'key' => 'footer_btn_url', 'type' => 'text', 'label' => __( 'Button link', 'coregas' ), 'default' => '/contact-us/?enquiry=supply' ),
				array( 'key' => 'profile_show', 'type' => 'checkbox', 'label' => __( 'Show a "Download company profile" link', 'coregas' ), 'default' => '1', 'desc' => __( 'Also available at /company-profile/ for buttons anywhere on the site.', 'coregas' ) ),
				array( 'key' => 'profile_label', 'type' => 'text', 'label' => __( 'Download link label', 'coregas' ), 'default' => 'Download company profile (PDF)' ),
				array( 'key' => 'profile_pdf', 'type' => 'url', 'label' => __( 'Company profile PDF (optional)', 'coregas' ), 'desc' => __( 'Upload a PDF in Media → Add New and paste its link here. Empty = the profile bundled with the theme.', 'coregas' ) ),
				array( 'key' => 'footer_h_base', 'type' => 'heading', 'label' => __( 'Bottom row', 'coregas' ) ),
				array( 'key' => 'footer_copyright', 'type' => 'text', 'label' => __( 'Copyright line', 'coregas' ), 'default' => 'Core Gas Private Limited. Copyright © {year}.', 'desc' => __( '{year} becomes the current year.', 'coregas' ) ),
				array( 'key' => 'footer_h_more', 'type' => 'heading', 'label' => __( 'Also show', 'coregas' ) ),
				array( 'key' => 'menu_hide_empty', 'type' => 'checkbox', 'label' => __( 'Hide menu links to lists that are still empty (Industries, News)', 'coregas' ), 'default' => '1' ),
				array( 'key' => 'footer_show_social', 'type' => 'checkbox', 'label' => __( 'Show social icons', 'coregas' ), 'default' => '1' ),
				array( 'key' => 'footer_show_contact', 'type' => 'checkbox', 'label' => __( 'Show phone and email (from the Contact tab)', 'coregas' ), 'default' => '1' ),
			),
		),

		/* ---------------- Social ---------------- */
		array(
			'id'          => 'social',
			'tab'         => __( 'Social media', 'coregas' ),
			'title'       => __( 'Social media profiles', 'coregas' ),
			'description' => __( 'Only filled-in profiles are shown (footer and contact page).', 'coregas' ),
			'fields'      => array(
				array( 'key' => 'social_linkedin', 'type' => 'url', 'label' => 'LinkedIn' ),
				array( 'key' => 'social_facebook', 'type' => 'url', 'label' => 'Facebook' ),
				array( 'key' => 'social_x', 'type' => 'url', 'label' => 'X (Twitter)' ),
				array( 'key' => 'social_instagram', 'type' => 'url', 'label' => 'Instagram' ),
				array( 'key' => 'social_youtube', 'type' => 'url', 'label' => 'YouTube' ),
				array( 'key' => 'social_whatsapp', 'type' => 'url', 'label' => 'WhatsApp (https://wa.me/…)' ),
			),
		),

		/* ---------------- Contact & form ---------------- */
		array(
			'id'     => 'contact',
			'tab'    => __( 'Contact & form', 'coregas' ),
			'title'  => __( 'Contact details', 'coregas' ),
			'fields' => array(
				array( 'key' => 'contact_address', 'type' => 'textarea', 'label' => __( 'Office address', 'coregas' ), 'rows' => 3 ),
				array( 'key' => 'contact_phone', 'type' => 'text', 'label' => __( 'Phone', 'coregas' ) ),
				array( 'key' => 'contact_email', 'type' => 'email', 'label' => __( 'Public email', 'coregas' ) ),
				array( 'key' => 'contact_hours', 'type' => 'text', 'label' => __( 'Office hours (optional)', 'coregas' ) ),
				array( 'key' => 'contact_map', 'type' => 'url', 'label' => __( 'Google Maps embed URL', 'coregas' ), 'desc' => __( 'Google Maps → Share → Embed a map → copy only the src="…" address.', 'coregas' ) ),
			),
		),
		array(
			'id'     => 'form',
			'tab'    => __( 'Contact & form', 'coregas' ),
			'title'  => __( 'Enquiry form', 'coregas' ),
			'fields' => array(
				array( 'key' => 'form_recipient', 'type' => 'email', 'label' => __( 'Send enquiries to', 'coregas' ), 'desc' => __( 'Leave empty to use the site admin email. Every enquiry is also saved under Enquiries in the dashboard.', 'coregas' ) ),
				array( 'key' => 'form_heading', 'type' => 'text', 'label' => __( 'Form heading (optional)', 'coregas' ), 'default' => 'Send us your requirements' ),
				array( 'key' => 'form_name_label', 'type' => 'text', 'label' => __( '"Full name" label', 'coregas' ), 'default' => 'Full name' ),
				array( 'key' => 'form_company_label', 'type' => 'text', 'label' => __( '"Company" label', 'coregas' ), 'default' => 'Company' ),
				array( 'key' => 'form_email_label', 'type' => 'text', 'label' => __( '"Email" label', 'coregas' ), 'default' => 'Email' ),
				array( 'key' => 'form_phone_label', 'type' => 'text', 'label' => __( '"Phone" label', 'coregas' ), 'default' => 'Phone' ),
				array( 'key' => 'form_type_label', 'type' => 'text', 'label' => __( '"Enquiry type" label', 'coregas' ), 'default' => 'Enquiry type' ),
				array( 'key' => 'form_message_label', 'type' => 'text', 'label' => __( '"Requirements" label', 'coregas' ), 'default' => 'Your requirements' ),
				array( 'key' => 'form_supply_heading', 'type' => 'text', 'label' => __( 'Gas supply application: heading', 'coregas' ), 'default' => 'About your gas requirement', 'desc' => __( 'These extra fields appear when the enquiry type mentions supply or connection.', 'coregas' ) ),
				array( 'key' => 'form_site_label', 'type' => 'text', 'label' => __( 'Gas supply: site location label', 'coregas' ), 'default' => 'Site location (city / industrial area)' ),
				array( 'key' => 'form_sector_label', 'type' => 'text', 'label' => __( 'Gas supply: industry label', 'coregas' ), 'default' => 'Industry / sector' ),
				array( 'key' => 'form_volume_label', 'type' => 'text', 'label' => __( 'Gas supply: volume label', 'coregas' ), 'default' => 'Gas required (e.g. MMCFD or MMBTU per day)' ),
				array( 'key' => 'form_pipe_label', 'type' => 'text', 'label' => __( 'Gas supply: connection label', 'coregas' ), 'default' => 'Existing gas connection / pipeline size' ),
				array( 'key' => 'form_pipe_options', 'type' => 'text', 'label' => __( 'Gas supply: connection options (comma separated)', 'coregas' ), 'default' => 'Not connected yet, 2 inch, 4 inch, 6 inch, 8 inch, 10 inch or larger, Not sure' ),
				array( 'key' => 'form_term_label', 'type' => 'text', 'label' => __( 'Gas supply: supply period label', 'coregas' ), 'default' => 'Supply period needed' ),
				array( 'key' => 'form_term_options', 'type' => 'text', 'label' => __( 'Gas supply: supply period options (comma separated)', 'coregas' ), 'default' => '1 year, 2 years, 3 years, 5 years or more, Not sure' ),
				array(
					'key'       => 'form_types',
					'type'      => 'repeater',
					'label'     => __( 'Enquiry types (dropdown options)', 'coregas' ),
					'row_label' => __( 'Option', 'coregas' ),
					'sub'       => array( array( 'key' => 'label', 'type' => 'text', 'label' => __( 'Option', 'coregas' ) ) ),
					'default'   => array( array( 'label' => 'New gas supply / connection' ), array( 'label' => 'Existing customer support' ), array( 'label' => 'Gas producer or supplier' ), array( 'label' => 'Partnership' ), array( 'label' => 'General enquiry' ) ),
				),
				array( 'key' => 'form_button', 'type' => 'text', 'label' => __( 'Send button label', 'coregas' ), 'default' => 'Send enquiry' ),
				array( 'key' => 'form_success', 'type' => 'text', 'label' => __( 'Message after sending', 'coregas' ), 'default' => 'Thank you. Our commercial team will get back to you shortly.' ),
				array( 'key' => 'form_error', 'type' => 'text', 'label' => __( 'Message if sending fails', 'coregas' ), 'default' => 'Sorry, the message could not be sent. Please check the required fields and try again.' ),
				array( 'key' => 'form_subject', 'type' => 'text', 'label' => __( 'Email subject prefix', 'coregas' ), 'default' => '[Core Gas website]' ),
			),
		),

		/* ---------------- Email sending ---------------- */
		array(
			'id'          => 'email',
			'tab'         => __( 'Contact & form', 'coregas' ),
			'title'       => __( 'Email sending (SMTP)', 'coregas' ),
			'description' => __( 'Hosting servers often fail to deliver website email. Send it through a real mailbox instead: in Bluehost, create an email account (e.g. website@core-gas.com) and enter its details here. Then use "Send a test email" on the right. Every enquiry is also saved under Enquiries, so nothing is lost.', 'coregas' ),
			'fields'      => array(
				array( 'key' => 'smtp_on', 'type' => 'checkbox', 'label' => __( 'Send email through the mail server below', 'coregas' ), 'default' => '0' ),
				array( 'key' => 'smtp_host', 'type' => 'text', 'label' => __( 'Mail server (SMTP host)', 'coregas' ), 'placeholder' => 'mail.core-gas.com' ),
				array( 'key' => 'smtp_port', 'type' => 'number', 'label' => __( 'Port', 'coregas' ), 'default' => '465', 'desc' => __( 'Bluehost: 465 with SSL.', 'coregas' ) ),
				array(
					'key'     => 'smtp_secure',
					'type'    => 'select',
					'label'   => __( 'Encryption', 'coregas' ),
					'default' => 'ssl',
					'choices' => array( 'ssl' => 'SSL', 'tls' => 'TLS (STARTTLS)', 'none' => __( 'None', 'coregas' ) ),
				),
				array( 'key' => 'smtp_user', 'type' => 'text', 'label' => __( 'Username (usually the full email address)', 'coregas' ) ),
				array( 'key' => 'smtp_pass', 'type' => 'password', 'label' => __( 'Password', 'coregas' ) ),
				array( 'key' => 'smtp_from_email', 'type' => 'email', 'label' => __( 'Send from address', 'coregas' ), 'desc' => __( 'Normally the same mailbox as the username.', 'coregas' ) ),
				array( 'key' => 'smtp_from_name', 'type' => 'text', 'label' => __( 'Send from name', 'coregas' ), 'default' => 'Core Gas website' ),
			),
		),

		/* ---------------- SEO ---------------- */
		array(
			'id'          => 'seo',
			'tab'         => __( 'SEO & sharing', 'coregas' ),
			'title'       => __( 'Search engines & social sharing', 'coregas' ),
			'description' => __( 'Defaults for every page. Each page, post and item can override them in its own "SEO" box. If an SEO plugin (Yoast, Rank Math, SEOPress, AIOSEO) is active, the theme leaves SEO to it.', 'coregas' ),
			'fields'      => array(
				array( 'key' => 'seo_description', 'type' => 'textarea', 'label' => __( 'Default description', 'coregas' ), 'rows' => 2, 'default' => 'Core Gas Private Limited is an OGRA-licensed downstream company marketing and selling natural gas to industrial consumers in Pakistan.' ),
				array( 'key' => 'seo_image', 'type' => 'image', 'label' => __( 'Default social sharing image', 'coregas' ), 'desc' => __( 'Shown when a link is shared on WhatsApp, LinkedIn, Facebook… Ideal size 1200 × 630 px.', 'coregas' ) ),
				array( 'key' => 'seo_separator', 'type' => 'text', 'label' => __( 'Title separator', 'coregas' ), 'default' => '|' ),
				array( 'key' => 'seo_verify_google', 'type' => 'text', 'label' => __( 'Google Search Console verification code', 'coregas' ), 'desc' => __( 'Only the content="…" value.', 'coregas' ) ),
				array( 'key' => 'seo_verify_bing', 'type' => 'text', 'label' => __( 'Bing verification code', 'coregas' ) ),
				array( 'key' => 'seo_live_domain', 'type' => 'text', 'label' => __( 'Live website domain', 'coregas' ), 'default' => 'core-gas.com', 'desc' => __( 'Only this domain is shown to search engines. Copies on any other address (staging, test) are hidden.', 'coregas' ) ),
				array( 'key' => 'seo_hide_staging', 'type' => 'checkbox', 'label' => __( 'Hide copies on other addresses (staging) from search engines', 'coregas' ), 'default' => '1' ),
			),
		),

		/* ---------------- Pages & labels ---------------- */
		array(
			'id'          => 'archives',
			'tab'         => __( 'Pages & wording', 'coregas' ),
			'title'       => __( 'Banners of list pages', 'coregas' ),
			'description' => __( 'Normal pages have their banner on their own edit screen. These are the automatic list pages.', 'coregas' ),
			'fields'      => $archive_fields,
		),
		array(
			'id'     => 'notfound',
			'tab'    => __( 'Pages & wording', 'coregas' ),
			'title'  => __( '"Page not found" (404) page', 'coregas' ),
			'fields' => array(
				array( 'key' => 'nf_eyebrow', 'type' => 'text', 'label' => __( 'Small label', 'coregas' ), 'default' => 'Page not found' ),
				array( 'key' => 'nf_title', 'type' => 'text', 'label' => __( 'Title', 'coregas' ), 'default' => 'This line has no pressure.' ),
				array( 'key' => 'nf_text', 'type' => 'textarea', 'label' => __( 'Text', 'coregas' ), 'rows' => 2, 'default' => 'The page you were looking for has moved or no longer exists.' ),
				array( 'key' => 'nf_button', 'type' => 'text', 'label' => __( 'Button label', 'coregas' ), 'default' => 'Back to home' ),
			),
		),
		array(
			'id'          => 'labels',
			'tab'         => __( 'Pages & wording', 'coregas' ),
			'title'       => __( 'Website wording', 'coregas' ),
			'description' => __( 'Small pieces of text used across the website.', 'coregas' ),
			'fields'      => $label_fields,
		),

		/* ---------------- Analytics ---------------- */
		array(
			'id'     => 'analytics',
			'tab'    => __( 'Analytics & code', 'coregas' ),
			'title'  => __( 'Analytics & tracking', 'coregas' ),
			'fields' => array(
				array( 'key' => 'ga4_id', 'type' => 'text', 'label' => __( 'Google Analytics 4 measurement ID', 'coregas' ), 'placeholder' => 'G-XXXXXXXXXX' ),
				array( 'key' => 'gtm_id', 'type' => 'text', 'label' => __( 'Google Tag Manager container ID', 'coregas' ), 'placeholder' => 'GTM-XXXXXXX' ),
				array( 'key' => 'meta_pixel', 'type' => 'text', 'label' => __( 'Meta (Facebook) Pixel ID', 'coregas' ), 'placeholder' => '123456789012345' ),
				array( 'key' => 'head_code', 'type' => 'code', 'label' => __( 'Extra code in <head>', 'coregas' ), 'desc' => __( 'For other trackers. Saved as-is for administrators only.', 'coregas' ) ),
				array( 'key' => 'footer_code', 'type' => 'code', 'label' => __( 'Extra code before </body>', 'coregas' ) ),
				array( 'key' => 'custom_css', 'type' => 'code', 'label' => __( 'Custom CSS', 'coregas' ), 'desc' => __( 'Small style tweaks without editing theme files.', 'coregas' ) ),
			),
		),

		/* ---------------- Security ---------------- */
		array(
			'id'          => 'security',
			'tab'         => __( 'Security', 'coregas' ),
			'title'       => __( 'Security', 'coregas' ),
			'description' => __( 'Safe defaults for a company website. Server items (SSL certificate, file permissions, backups) are handled in the Bluehost control panel.', 'coregas' ),
			'fields'      => array(
				array( 'key' => 'sec_xmlrpc_off', 'type' => 'checkbox', 'label' => __( 'Turn off XML-RPC (blocks a common route for password-guessing attacks)', 'coregas' ), 'default' => '1', 'desc' => __( 'Only switch this back on if you start using Jetpack or the WordPress mobile app.', 'coregas' ) ),
				array( 'key' => 'sec_hide_version', 'type' => 'checkbox', 'label' => __( 'Hide the WordPress version number from the page source', 'coregas' ), 'default' => '1' ),
				array( 'key' => 'sec_headers', 'type' => 'checkbox', 'label' => __( 'Send browser security headers (no framing by other sites, no content sniffing, private referrers)', 'coregas' ), 'default' => '1' ),
				array( 'key' => 'sec_hide_errors', 'type' => 'checkbox', 'label' => __( 'Never show PHP error messages to visitors', 'coregas' ), 'default' => '1', 'desc' => __( 'Errors are still written to the server error log. For full effect also set WP_DEBUG_DISPLAY to false in wp-config.php.', 'coregas' ) ),
				array( 'key' => 'sec_lock_editor', 'type' => 'checkbox', 'label' => __( 'Lock the built-in theme and plugin code editor', 'coregas' ), 'default' => '1', 'desc' => __( 'Prevents code changes from the dashboard. Theme updates still arrive through GitHub / WP Pusher.', 'coregas' ) ),
			),
		),

		/* ---------------- Motion ---------------- */
		array(
			'id'     => 'advanced',
			'tab'    => __( 'Motion', 'coregas' ),
			'title'  => __( 'Motion & effects', 'coregas' ),
			'fields' => array(
				array( 'key' => 'hero_intro', 'type' => 'checkbox', 'label' => __( 'Animate the hero headline when the page loads', 'coregas' ), 'default' => '1' ),
				array( 'key' => 'pulse_dots', 'type' => 'checkbox', 'label' => __( 'Pulsing dot next to small labels', 'coregas' ), 'default' => '1' ),
				array( 'key' => 'smooth_scroll', 'type' => 'checkbox', 'label' => __( 'JavaScript smooth scrolling (can stutter on large / 4K screens)', 'coregas' ), 'default' => '0', 'desc' => __( 'Visitors can still try it with ?smooth=1 in the address.', 'coregas' ) ),
			),
		),
	);
}
