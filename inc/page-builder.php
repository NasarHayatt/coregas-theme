<?php
/**
 * Edit a page's sections directly inside the page editor.
 *
 * Sections are still stored as cg_section posts (so nothing changes for the front end
 * or existing content), but editors never need to leave the page they are working on:
 * every section of the page is shown as a collapsible panel with all its fields, and
 * can be reordered, hidden, removed or added from the same screen.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Human labels for section types.
 *
 * @return array
 */
function coregas_section_type_labels() {
	return array(
		'hero'  => __( 'Hero (photos + headline)', 'coregas' ),
		'about' => __( 'About block (statement, image, pillars)', 'coregas' ),
		'split' => __( 'Image and text', 'coregas' ),
		'cards' => __( 'List of items (services, licences, companies…)', 'coregas' ),
		'steps' => __( 'Steps strip', 'coregas' ),
		'columns' => __( 'Columns (2-4 text columns)', 'coregas' ),
		'gallery' => __( 'Photo gallery', 'coregas' ),
		'faq'     => __( 'Questions & answers', 'coregas' ),
		'contact' => __( 'Contact details + form', 'coregas' ),
		'embed'   => __( 'Video or map', 'coregas' ),
		'text'    => __( 'Text', 'coregas' ),
		'cta'     => __( 'Call to action', 'coregas' ),
	);
}

/**
 * Where the list items of a "cards" section are edited.
 *
 * @param string $source Post type.
 * @return string
 */
function coregas_cards_source_link( $source ) {
	$labels = array(
		'cg_service'  => __( 'Supply Services', 'coregas' ),
		'cg_industry' => __( 'Industries', 'coregas' ),
		'cg_licence'  => __( 'Licences', 'coregas' ),
		'post'        => __( 'Posts (news)', 'coregas' ),
	);
	if ( ! isset( $labels[ $source ] ) ) {
		return '';
	}
	return sprintf(
		/* translators: %s: link to the list of items */
		__( 'The items in this list are edited under %s (opens in a new tab).', 'coregas' ),
		'<a href="' . esc_url( admin_url( 'edit.php?post_type=' . $source ) ) . '" target="_blank">' . esc_html( $labels[ $source ] ) . '</a>'
	);
}

add_action( 'add_meta_boxes_page', 'coregas_page_sections_box' );
/**
 * Register the "Page content" box.
 *
 * @param WP_Post $post Page.
 */
function coregas_page_sections_box( $post ) {
	add_meta_box( 'cg-page-sections', __( 'Page content: sections', 'coregas' ), 'coregas_render_page_sections', 'page', 'normal', 'high' );
}

/**
 * Render every section of the page as an editable panel.
 *
 * @param WP_Post $post Page.
 */
