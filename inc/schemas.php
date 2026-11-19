<?php
/**
 * Field schemas for every post type.
 *
 * Each post type maps to a list of groups (one meta box each). A group may
 * declare `show_if` => [ field_key => [values] ] to only appear for certain
 * Page Section types.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shared choices.
 */
function coregas_bg_choices() {
	return array(
		'light'  => __( 'Light (off-white)', 'coregas' ),
		'white'  => __( 'White', 'coregas' ),
		'dark'   => __( 'Dark (near-black)', 'coregas' ),
		'maroon' => __( 'Brand maroon', 'coregas' ),
	);
}

/**
 * All schemas, keyed by post type.
 *
 * @return array
 */
function coregas_schemas() {
	static $schemas = null;
	if ( null !== $schemas ) {
		return $schemas;
	}

	$link_fields = array(
		array( 'key' => 'label', 'type' => 'text', 'label' => __( 'Label', 'coregas' ) ),
		array( 'key' => 'url', 'type' => 'text', 'label' => __( 'Link (URL or #anchor)', 'coregas' ) ),
	);

	$schemas = array(

		/* ================= Theme Settings ================= */
		'cg_settings' => coregas_settings_schema(),

		/* ================= Page Sections ================= */
		'cg_section' => array(
			array(
				'id'          => 'placement',
				'title'       => __( 'Section type & placement', 'coregas' ),
				'description' => __( 'Sections appear on the chosen page in the order set by "Order" (Page Attributes box).', 'coregas' ),
				'fields'      => array(
					array(
						'key'     => 'section_type',
						'type'    => 'select',
						'label'   => __( 'Section type', 'coregas' ),
						'default' => 'text',
						'choices' => array(
							'hero'  => __( 'Hero (photo slideshow, headline, licence readout)', 'coregas' ),
							'about' => __( 'About (statement, image, pillars, group block)', 'coregas' ),
							'text'  => __( 'Rich text (uses the main editor)', 'coregas' ),
							'cards' => __( 'Feature rows (items from a post type, image and text alternating)', 'coregas' ),
							'cta'   => __( 'Call to action', 'coregas' ),
							'split' => __( 'Image and text (photo on one side, copy on the other)', 'coregas' ),
							'steps' => __( 'Steps strip (your own steps, with photos)', 'coregas' ),
							'columns' => __( 'Columns (2-4 text columns, optional photos)', 'coregas' ),
							'gallery' => __( 'Photo gallery', 'coregas' ),
							'faq'     => __( 'Questions & answers', 'coregas' ),
							'contact' => __( 'Contact details + enquiry form', 'coregas' ),
							'embed'   => __( 'Video or map', 'coregas' ),
						),
					),
					array( 'key' => 'page', 'type' => 'page', 'label' => __( 'Show on page', 'coregas' ), 'none' => __( '— Not shown —', 'coregas' ) ),
					array( 'key' => 'anchor', 'type' => 'text', 'label' => __( 'Anchor ID (optional)', 'coregas' ), 'desc' => __( 'Lets menus link to #anchor.', 'coregas' ) ),
					array(
						'key'     => 'sec_spacing',
						'type'    => 'select',
						'label'   => __( 'Space above and below', 'coregas' ),
						'default' => 'default',
						'choices' => array( 'default' => __( 'Site default', 'coregas' ), 'compact' => __( 'Compact', 'coregas' ), 'normal' => __( 'Normal', 'coregas' ), 'spacious' => __( 'Spacious', 'coregas' ), 'none' => __( 'None', 'coregas' ) ),
					),
					array(
						'key'     => 'sec_visibility',
						'type'    => 'select',
						'label'   => __( 'Show on', 'coregas' ),
						'default' => 'all',
						'choices' => array( 'all' => __( 'All screens', 'coregas' ), 'desktop' => __( 'Computers and tablets only', 'coregas' ), 'mobile' => __( 'Phones only', 'coregas' ) ),
					),
					array( 'key' => 'sec_class', 'type' => 'text', 'label' => __( 'Extra CSS class (advanced, optional)', 'coregas' ) ),
				),
			),
			array(
				'id'      => 'hero',
				'title'   => __( 'Hero', 'coregas' ),
				'show_if' => array( 'section_type' => array( 'hero' ) ),
				'fields'  => array(
					array( 'key' => 'hero_eyebrow_left', 'type' => 'text', 'label' => __( 'Top line, left', 'coregas' ), 'default' => 'Downstream natural gas' ),
					array( 'key' => 'hero_eyebrow_right', 'type' => 'text', 'label' => __( 'Top line, right', 'coregas' ), 'default' => 'Pakistan · Industrial supply' ),
					array( 'key' => 'hero_line1', 'type' => 'text', 'label' => __( 'Headline line 1', 'coregas' ), 'default' => 'Powering' ),
					array( 'key' => 'hero_line2', 'type' => 'text', 'label' => __( 'Headline line 2', 'coregas' ), 'default' => "Pakistan's" ),
					array( 'key' => 'hero_accent', 'type' => 'text', 'label' => __( 'Highlighted word (accent colour)', 'coregas' ), 'default' => 'industry.' ),
					array( 'key' => 'hero_subline', 'type' => 'text', 'label' => __( 'Sub-headline', 'coregas' ), 'default' => 'Dependable natural gas, delivered.' ),
					array( 'key' => 'hero_lead', 'type' => 'textarea', 'label' => __( 'Intro paragraph', 'coregas' ), 'rows' => 3, 'default' => "Core Gas sources, transports and delivers natural gas to Pakistan's industrial consumers, licensed by OGRA for gas marketing and sales." ),
					array( 'key' => 'hero_btn_label', 'type' => 'text', 'label' => __( 'Button label', 'coregas' ), 'default' => 'About Core Gas' ),
					array( 'key' => 'hero_btn_url', 'type' => 'text', 'label' => __( 'Button link', 'coregas' ), 'default' => '#about' ),
					array(
						'key'       => 'hero_readout',
						'type'      => 'repeater',
						'label'     => __( 'Key facts strip', 'coregas' ),
						'row_label' => __( 'Fact', 'coregas' ),
						'sub'       => array(
							array( 'key' => 'label', 'type' => 'text', 'label' => __( 'Label', 'coregas' ) ),
							array( 'key' => 'value', 'type' => 'text', 'label' => __( 'Value', 'coregas' ) ),
						),
						'default'   => array(
							array( 'label' => 'Licensed by', 'value' => 'OGRA' ),
							array( 'label' => 'Activity', 'value' => 'Gas marketing & sales' ),
							array( 'label' => 'Customers', 'value' => 'Industrial, Pakistan' ),
						),
					),
					array( 'key' => 'hero_image', 'type' => 'image', 'label' => __( 'Background photo (first slide)', 'coregas' ), 'desc' => __( 'Leave this and the photos below empty to show the placeholder photos.', 'coregas' ) ),
					array(
						'key'       => 'hero_slides',
						'type'      => 'repeater',
						'label'     => __( 'More photos (they fade from one to the next)', 'coregas' ),
						'row_label' => __( 'Photo', 'coregas' ),
						'sub'       => array( array( 'key' => 'image', 'type' => 'image', 'label' => __( 'Photo', 'coregas' ) ) ),
					),
					array( 'key' => 'hero_darkness', 'type' => 'number', 'label' => __( 'Photo brightness (%)', 'coregas' ), 'default' => '55', 'desc' => __( 'Lower = darker photos and stronger text contrast.', 'coregas' ) ),
				),
			),
			array(
				'id'          => 'hero_design',
				'title'       => __( 'Hero: design', 'coregas' ),
				'show_if'     => array( 'section_type' => array( 'hero' ) ),
				'description' => __( 'Styling of this hero only. Leave a colour empty to use the colour from Theme Settings.', 'coregas' ),
				'fields'      => array(
					array( 'key' => 'hd_h_photo', 'type' => 'heading', 'label' => __( 'Photo', 'coregas' ) ),
					array( 'key' => 'hd_photo_mobile', 'type' => 'image', 'label' => __( 'Different photo on phones (optional)', 'coregas' ) ),
					array(
						'key'     => 'hd_photo_focus',
						'type'    => 'select',
						'label'   => __( 'Photo focus point', 'coregas' ),
						'default' => 'default',
						'desc'    => __( 'Which part of the photo stays visible when the screen crops it.', 'coregas' ),
						'choices' => array(
							'default' => __( 'Default (left of centre, lower half)', 'coregas' ),
							'center'  => __( 'Centre', 'coregas' ),
							'top'     => __( 'Top', 'coregas' ),
							'bottom'  => __( 'Bottom', 'coregas' ),
							'left'    => __( 'Left', 'coregas' ),
							'right'   => __( 'Right', 'coregas' ),
						),
					),
					array( 'key' => 'hd_bw', 'type' => 'checkbox', 'label' => __( 'Show the photos in black and white', 'coregas' ), 'default' => '0' ),
					array( 'key' => 'hd_overlay', 'type' => 'color', 'label' => __( 'Overlay colour (darkens the photo)', 'coregas' ), 'default' => '#000000', 'desc' => __( 'How strong it is: "Photo brightness" in the Hero box above.', 'coregas' ) ),

					array( 'key' => 'hd_h_slides', 'type' => 'heading', 'label' => __( 'Slideshow', 'coregas' ) ),
					array( 'key' => 'hd_slide_interval', 'type' => 'number', 'label' => __( 'Seconds each photo stays', 'coregas' ), 'default' => '7', 'desc' => __( 'Between 3 and 20. Only used when there is more than one photo.', 'coregas' ) ),

					array( 'key' => 'hd_h_text', 'type' => 'heading', 'label' => __( 'Text', 'coregas' ) ),
					array(
						'key'     => 'hd_align',
						'type'    => 'select',
						'label'   => __( 'Text alignment', 'coregas' ),
						'default' => 'left',
						'choices' => array( 'left' => __( 'Left', 'coregas' ), 'center' => __( 'Centred', 'coregas' ) ),
					),
					array(
						'key'     => 'hd_valign',
						'type'    => 'select',
						'label'   => __( 'Headline position', 'coregas' ),
						'default' => 'center',
						'choices' => array( 'center' => __( 'Middle', 'coregas' ), 'top' => __( 'Near the top', 'coregas' ), 'bottom' => __( 'Near the bottom', 'coregas' ) ),
					),
					array( 'key' => 'hd_title_size', 'type' => 'number', 'label' => __( 'Headline size (%)', 'coregas' ), 'default' => '100', 'desc' => __( '100 = default. Between 50 and 150.', 'coregas' ) ),
					array( 'key' => 'hd_title_color', 'type' => 'color', 'label' => __( 'Headline colour', 'coregas' ), 'default' => '' ),
					array( 'key' => 'hd_accent_color', 'type' => 'color', 'label' => __( 'Highlighted word colour', 'coregas' ), 'default' => '' ),
					array( 'key' => 'hd_sub_color', 'type' => 'color', 'label' => __( 'Sub-headline colour', 'coregas' ), 'default' => '' ),
					array( 'key' => 'hd_lead_color', 'type' => 'color', 'label' => __( 'Intro paragraph colour', 'coregas' ), 'default' => '' ),
					array( 'key' => 'hd_meta_color', 'type' => 'color', 'label' => __( 'Top line and facts label colour', 'coregas' ), 'default' => '' ),
					array( 'key' => 'hd_show_meta', 'type' => 'checkbox', 'label' => __( 'Show the top line', 'coregas' ), 'default' => '1' ),
					array( 'key' => 'hd_show_readout', 'type' => 'checkbox', 'label' => __( 'Show the key facts strip', 'coregas' ), 'default' => '1' ),
					array( 'key' => 'hd_intro', 'type' => 'checkbox', 'label' => __( 'Animate the headline when the page loads', 'coregas' ), 'default' => '1' ),

					array( 'key' => 'hd_h_layout', 'type' => 'heading', 'label' => __( 'Size', 'coregas' ) ),
					array( 'key' => 'hd_height', 'type' => 'number', 'label' => __( 'Height (% of the screen)', 'coregas' ), 'default' => '0', 'desc' => __( '0 = use the height from Theme Settings → Typography & layout.', 'coregas' ) ),

					array( 'key' => 'hd_h_buttons', 'type' => 'heading', 'label' => __( 'Buttons', 'coregas' ) ),
					array(
						'key'     => 'hd_btn_style',
						'type'    => 'select',
						'label'   => __( 'Main button style', 'coregas' ),
						'default' => 'light',
						'choices' => array( 'light' => __( 'White', 'coregas' ), 'flame' => __( 'Accent colour', 'coregas' ), 'maroon' => __( 'Maroon', 'coregas' ), 'outline' => __( 'Outline', 'coregas' ) ),
					),
					array(
						'key'     => 'hd_btn_icon',
						'type'    => 'select',
						'label'   => __( 'Main button icon', 'coregas' ),
						'default' => 'down',
						'choices' => array( 'down' => __( 'Arrow down', 'coregas' ), 'out' => __( 'Arrow out', 'coregas' ), 'none' => __( 'No icon', 'coregas' ) ),
					),
					array( 'key' => 'hd_btn2_label', 'type' => 'text', 'label' => __( 'Second button label (optional)', 'coregas' ) ),
					array( 'key' => 'hd_btn2_url', 'type' => 'text', 'label' => __( 'Second button link', 'coregas' ) ),
					array(
						'key'     => 'hd_btn2_style',
						'type'    => 'select',
						'label'   => __( 'Second button style', 'coregas' ),
						'default' => 'outline',
						'choices' => array( 'outline' => __( 'Outline', 'coregas' ), 'light' => __( 'White', 'coregas' ), 'flame' => __( 'Accent colour', 'coregas' ), 'maroon' => __( 'Maroon', 'coregas' ) ),
					),
				),
			),
			array(
				'id'      => 'about',
				'title'   => __( 'About', 'coregas' ),
				'show_if' => array( 'section_type' => array( 'about' ) ),
				'fields'  => array(
					array( 'key' => 'about_eyebrow', 'type' => 'text', 'label' => __( 'Small label', 'coregas' ), 'default' => 'About Core Gas' ),
					array( 'key' => 'about_heading', 'type' => 'text', 'label' => __( 'Heading', 'coregas' ), 'default' => "A dependable natural gas supply platform for Pakistan's industry" ),
					array( 'key' => 'about_intro', 'type' => 'textarea', 'label' => __( 'Intro (blank line = new paragraph)', 'coregas' ), 'rows' => 6, 'default' => "Core Gas Private Limited is a downstream gas company established to meet the natural gas requirements of industrial consumers in Pakistan.\n\nLicensed by the Oil & Gas Regulatory Authority, we source and market natural gas for sale to customers, connecting natural gas supply with industrial demand." ),
					array( 'key' => 'about_image', 'type' => 'image', 'label' => __( 'Image', 'coregas' ), 'desc' => __( 'Leave empty for the placeholder photo.', 'coregas' ) ),
					array( 'key' => 'about_caption', 'type' => 'text', 'label' => __( 'Image caption', 'coregas' ), 'default' => 'Current focus: natural gas supply to industrial consumers in Pakistan' ),
					array(
						'key'       => 'about_pillars',
						'type'      => 'repeater',
						'label'     => __( 'Pillars', 'coregas' ),
						'row_label' => __( 'Pillar', 'coregas' ),
						'sub'       => array(
							array( 'key' => 'tag', 'type' => 'text', 'label' => __( 'Small label', 'coregas' ) ),
							array( 'key' => 'title', 'type' => 'text', 'label' => __( 'Title', 'coregas' ) ),
							array( 'key' => 'text', 'type' => 'textarea', 'label' => __( 'Text', 'coregas' ) ),
							array( 'key' => 'chips', 'type' => 'text', 'label' => __( 'Flow chips (comma separated, optional)', 'coregas' ) ),
						),
						'default'   => array(
							array( 'tag' => 'Licensed activity', 'title' => 'Gas marketing & sales', 'text' => 'Sourcing and marketing natural gas for sale to customers under an OGRA licence, within the applicable regulatory framework.', 'chips' => '' ),
							array( 'tag' => 'Customer focus', 'title' => 'Industrial consumers', 'text' => 'Serving industrial users whose operations depend on a reliable and continuous supply of natural gas.', 'chips' => '' ),
							array( 'tag' => 'Gas supply chain', 'title' => 'Source to service', 'text' => 'Gas sourcing, transportation, delivery and customer service, handled as one dependable supply relationship.', 'chips' => 'Source, Transport, Deliver, Serve' ),
						),
					),
				),
			),
			array(
				'id'      => 'common',
				'title'   => __( 'Heading & background', 'coregas' ),
				'show_if' => array( 'section_type' => array( 'text', 'cards', 'cta', 'split', 'steps', 'columns', 'gallery', 'faq', 'contact', 'embed' ) ),
				'fields'  => array(
					array( 'key' => 'sec_eyebrow', 'type' => 'text', 'label' => __( 'Small label', 'coregas' ) ),
					array( 'key' => 'sec_heading', 'type' => 'text', 'label' => __( 'Heading', 'coregas' ) ),
					array( 'key' => 'sec_intro', 'type' => 'textarea', 'label' => __( 'Intro text', 'coregas' ), 'rows' => 3 ),
					array( 'key' => 'sec_bg', 'type' => 'select', 'label' => __( 'Background', 'coregas' ), 'choices' => coregas_bg_choices(), 'default' => 'light' ),
				),
			),
			array(
				'id'      => 'cards',
				'title'   => __( 'Feature rows', 'coregas' ),
				'show_if' => array( 'section_type' => array( 'cards' ) ),
				'fields'  => array(
					array(
						'key'     => 'cards_source',
						'type'    => 'select',
						'label'   => __( 'List items from', 'coregas' ),
						'default' => 'cg_service',
						'choices' => array(
							'cg_service'  => __( 'Supply Services', 'coregas' ),
							'cg_industry' => __( 'Industries', 'coregas' ),
							'cg_licence'  => __( 'Licences', 'coregas' ),
							'post'        => __( 'News posts', 'coregas' ),
						),
					),
					array( 'key' => 'cards_count', 'type' => 'number', 'label' => __( 'How many (0 = all)', 'coregas' ), 'default' => '0' ),
					array(
						'key'     => 'cards_layout',
						'type'    => 'select',
						'label'   => __( 'Layout', 'coregas' ),
						'default' => 'rows',
						'choices' => array(
							'rows'   => __( 'Alternating rows (image and text)', 'coregas' ),
							'strip'  => __( 'Steps strip (compact, side by side)', 'coregas' ),
							'record' => __( 'Licence record panel (for Licences)', 'coregas' ),
						),
					),
				),
			),
			array(
				'id'      => 'split',
				'title'   => __( 'Image and text', 'coregas' ),
				'show_if' => array( 'section_type' => array( 'split' ) ),
				'description' => __( 'The copy is the main editor content above, under the heading set in "Heading & background".', 'coregas' ),
				'fields'  => array(
					array( 'key' => 'split_image', 'type' => 'image', 'label' => __( 'Photo', 'coregas' ), 'desc' => __( 'Leave empty for a placeholder photo.', 'coregas' ) ),
					array(
						'key'     => 'split_side',
						'type'    => 'select',
						'label'   => __( 'Photo side', 'coregas' ),
						'default' => 'right',
						'choices' => array( 'right' => __( 'Right', 'coregas' ), 'left' => __( 'Left', 'coregas' ) ),
					),
					array( 'key' => 'split_caption', 'type' => 'text', 'label' => __( 'Caption on the photo (optional)', 'coregas' ) ),
				),
			),
			array(
				'id'      => 'steps',
				'title'   => __( 'Steps', 'coregas' ),
				'show_if' => array( 'section_type' => array( 'steps' ) ),
				'fields'  => array(
					array(
						'key'       => 'steps',
						'type'      => 'repeater',
						'label'     => __( 'Steps (shown left to right)', 'coregas' ),
						'row_label' => __( 'Step', 'coregas' ),
						'sub'       => array(
							array( 'key' => 'tag', 'type' => 'text', 'label' => __( 'Small label', 'coregas' ) ),
							array( 'key' => 'title', 'type' => 'text', 'label' => __( 'Title', 'coregas' ) ),
							array( 'key' => 'text', 'type' => 'textarea', 'label' => __( 'Text', 'coregas' ) ),
							array( 'key' => 'image', 'type' => 'image', 'label' => __( 'Photo (optional)', 'coregas' ) ),
						),
					),
					array( 'key' => 'steps_photos', 'type' => 'checkbox', 'label' => __( 'Show a photo above each step', 'coregas' ), 'default' => '1', 'desc' => __( 'Untick for a compact text-only row.', 'coregas' ) ),
				),
			),
			array(
				'id'      => 'columns',
				'title'   => __( 'Columns', 'coregas' ),
				'show_if' => array( 'section_type' => array( 'columns' ) ),
				'fields'  => array(
					array(
						'key'     => 'cols_count',
						'type'    => 'select',
						'label'   => __( 'Columns per row', 'coregas' ),
						'default' => '3',
						'choices' => array( '2' => '2', '3' => '3', '4' => '4' ),
					),
					array(
						'key'       => 'cols_items',
						'type'      => 'repeater',
						'label'     => __( 'Columns', 'coregas' ),
						'row_label' => __( 'Column', 'coregas' ),
						'sub'       => array(
							array( 'key' => 'image', 'type' => 'image', 'label' => __( 'Photo (optional)', 'coregas' ) ),
							array( 'key' => 'tag', 'type' => 'text', 'label' => __( 'Small label', 'coregas' ) ),
							array( 'key' => 'title', 'type' => 'text', 'label' => __( 'Title', 'coregas' ) ),
							array( 'key' => 'text', 'type' => 'textarea', 'label' => __( 'Text', 'coregas' ) ),
							array( 'key' => 'link_label', 'type' => 'text', 'label' => __( 'Link label (optional)', 'coregas' ) ),
							array( 'key' => 'link_url', 'type' => 'text', 'label' => __( 'Link', 'coregas' ) ),
						),
					),
				),
			),
			array(
				'id'      => 'gallery',
				'title'   => __( 'Gallery', 'coregas' ),
				'show_if' => array( 'section_type' => array( 'gallery' ) ),
				'fields'  => array(
					array(
						'key'     => 'gal_columns',
						'type'    => 'select',
						'label'   => __( 'Photos per row', 'coregas' ),
						'default' => '3',
						'choices' => array( '2' => '2', '3' => '3', '4' => '4' ),
					),
					array(
						'key'       => 'gal_images',
						'type'      => 'repeater',
						'label'     => __( 'Photos', 'coregas' ),
						'row_label' => __( 'Photo', 'coregas' ),
						'sub'       => array(
							array( 'key' => 'image', 'type' => 'image', 'label' => __( 'Photo', 'coregas' ) ),
							array( 'key' => 'caption', 'type' => 'text', 'label' => __( 'Caption (optional)', 'coregas' ) ),
						),
					),
				),
			),
			array(
				'id'      => 'faq',
				'title'   => __( 'Questions & answers', 'coregas' ),
				'show_if' => array( 'section_type' => array( 'faq' ) ),
				'fields'  => array(
					array(
						'key'       => 'faq_items',
						'type'      => 'repeater',
						'label'     => __( 'Questions', 'coregas' ),
						'row_label' => __( 'Question', 'coregas' ),
						'sub'       => array(
							array( 'key' => 'q', 'type' => 'text', 'label' => __( 'Question', 'coregas' ) ),
							array( 'key' => 'a', 'type' => 'textarea', 'label' => __( 'Answer', 'coregas' ) ),
						),
					),
				),
			),
			array(
				'id'          => 'contact_sec',
				'title'       => __( 'Contact block', 'coregas' ),
				'show_if'     => array( 'section_type' => array( 'contact' ) ),
				'description' => __( 'Address, phone, email, form labels and recipient are set in Theme Settings, tab "Contact & form".', 'coregas' ),
				'fields'      => array(
					array( 'key' => 'ct_show_details', 'type' => 'checkbox', 'label' => __( 'Show contact details', 'coregas' ), 'default' => '1' ),
					array( 'key' => 'ct_show_form', 'type' => 'checkbox', 'label' => __( 'Show the enquiry form', 'coregas' ), 'default' => '1' ),
					array( 'key' => 'ct_show_map', 'type' => 'checkbox', 'label' => __( 'Show the map (if a map is set)', 'coregas' ), 'default' => '0' ),
				),
			),
			array(
				'id'      => 'embed',
				'title'   => __( 'Video or map', 'coregas' ),
				'show_if' => array( 'section_type' => array( 'embed' ) ),
				'fields'  => array(
					array( 'key' => 'emb_url', 'type' => 'url', 'label' => __( 'Link', 'coregas' ), 'desc' => __( 'A YouTube or Vimeo link, or a Google Maps embed address.', 'coregas' ) ),
					array(
						'key'     => 'emb_ratio',
						'type'    => 'select',
						'label'   => __( 'Shape', 'coregas' ),
						'default' => '16/9',
						'choices' => array( '16/9' => __( 'Wide (16:9)', 'coregas' ), '4/3' => __( 'Standard (4:3)', 'coregas' ), '21/9' => __( 'Cinema (21:9)', 'coregas' ) ),
					),
					array( 'key' => 'emb_caption', 'type' => 'text', 'label' => __( 'Caption (optional)', 'coregas' ) ),
				),
			),
			array(
				'id'      => 'cta',
				'title'   => __( 'Call to action', 'coregas' ),
				'show_if' => array( 'section_type' => array( 'cta' ) ),
				'fields'  => array(
					array( 'key' => 'cta_btn_label', 'type' => 'text', 'label' => __( 'Button label', 'coregas' ), 'default' => 'Request gas supply' ),
					array( 'key' => 'cta_btn_url', 'type' => 'text', 'label' => __( 'Button link', 'coregas' ), 'default' => '/contact-us/' ),
				),
			),
			array(
				'id'      => 'buttons',
				'title'   => __( 'Buttons', 'coregas' ),
				'show_if' => array( 'section_type' => array( 'text', 'cards', 'split', 'columns', 'gallery', 'faq', 'embed' ) ),
				'fields'  => array(
					array(
						'key'       => 'sec_links',
						'type'      => 'repeater',
						'label'     => __( 'Buttons under the section', 'coregas' ),
						'row_label' => __( 'Button', 'coregas' ),
						'sub'       => $link_fields,
					),
				),
			),
		),

		/* ================= Pages: banner ================= */
		'page' => array(
			array(
				'id'          => 'seo_box',
				'title'       => __( 'SEO & sharing', 'coregas' ),
				'context'     => 'normal',
				'priority'    => 'low',
				'description' => __( 'Optional. Leave empty to use the page title and the defaults from Theme Settings, tab "SEO & sharing".', 'coregas' ),
				'fields'      => array(
					array( 'key' => 'seo_title', 'type' => 'text', 'label' => __( 'Title in Google', 'coregas' ) ),
					array( 'key' => 'seo_desc', 'type' => 'textarea', 'label' => __( 'Description in Google', 'coregas' ), 'rows' => 2, 'desc' => __( 'About 150 characters.', 'coregas' ) ),
					array( 'key' => 'seo_img', 'type' => 'image', 'label' => __( 'Image when shared', 'coregas' ) ),
					array( 'key' => 'seo_noindex', 'type' => 'checkbox', 'label' => __( 'Hide this page from search engines', 'coregas' ), 'default' => '0' ),
				),
			),
			array(
				'id'          => 'page_banner',
				'title'       => __( 'Page banner', 'coregas' ),
				'description' => __( 'Inner pages open with a dark banner. The home page uses its Page Sections instead.', 'coregas' ),
				'fields'      => array(
					array( 'key' => 'banner_show', 'type' => 'checkbox', 'label' => __( 'Show the banner', 'coregas' ), 'default' => '1' ),
					array( 'key' => 'banner_eyebrow', 'type' => 'text', 'label' => __( 'Small label', 'coregas' ) ),
					array( 'key' => 'banner_title', 'type' => 'text', 'label' => __( 'Title (defaults to the page title)', 'coregas' ) ),
					array( 'key' => 'banner_text', 'type' => 'textarea', 'label' => __( 'Intro text', 'coregas' ), 'rows' => 3 ),
					array( 'key' => 'banner_image', 'type' => 'image', 'label' => __( 'Background photo (optional)', 'coregas' ) ),
				),
			),
		),

		/* ================= Content types ================= */
		'cg_licence' => array(
			array(
				'id'     => 'licence',
				'title'  => __( 'Licence details', 'coregas' ),
				'fields' => array(
					array( 'key' => 'licence_no', 'type' => 'text', 'label' => __( 'Licence number (optional, leave empty to hide)', 'coregas' ) ),
					array( 'key' => 'issuer', 'type' => 'text', 'label' => __( 'Issued by', 'coregas' ) ),
					array( 'key' => 'granted', 'type' => 'text', 'label' => __( 'Date of grant (optional, leave empty to hide)', 'coregas' ) ),
					array( 'key' => 'activity', 'type' => 'text', 'label' => __( 'Licensed activity', 'coregas' ) ),
					array(
						'key'     => 'status',
						'type'    => 'select',
						'label'   => __( 'Status', 'coregas' ),
						'default' => 'active',
						'choices' => array( 'active' => __( 'Active', 'coregas' ), 'pending' => __( 'Pending', 'coregas' ), 'expired' => __( 'Expired', 'coregas' ) ),
					),
				),
			),
		),
		'cg_service' => array(
			array(
				'id'     => 'service',
				'title'  => __( 'Service details', 'coregas' ),
				'fields' => array(
					array( 'key' => 'service_tag', 'type' => 'text', 'label' => __( 'Small label', 'coregas' ), 'placeholder' => 'Gas supply chain' ),
				),
			),
		),
		'cg_industry' => array(
			array(
				'id'     => 'industry',
				'title'  => __( 'Industry details', 'coregas' ),
				'fields' => array(
					array( 'key' => 'industry_tag', 'type' => 'text', 'label' => __( 'Small label', 'coregas' ), 'placeholder' => 'Industrial consumers' ),
				),
			),
		),
	);

	// The same SEO box on news posts and every content type.
	$seo_box = $schemas['page'][0];
	foreach ( array( 'post', 'cg_licence', 'cg_service', 'cg_industry' ) as $type ) {
		$schemas[ $type ]   = isset( $schemas[ $type ] ) ? $schemas[ $type ] : array();
		$schemas[ $type ][] = $seo_box;
	}

	return $schemas;
}
