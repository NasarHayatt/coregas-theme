<?php
/**
 * Top banner of inner pages, archives and single items: black-and-white photo,
 * dark overlay, label, title and intro.
 *
 * Photo priority: the page's own banner photo → Theme Settings default banner →
 * a placeholder chosen for the page (args['fallback']).
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

$a = wp_parse_args(
	$args,
	array(
		'eyebrow'  => '',
		'title'    => '',
		'text'     => '',
		'image'    => 0,
		'fallback' => coregas_banner_placeholder( 'default' ),
	)
);
$image_id = (int) $a['image'] ? (int) $a['image'] : (int) coregas_setting( 'banner_default' );
$photo    = coregas_image( $image_id, 'coregas-wide', $a['fallback'], array( 'alt' => '', 'decoding' => 'async', 'fetchpriority' => 'high', 'class' => $image_id ? 'is-colour' : '' ) );
?>
<section class="banner">
	<div class="banner__photo" aria-hidden="true"><?php echo $photo; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	<div class="banner__inner">
		<?php if ( $a['eyebrow'] ) : ?>
			<p class="mono label"><span class="dot"></span><?php echo esc_html( $a['eyebrow'] ); ?></p>
		<?php endif; ?>
		<h1><?php echo esc_html( $a['title'] ); ?></h1>
		<?php if ( $a['text'] ) : ?>
			<div class="banner__text"><?php echo coregas_paragraphs( $a['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php endif; ?>
	</div>
</section>
