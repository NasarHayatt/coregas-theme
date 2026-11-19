<?php
/**
 * Core Gas theme bootstrap.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

define( 'COREGAS_VERSION', '2.1.1' );
define( 'COREGAS_DIR', get_template_directory() );
define( 'COREGAS_URI', get_template_directory_uri() );

require COREGAS_DIR . '/inc/fonts.php';          // heading/body font catalogue.
require COREGAS_DIR . '/inc/fields.php';         // meta-box field framework (no plugins needed).
require COREGAS_DIR . '/inc/settings-schema.php'; // Theme Settings fields (tabs).
require COREGAS_DIR . '/inc/schemas.php';        // field definitions for every post type.
require COREGAS_DIR . '/inc/post-types.php';     // custom post types.
require COREGAS_DIR . '/inc/settings.php';       // Theme Settings post type + helpers.
require COREGAS_DIR . '/inc/setup.php';          // theme supports, menus, assets.
require COREGAS_DIR . '/inc/head.php';           // CSS variables, fonts, analytics output.
require COREGAS_DIR . '/inc/template-tags.php';  // rendering helpers.
require COREGAS_DIR . '/inc/sections.php';       // Page Sections rendering.
require COREGAS_DIR . '/inc/contact-form.php';   // native contact / supply-request form.
require COREGAS_DIR . '/inc/seed.php';           // starter content on activation.
require COREGAS_DIR . '/inc/migrations.php';     // one-time structural updates for existing sites.
require COREGAS_DIR . '/inc/page-builder.php';   // edit a page's sections inside the page editor.
require COREGAS_DIR . '/inc/settings-tools.php'; // backup & restore, history, login branding, security.
require COREGAS_DIR . '/inc/site-extras.php';  // search-engine guard, SMTP, favicon, security headers, profile download.
require COREGAS_DIR . '/inc/admin-guide.php';    // dashboard guide + tidy admin menu.