function coregas_render_page_sections( $post ) {
	wp_nonce_field( 'coregas_page_sections', 'coregas_page_sections_nonce' );
	$sections = get_posts(
		array(
			'post_type'      => 'cg_section',
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => 100,
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
			'meta_key'       => COREGAS_META_PREFIX . 'page', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => (string) $post->ID, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
	// Drafted (hidden) sections go to the end of the list.
	usort(
		$sections,
		function ( $a, $b ) {
			if ( $a->post_status !== $b->post_status ) {
				return 'publish' === $a->post_status ? -1 : 1;
			}
			return $a->menu_order - $b->menu_order;
		}
	);

	echo '<p class="cgs-intro">' . esc_html__( 'Everything shown on this page is edited here. Sections appear on the website in this order. Click a section to open it, change text or photos, then press Update.', 'coregas' ) . '</p>';

	echo '<div class="cgs-list">';
	foreach ( $sections as $pos => $section ) {
		coregas_render_section_panel( $section, $pos );
	}
	if ( ! $sections ) {
		echo '<p class="cgs-empty">' . esc_html__( 'This page has no sections yet. Add one below.', 'coregas' ) . '</p>';
	}
	echo '</div>';

	echo '<div class="cgs-add"><label for="cgs-add-type"><strong>' . esc_html__( 'Add a section:', 'coregas' ) . '</strong></label> ';
	echo '<select id="cgs-add-type" name="cgs_add_type"><option value="">' . esc_html__( '— choose a type —', 'coregas' ) . '</option>';
	foreach ( coregas_section_type_labels() as $k => $label ) {
		echo '<option value="' . esc_attr( $k ) . '">' . esc_html( $label ) . '</option>';
	}
	echo '</select> <button type="submit" class="button" name="cgs_add_submit" value="1">' . esc_html__( 'Add section and save page', 'coregas' ) . '</button>';
	echo '<p class="description">' . esc_html__( 'The new section is added at the end with starter text, ready to edit.', 'coregas' ) . '</p></div>';
}

/**
 * One collapsible section panel.
 *
 * @param WP_Post $section Section.
 * @param int     $pos     Position.
 */
function coregas_render_section_panel( $section, $pos ) {
	$sid    = $section->ID;
	$type   = coregas_meta( $sid, 'section_type' );
	$labels = coregas_section_type_labels();
	$base   = 'cgs[' . $sid . ']';
	$hidden = 'publish' !== $section->post_status;

	printf( '<details class="cgs-panel%s" data-cgs-panel style="order:%d"%s>', $hidden ? ' is-hidden' : '', (int) $pos, $pos < 1 && ! $hidden ? ' open' : '' );
	echo '<summary class="cgs-panel__head">';
	echo '<span class="cgs-panel__title">' . esc_html( $section->post_title ) . '</span>';
	echo '<span class="cgs-panel__type">' . esc_html( isset( $labels[ $type ] ) ? $labels[ $type ] : $type ) . '</span>';
	if ( $hidden ) {
		echo '<span class="cgs-panel__badge">' . esc_html__( 'Hidden', 'coregas' ) . '</span>';
	}
	echo '<span class="cgs-panel__tools">';
	echo '<button type="button" class="button-link cgs-up" aria-label="' . esc_attr__( 'Move up', 'coregas' ) . '">&uarr; ' . esc_html__( 'Up', 'coregas' ) . '</button>';
	echo '<button type="button" class="button-link cgs-down" aria-label="' . esc_attr__( 'Move down', 'coregas' ) . '">&darr; ' . esc_html__( 'Down', 'coregas' ) . '</button>';
	echo '</span></summary>';

	echo '<div class="cgs-panel__body">';
	printf( '<input type="hidden" class="cgs-order" name="%s[order]" value="%d">', esc_attr( $base ), (int) $pos );

	// Basics.
	echo '<div class="cgs-basics">';
	printf( '<label>%s <input type="text" class="regular-text" name="%s[title]" value="%s"></label>', esc_html__( 'Section name (admin only)', 'coregas' ), esc_attr( $base ), esc_attr( $section->post_title ) );
	printf( '<label>%s <select class="cgs-type" name="%s[section_type]">', esc_html__( 'Type', 'coregas' ), esc_attr( $base ) );
	foreach ( $labels as $k => $label ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $type, $k, false ), esc_html( $label ) );
	}
	echo '</select></label>';
	printf( '<label class="cg-check"><input type="hidden" name="%1$s[visible]" value="0"><input type="checkbox" name="%1$s[visible]" value="1"%2$s> %3$s</label>', esc_attr( $base ), checked( ! $hidden, true, false ), esc_html__( 'Show on the website', 'coregas' ) );
	printf( '<label class="cg-check cgs-remove"><input type="checkbox" name="%s[remove]" value="1"> %s</label>', esc_attr( $base ), esc_html__( 'Delete this section (moves to Trash on Update)', 'coregas' ) );
	echo '</div>';

	// Main text editor for types that use it.
	echo '<div class="cgs-group" data-types="text,split">';
	echo '<div class="cg-field cg-field--wide"><label class="cg-field__label">' . esc_html__( 'Text', 'coregas' ) . '</label><div class="cg-field__control">';
	wp_editor(
		$section->post_content,
		'cgsc_' . $sid,
		array(
			'textarea_name' => 'cgs_content[' . $sid . ']',
			'textarea_rows' => 8,
			'media_buttons' => false,
			'teeny'         => true,
		)
	);
	echo '</div></div></div>';

	// Field groups, each tagged with the section types it applies to.
	foreach ( coregas_groups_for( 'cg_section' ) as $group ) {
		$types = ! empty( $group['show_if']['section_type'] ) ? implode( ',', $group['show_if']['section_type'] ) : 'all';
		echo '<div class="cgs-group" data-types="' . esc_attr( $types ) . '">';
		if ( 'placement' !== $group['id'] ) {
			echo '<h4 class="cgs-group__title">' . esc_html( $group['title'] ) . '</h4>';
		}
		foreach ( $group['fields'] as $field ) {
			if ( in_array( $field['key'], array( 'section_type', 'page' ), true ) ) {
				continue;
			}
			$value = coregas_meta( $sid, $field['key'], isset( $field['default'] ) ? $field['default'] : '' );
			coregas_render_field_row( $field, $value, $base . '[' . $field['key'] . ']', 'cgs-' . $sid . '-' . $field['key'] );
		}
		if ( 'cards' === $group['id'] ) {
			echo '<p class="description cgs-source-note">' . wp_kses_post( coregas_cards_source_link( coregas_meta( $sid, 'cards_source' ) ) ) . '</p>';
		}
		echo '</div>';
	}
	echo '</div></details>';
}

