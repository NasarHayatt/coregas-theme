<?php
/**
 * Section: Hero. A slow cross-fading slideshow of photos behind the headline.
 * Content from the "Hero" box, styling from "Hero: design".
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

$sid = (int) $args['id'];
$m   = function ( $k ) use ( $sid ) {
	return coregas_meta( $sid, $k );
};
$hex = function ( $k ) use ( $m ) {
	return sanitize_hex_color( (string) $m( $k ) );
};
$pct = function ( $k, $min, $max, $def = 100 ) use ( $m ) {
	$v = (int) $m( $k );
	return ( $v ? max( $min, min( $max, $v ) ) : $def ) / 100;
};

$readout = $m( 'hero_readout' );
$bright  = max( 5, min( 100, (int) $m( 'hero_darkness' ) ) );
$bw      = '1' === $m( 'hd_bw' );
$mobile  = (int) $m( 'hd_photo_mobile' );
$focus   = array(
	'default' => '30% 60%',
	'center'  => '50% 50%',
	'top'     => '50% 0%',
	'bottom'  => '50% 100%',
	'left'    => '0% 50%',
	'right'   => '100% 50%',
);
$focus   = isset( $focus[ $m( 'hd_photo_focus' ) ] ) ? $focus[ $m( 'hd_photo_focus' ) ] : $focus['default'];

// Photos: the main photo, then the extra slideshow photos.
$photos = array();
if ( (int) $m( 'hero_image' ) && wp_attachment_is_image( (int) $m( 'hero_image' ) ) ) {
	$photos[] = (int) $m( 'hero_image' );
}
$slides = $m( 'hero_slides' );
foreach ( is_array( $slides ) ? $slides : array() as $row ) {
	if ( ! empty( $row['image'] ) && wp_attachment_is_image( (int) $row['image'] ) ) {
		$photos[] = (int) $row['image'];
	}
}
// Placeholders until real photos are added (the image CDN can deliver them black and white).
if ( ! $photos ) {
	foreach ( array( '1509390288171-ce2088f7d08e', '1509390032417-bd802f3fc669', '1627752633728-85ebd86dfe4a' ) as $ph ) {
		$photos[] = 'https://images.unsplash.com/photo-' . $ph . '?w=2400&q=80&auto=format&fit=crop' . ( $bw ? '&sat=-100&con=15' : '' );
	}
}
$interval = (int) $m( 'hd_slide_interval' );
$interval = $interval ? max( 3, min( 20, $interval ) ) : 7;

// Per-hero style variables (only the ones that are set).
$vars = array(
	'--hero-scale'   => $pct( 'hd_title_size', 50, 150 ),
	'--hero-overlay' => $hex( 'hd_overlay' ) ? $hex( 'hd_overlay' ) : '#000000',
);
foreach ( array( 'hd_title_color' => '--hero-title', 'hd_accent_color' => '--hero-accent', 'hd_sub_color' => '--hero-sub', 'hd_lead_color' => '--hero-lead', 'hd_meta_color' => '--hero-meta' ) as $k => $var ) {
	if ( $hex( $k ) ) {
		$vars[ $var ] = $hex( $k );
	}
}
$height = (int) $m( 'hd_height' );
if ( $height > 0 ) {
	$vars['--hero-h-own'] = max( 40, min( 100, $height ) ) . 'svh';
}
$style = '';
foreach ( $vars as $k => $v ) {
	$style .= $k . ':' . $v . ';';
}

$classes = array( 'hero', coregas_section_classes( $sid ) );
if ( 'center' === $m( 'hd_align' ) ) {
	$classes[] = 'hero--center';
}
if ( in_array( $m( 'hd_valign' ), array( 'top', 'bottom' ), true ) ) {
	$classes[] = 'hero--v-' . $m( 'hd_valign' );
}
if ( '1' !== $m( 'hd_intro' ) ) {
	$classes[] = 'hero--no-intro';
}
if ( $height > 0 ) {
	$classes[] = 'hero--own-height';
}

// Buttons.
$icons    = array( 'down' => '↓', 'out' => '↗', 'none' => '' );
$btn_url  = coregas_link( $m( 'hero_btn_url' ) );
$btn_sty  = in_array( $m( 'hd_btn_style' ), array( 'light', 'flame', 'maroon', 'outline' ), true ) ? $m( 'hd_btn_style' ) : 'light';
$btn_icon = isset( $icons[ $m( 'hd_btn_icon' ) ] ) ? $icons[ $m( 'hd_btn_icon' ) ] : '↓';
$btn2_url = coregas_link( $m( 'hd_btn2_url' ) );
$btn2_sty = in_array( $m( 'hd_btn2_style' ), array( 'light', 'flame', 'maroon', 'outline' ), true ) ? $m( 'hd_btn2_style' ) : 'outline';
?>
<section class="<?php echo esc_attr( trim( implode( ' ', $classes ) ) ); ?>" style="<?php echo esc_attr( $style ); ?>"<?php echo coregas_section_anchor( $sid, 'top' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="hero__photo" aria-hidden="true" data-interval="<?php echo (int) $interval; ?>" style="--hero-bright:<?php echo esc_attr( $bright / 100 ); ?>">
		<?php foreach ( $photos as $i => $photo ) : ?>
			<div class="hero__slide<?php echo 0 === $i ? ' is-active' : ''; ?>">
				<picture>
					<?php if ( 0 === $i && $mobile && wp_attachment_is_image( $mobile ) ) : ?>
						<source media="(max-width: 700px)" srcset="<?php echo esc_url( wp_get_attachment_image_url( $mobile, 'large' ) ); ?>">
					<?php endif; ?>
					<?php
					$attr = array(
						'alt'      => '',
						'decoding' => 'async',
						'class'    => ( is_int( $photo ) && $bw ) ? 'is-colour' : '',
						'style'    => 'object-position:' . $focus,
					);
					if ( 0 === $i ) {
						$attr['fetchpriority'] = 'high';
					} else {
						$attr['fetchpriority'] = 'low';
					}
					echo is_int( $photo ) // phpcs:ignore WordPress.Security.EscapeOutput
						? coregas_image( $photo, 'coregas-wide', '', $attr )
						: coregas_image( 0, 'coregas-wide', $photo, $attr );
					?>
				</picture>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( '1' === $m( 'hd_show_meta' ) ) : ?>
		<div class="hero__meta">
			<span class="mono"><?php echo esc_html( $m( 'hero_eyebrow_left' ) ); ?></span>
			<span class="mono"><?php echo esc_html( $m( 'hero_eyebrow_right' ) ); ?></span>
		</div>
	<?php else : ?>
		<div class="hero__meta hero__meta--empty" aria-hidden="true"></div>
	<?php endif; ?>

	<h1 class="hero__title">
		<?php if ( $m( 'hero_line1' ) ) : ?>
			<span class="line"><span class="word"><?php echo esc_html( $m( 'hero_line1' ) ); ?></span></span>
		<?php endif; ?>
		<span class="line">
			<?php if ( $m( 'hero_line2' ) ) : ?>
				<span class="word"><?php echo esc_html( $m( 'hero_line2' ) ); ?></span>
			<?php endif; ?>
			<?php if ( $m( 'hero_accent' ) ) : ?>
				<em class="word"><?php echo esc_html( $m( 'hero_accent' ) ); ?></em>
			<?php endif; ?>
		</span>
		<?php if ( $m( 'hero_subline' ) ) : ?>
			<span class="line line--small"><span class="word"><?php echo esc_html( $m( 'hero_subline' ) ); ?></span></span>
		<?php endif; ?>
	</h1>

	<div class="hero__foot">
		<?php if ( $m( 'hero_lead' ) ) : ?>
			<p class="hero__lead"><?php echo esc_html( $m( 'hero_lead' ) ); ?></p>
		<?php endif; ?>
		<div class="hero__buttons">
			<?php if ( $m( 'hero_btn_label' ) && $btn_url ) : ?>
				<a class="pill pill--<?php echo esc_attr( $btn_sty ); ?><?php echo $btn_icon ? '' : ' pill--noicon'; ?>" href="<?php echo esc_url( $btn_url ); ?>"><span><?php echo esc_html( $m( 'hero_btn_label' ) ); ?></span><?php echo $btn_icon ? '<i aria-hidden="true">' . esc_html( $btn_icon ) . '</i>' : ''; ?></a>
			<?php endif; ?>
			<?php if ( $m( 'hd_btn2_label' ) && $btn2_url ) : ?>
				<a class="pill pill--<?php echo esc_attr( $btn2_sty ); ?> pill--noicon" href="<?php echo esc_url( $btn2_url ); ?>"><span><?php echo esc_html( $m( 'hd_btn2_label' ) ); ?></span></a>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( '1' === $m( 'hd_show_readout' ) && is_array( $readout ) && $readout ) : ?>
		<div class="hero__readout">
			<?php foreach ( $readout as $r ) : ?>
				<div><span class="mono"><?php echo esc_html( $r['label'] ); ?></span><b><?php echo esc_html( $r['value'] ); ?></b></div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
