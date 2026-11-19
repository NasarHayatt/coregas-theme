<?php
/**
 * Site header.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

$cta_show  = '1' === coregas_setting( 'header_cta_show' );
$cta_label = coregas_setting( 'header_cta_label' );
$cta_url   = coregas_link( coregas_setting( 'header_cta_url' ) );
$plate     = '1' === coregas_setting( 'logo_plate' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#main"><?php echo esc_html( coregas_label( 'skip' ) ); ?></a>

<header class="hdr" id="hdr">
	<a class="hdr__brand<?php echo $plate ? '' : ' hdr__brand--bare'; ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( coregas_setting( 'company_name' ) ); ?>">
		<?php echo coregas_logo( 'header' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</a>
	<nav class="hdr__nav" id="nav" aria-label="<?php esc_attr_e( 'Main', 'coregas' ); ?>">
		<?php echo coregas_menu_links( 'primary' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php if ( $cta_show && $cta_label && $cta_url ) : ?>
			<a class="hdr__nav-cta" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ); ?></a>
		<?php endif; ?>
	</nav>
	<?php if ( $cta_show && $cta_label && $cta_url ) : ?>
		<a class="pill pill--flame hdr__cta" href="<?php echo esc_url( $cta_url ); ?>"><span><?php echo esc_html( $cta_label ); ?></span><i aria-hidden="true">↗</i></a>
	<?php endif; ?>
	<button class="hdr__menu" id="menuBtn" aria-expanded="false" aria-controls="nav"><span class="sr"><?php echo esc_html( coregas_label( 'menu' ) ); ?></span><b></b><b></b></button>
</header>

<main id="main">
