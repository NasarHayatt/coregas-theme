<?php
/**
 * Output driven by Theme Settings: CSS variables, SEO & sharing tags,
 * analytics, verification codes, custom CSS and code, favicon.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', 'coregas_css_variables', 20 );
/**
 * Print global colour, typography and layout variables.
 */
function coregas_css_variables() {
	$c = function ( $key ) {
		$v = sanitize_hex_color( (string) coregas_setting( $key ) );
		if ( ! $v ) {
			$f = coregas_find_field( 'cg_settings', $key );
			$v = sanitize_hex_color( (string) ( $f ? $f['default'] : '#000000' ) );
		}
		return $v;
	};
	$heading = coregas_heading_fonts();
	$body    = coregas_body_fonts();
	$h       = coregas_setting( 'font_heading' );
	$b       = coregas_setting( 'font_body' );
	$h       = isset( $heading[ $h ] ) ? $heading[ $h ] : $heading['barlow'];
	$b       = isset( $body[ $b ] ) ? $body[ $b ] : $body['geist'];
	$v       = $h['vars'];
	$size    = max( 14, min( 22, (int) coregas_setting( 'font_base_size' ) ) );
	$hscale  = max( 60, min( 150, (int) coregas_setting( 'heading_scale' ) ) ) / 100;
	$case    = coregas_setting( 'heading_case' );
	$case    = in_array( $case, array( 'none', 'uppercase' ), true ) ? $case : $v['case'];
	$space   = array( 'compact' => '.7', 'normal' => '1', 'spacious' => '1.35' );
	$sp      = coregas_setting( 'section_spacing' );
	$rgb     = sscanf( $c( 'color_header' ), '#%02x%02x%02x' );

	$css  = ':root{';
	$css .= '--logo-maroon:' . $c( 'color_maroon' ) . ';';
	$css .= '--logo-grey:' . $c( 'color_grey' ) . ';';
	$css .= '--logo-orange:' . $c( 'color_primary' ) . ';';
	$css .= '--logo-crimson:' . $c( 'color_crimson' ) . ';';
	$css .= '--logo-red:' . $c( 'color_red' ) . ';';
	$css .= '--logo-amber:' . $c( 'color_amber' ) . ';';
	$css .= '--logo-yellow:' . $c( 'color_yellow' ) . ';';
	$css .= '--surface:' . $c( 'color_surface' ) . ';';
	$css .= '--bone:' . $c( 'color_light' ) . ';';
	$css .= '--char:' . $c( 'color_dark' ) . ';';
	$css .= '--header-bg:rgba(' . (int) $rgb[0] . ',' . (int) $rgb[1] . ',' . (int) $rgb[2] . ',.97);';
	$css .= '--btn-text:' . $c( 'color_btn_text' ) . ';';
	$css .= '--footer-bg:' . $c( 'color_footer_bg' ) . ';--footer-head:' . $c( 'color_footer_head' ) . ';--footer-text:' . $c( 'color_footer_text' ) . ';';
	$css .= '--display:"' . $h['family'] . '",Arial,sans-serif;';
	$css .= '--body:"' . $b['family'] . '","Helvetica Neue",Arial,sans-serif;';
	$css .= '--h-weight:' . (int) $v['weight'] . ';--h-weight-hero:' . (int) $v['hero'] . ';';
	$css .= '--h-lh:' . $v['lh'] . ';--h-track:' . $v['track'] . ';--h-case:' . $case . ';';
	$css .= '--h-scale:' . round( $v['scale'] * $hscale, 3 ) . ';--h-scale-sm:' . round( $v['scale-sm'] * $hscale, 3 ) . ';';
	$css .= '--base-size:' . $size . 'px;';
	$css .= '--logo-h:' . max( 20, min( 80, (int) coregas_setting( 'logo_height' ) ) ) . 'px;';
	$css .= '--maxw:' . max( 960, min( 2400, (int) coregas_setting( 'content_width' ) ) ) . 'px;';
	$css .= '--space:' . ( isset( $space[ $sp ] ) ? $space[ $sp ] : '1' ) . ';';
	$css .= '--radius:' . max( 0, min( 48, (int) coregas_setting( 'radius' ) ) ) . 'px;';
	$css .= '--hero-h:' . max( 50, min( 100, (int) coregas_setting( 'hero_height' ) ) ) . 'svh;';
	$css .= '}';
	if ( '1' !== coregas_setting( 'pulse_dots' ) ) {
		$css .= '.dot::after{display:none}';
	}
	if ( '1' === coregas_setting( 'header_cta_mobile' ) ) {
		$css .= '@media (max-width:560px){.hdr__cta{display:inline-flex!important;margin-left:auto;height:40px;padding-left:14px;font-size:13px}.hdr__cta i{display:none}.hdr__menu{margin-left:8px}}';
	}

	// Values come from sanitised colours, clamped numbers and the fixed font catalogue.
	echo '<style id="coregas-vars">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput

	$custom = (string) coregas_setting( 'custom_css' );
	if ( '' !== trim( $custom ) ) {
		echo '<style id="coregas-custom-css">' . wp_strip_all_tags( $custom ) . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

add_action( 'wp_head', 'coregas_analytics_head', 1 );
/**
 * GA4, GTM, Meta Pixel, verification codes and custom head code.
 */
function coregas_analytics_head() {
	$gtm = coregas_clean_tracking_id( coregas_setting( 'gtm_id' ), 'GTM-' );
	$ga4 = coregas_clean_tracking_id( coregas_setting( 'ga4_id' ), 'G-' );

	if ( $gtm ) {
		?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?php echo esc_js( $gtm ); ?>');</script>
		<?php
	}
	if ( $ga4 ) {
		?>
<!-- Google Analytics 4 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga4 ); ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?php echo esc_js( $ga4 ); ?>');</script>
		<?php
	}
	$pixel = preg_replace( '/[^0-9]/', '', (string) coregas_setting( 'meta_pixel' ) );
	if ( $pixel ) {
		?>
<!-- Meta Pixel -->
<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','<?php echo esc_js( $pixel ); ?>');fbq('track','PageView');</script>
		<?php
	}
	foreach ( array( 'seo_verify_google' => 'google-site-verification', 'seo_verify_bing' => 'msvalidate.01' ) as $key => $name ) {
		$code = sanitize_text_field( (string) coregas_setting( $key ) );
		if ( $code ) {
			echo '<meta name="' . esc_attr( $name ) . '" content="' . esc_attr( $code ) . '">' . "\n";
		}
	}
	$head = (string) coregas_setting( 'head_code' );
	if ( '' !== trim( $head ) ) {
		echo $head . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- administrator-supplied code, filtered on save for users without unfiltered_html.
	}
}

add_action( 'wp_body_open', 'coregas_gtm_noscript' );
/**
 * GTM noscript fallback.
 */
function coregas_gtm_noscript() {
	$gtm = coregas_clean_tracking_id( coregas_setting( 'gtm_id' ), 'GTM-' );
	if ( $gtm ) {
		echo '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . esc_attr( $gtm ) . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
	}
}

add_action( 'wp_footer', 'coregas_footer_code', 99 );
/**
 * Custom code before </body>.
 */
function coregas_footer_code() {
	$code = (string) coregas_setting( 'footer_code' );
	if ( '' !== trim( $code ) ) {
		echo $code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- administrator-supplied code.
	}
}

/**
 * Accept only well-formed tracking IDs.
 *
 * @param string $id     Raw ID.
 * @param string $prefix Expected prefix.
 * @return string
 */
function coregas_clean_tracking_id( $id, $prefix ) {
	$id = strtoupper( trim( (string) $id ) );
	return ( 0 === strpos( $id, $prefix ) && preg_match( '/^[A-Z0-9-]{4,20}$/', $id ) ) ? $id : '';
}

/* -------------------------------------------------------------------------
 * SEO & social sharing (skipped when an SEO plugin is active)
 * ---------------------------------------------------------------------- */

/**
 * Whether a dedicated SEO plugin handles titles and meta tags.
 *
 * @return bool
 */
function coregas_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' ) || class_exists( 'The_SEO_Framework\Load' );
}