add_action( 'save_post_page', 'coregas_save_page_sections', 20, 2 );
/**
 * Save section panels, order, visibility, removals and additions with the page.
 *
 * @param int     $page_id Page ID.
 * @param WP_Post $page    Page.
 */
function coregas_save_page_sections( $page_id, $page ) {
	if ( ! isset( $_POST['coregas_page_sections_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['coregas_page_sections_nonce'] ) ), 'coregas_page_sections' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $page_id ) || ! current_user_can( 'edit_page', $page_id ) ) {
		return;
	}
	if ( ! isset( $_POST['post_ID'] ) || (int) $_POST['post_ID'] !== (int) $page_id ) {
		return;
	}
	remove_action( 'save_post_page', 'coregas_save_page_sections', 20 ); // avoid re-entry.

	$posted   = isset( $_POST['cgs'] ) && is_array( $_POST['cgs'] ) ? wp_unslash( $_POST['cgs'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitised per field.
	$contents = isset( $_POST['cgs_content'] ) && is_array( $_POST['cgs_content'] ) ? wp_unslash( $_POST['cgs_content'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- wp_kses_post below.
	$types    = array_keys( coregas_section_type_labels() );
	$max      = -1;

	foreach ( $posted as $sid => $data ) {
		$sid     = (int) $sid;
		$section = get_post( $sid );
		// Only sections that really belong to this page, and that the user may edit.
		if ( ! $section || 'cg_section' !== $section->post_type || (int) coregas_meta( $sid, 'page' ) !== (int) $page_id || ! current_user_can( 'edit_post', $sid ) ) {
			continue;
		}
		if ( ! empty( $data['remove'] ) ) {
			wp_trash_post( $sid );
			continue;
		}
		$type  = in_array( isset( $data['section_type'] ) ? $data['section_type'] : '', $types, true ) ? $data['section_type'] : coregas_meta( $sid, 'section_type' );
		$order = isset( $data['order'] ) ? (int) $data['order'] : (int) $section->menu_order;
		$max   = max( $max, $order );

		$update = array(
			'ID'          => $sid,
			'post_title'  => isset( $data['title'] ) && '' !== trim( $data['title'] ) ? sanitize_text_field( $data['title'] ) : $section->post_title,
			'menu_order'  => $order,
			'post_status' => ! empty( $data['visible'] ) ? 'publish' : 'draft',
		);
		if ( isset( $contents[ $sid ] ) ) {
			$update['post_content'] = wp_kses_post( $contents[ $sid ] );
		}
		wp_update_post( $update );

		update_post_meta( $sid, COREGAS_META_PREFIX . 'section_type', $type );
		foreach ( coregas_groups_for( 'cg_section' ) as $group ) {
			foreach ( $group['fields'] as $field ) {
				if ( in_array( $field['key'], array( 'section_type', 'page' ), true ) || ! array_key_exists( $field['key'], $data ) ) {
					continue;
				}
				update_post_meta( $sid, COREGAS_META_PREFIX . $field['key'], coregas_sanitize_field( $field, $data[ $field['key'] ] ) );
			}
			// Repeaters with every row removed are absent from the form: store them empty.
			foreach ( $group['fields'] as $field ) {
				if ( 'repeater' === $field['type'] && ! array_key_exists( $field['key'], $data ) && in_array( $type, isset( $group['show_if']['section_type'] ) ? $group['show_if']['section_type'] : $types, true ) ) {
					update_post_meta( $sid, COREGAS_META_PREFIX . $field['key'], array() );
				}
			}
		}
	}

	// "Add section and save page".
	$add = isset( $_POST['cgs_add_type'] ) ? sanitize_key( wp_unslash( $_POST['cgs_add_type'] ) ) : '';
	if ( $add && in_array( $add, $types, true ) && current_user_can( 'publish_posts' ) ) {
		$labels  = coregas_section_type_labels();
		$starter = array(
			'text'  => array( array( 'sec_heading' => 'New section heading', 'sec_bg' => 'light' ), '<p>Write the text for this section here.</p>' ),
			'split' => array( array( 'sec_heading' => 'New section heading', 'sec_bg' => 'light', 'split_side' => 'right' ), '<p>Write the text for this section here.</p>' ),
			'cta'   => array( array( 'sec_heading' => 'New call to action', 'sec_bg' => 'maroon' ), '' ),
			'cards' => array( array( 'sec_heading' => 'New list', 'sec_bg' => 'light' ), '' ),
			'columns' => array(
				array(
					'sec_heading' => 'New columns',
					'sec_bg'      => 'light',
					'cols_count'  => '3',
					'cols_items'  => array(
						array( 'image' => '', 'tag' => '', 'title' => 'First column', 'text' => 'Write the text for this column.', 'link_label' => '', 'link_url' => '' ),
						array( 'image' => '', 'tag' => '', 'title' => 'Second column', 'text' => 'Write the text for this column.', 'link_label' => '', 'link_url' => '' ),
						array( 'image' => '', 'tag' => '', 'title' => 'Third column', 'text' => 'Write the text for this column.', 'link_label' => '', 'link_url' => '' ),
					),
				),
				'',
			),
			'gallery' => array( array( 'sec_heading' => 'Gallery', 'sec_bg' => 'light', 'gal_columns' => '3' ), '' ),
			'faq'     => array(
				array(
					'sec_heading' => 'Questions & answers',
					'sec_bg'      => 'light',
					'faq_items'   => array( array( 'q' => 'Write a question here', 'a' => 'Write the answer here.' ) ),
				),
				'',
			),
			'contact' => array( array( 'sec_eyebrow' => 'Get in touch', 'sec_heading' => 'Talk to our team', 'sec_bg' => 'light' ), '' ),
			'embed'   => array( array( 'sec_heading' => 'Video', 'sec_bg' => 'light' ), '' ),
			'steps' => array(
				array(
					'sec_heading' => 'New steps',
					'sec_bg'      => 'light',
					'steps'       => array(
						array( 'tag' => '', 'title' => 'First step', 'text' => 'Describe this step.', 'image' => '' ),
						array( 'tag' => '', 'title' => 'Second step', 'text' => 'Describe this step.', 'image' => '' ),
						array( 'tag' => '', 'title' => 'Third step', 'text' => 'Describe this step.', 'image' => '' ),
					),
				),
				'',
			),
		);
		$s = isset( $starter[ $add ] ) ? $starter[ $add ] : array( array(), '' );
		coregas_seed_section( get_the_title( $page_id ) . ': ' . wp_strip_all_tags( $labels[ $add ] ), $page_id, $add, $max + 1, $s[0], $s[1] );
	}

	add_action( 'save_post_page', 'coregas_save_page_sections', 20, 2 );
}

/* -------------------------------------------------------------------------
 * Classic editor for pages and theme content, so all fields sit on one clear screen
 * ---------------------------------------------------------------------- */
add_filter(
	'use_block_editor_for_post_type',
	function ( $use, $post_type ) {
		return in_array( $post_type, array( 'page', 'cg_section', 'cg_industry', 'cg_service', 'cg_licence' ), true ) ? false : $use;
	},
	10,
	2
);

add_action( 'load-post.php', 'coregas_front_page_editor' );
/**
 * The home page is built only from sections: hide its unused main editor.
 */
function coregas_front_page_editor() {
	$id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0; // phpcs:ignore WordPress.Security.NonceVerification
	if ( $id && $id === (int) get_option( 'page_on_front' ) ) {
		remove_post_type_support( 'page', 'editor' );
		add_action(
			'edit_form_after_title',
			function () {
				echo '<div class="notice notice-info inline"><p>' . esc_html__( 'This is the home page. Its content is the sections below: open a section to edit its text and photos.', 'coregas' ) . '</p></div>';
			}
		);
	}
}

add_action( 'init', 'coregas_page_supports', 20 );
/**
 * Pages on this site don't use comments or raw custom fields: remove those boxes.
 */
function coregas_page_supports() {
	foreach ( array( 'comments', 'trackbacks', 'custom-fields' ) as $feature ) {
		remove_post_type_support( 'page', $feature );
	}
}

/* -------------------------------------------------------------------------
 * Pages list: show how many sections each page has
 * ---------------------------------------------------------------------- */
add_filter(
	'manage_page_posts_columns',
	function ( $cols ) {
		$cols['cg_sections'] = __( 'Sections', 'coregas' );
		return $cols;
	}
);
add_action(
	'manage_page_posts_custom_column',
	function ( $col, $id ) {
		if ( 'cg_sections' === $col ) {
			$n = count( coregas_sections_for_page_any( $id ) );
			echo $n ? (int) $n : '&mdash;';
		}
	},
	10,
	2
);

add_action( 'add_meta_boxes_page', 'coregas_front_page_boxes', 20 );
/**
 * The home page opens with its Hero section, not a banner: hide the banner box there.
 *
 * @param WP_Post $post Page.
 */
function coregas_front_page_boxes( $post ) {
	if ( (int) $post->ID === (int) get_option( 'page_on_front' ) ) {
		remove_meta_box( 'cg-group-page_banner', 'page', 'normal' );
	}
}