/**
 * Post whose SEO fields apply to the current view (pages, posts, items).
 *
 * @return int
 */
function coregas_seo_post_id() {
	return ( is_singular() || is_front_page() ) ? (int) get_queried_object_id() : 0;
}

add_filter( 'document_title_separator', 'coregas_title_separator' );
/**
 * Title separator from settings.
 *
 * @param string $sep Separator.
 * @return string
 */
function coregas_title_separator( $sep ) {
	$custom = trim( (string) coregas_setting( 'seo_separator' ) );
	return ( $custom && ! coregas_seo_plugin_active() ) ? $custom : $sep;
}

add_filter( 'pre_get_document_title', 'coregas_seo_title', 20 );
/**
 * Custom title from the page's SEO box.
 *
 * @param string $title Title.
 * @return string
 */
function coregas_seo_title( $title ) {
	$id = coregas_seo_post_id();
	if ( ! $id || coregas_seo_plugin_active() ) {
		return $title;
	}
	$custom = trim( (string) coregas_meta( $id, 'seo_title' ) );
	return $custom ? $custom : $title;
}

add_action( 'wp_head', 'coregas_seo_meta', 2 );
/**
 * Description, robots and Open Graph / Twitter tags.
 */
function coregas_seo_meta() {
	if ( coregas_seo_plugin_active() ) {
		return;
	}
	$id    = coregas_seo_post_id();
	$desc  = $id ? trim( (string) coregas_meta( $id, 'seo_desc' ) ) : '';
	$image = $id ? (int) coregas_meta( $id, 'seo_img' ) : 0;
	if ( ! $desc && $id && has_excerpt( $id ) ) {
		$desc = get_the_excerpt( $id );
	}
	if ( ! $desc ) {
		$desc = (string) coregas_setting( 'seo_description' );
	}
	if ( ! $image && $id && has_post_thumbnail( $id ) ) {
		$image = get_post_thumbnail_id( $id );
	}
	if ( ! $image ) {
		$image = (int) coregas_setting( 'seo_image' );
	}
	$url = $id ? get_permalink( $id ) : home_url( '/' );
	$img = $image ? wp_get_attachment_image_url( $image, 'coregas-wide' ) : '';

	if ( $id && '1' === coregas_meta( $id, 'seo_noindex' ) ) {
		echo '<meta name="robots" content="noindex, follow">' . "\n";
	}
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $desc ) ) . '">' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( wp_strip_all_tags( $desc ) ) . '">' . "\n";
	}
	echo '<meta property="og:type" content="' . ( is_singular( 'post' ) ? 'article' : 'website' ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( coregas_setting( 'company_name' ) ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( wp_get_document_title() ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	if ( $img ) {
		echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
	}
	echo '<meta name="twitter:card" content="' . ( $img ? 'summary_large_image' : 'summary' ) . '">' . "\n";
}

add_action( 'save_post', 'coregas_sync_favicon', 30 ); // after the field save (priority 10).
/**
 * The favicon chosen in Theme Settings becomes the WordPress site icon.
 *
 * @param int $post_id Settings post.
 */
function coregas_sync_favicon( $post_id ) {
	if ( 'cg_settings' !== get_post_type( $post_id ) ) {
		return;
	}
	$icon = (int) get_post_meta( $post_id, COREGAS_META_PREFIX . 'favicon', true );
	if ( $icon && current_user_can( 'manage_options' ) ) {
		update_option( 'site_icon', $icon );
	}
}
