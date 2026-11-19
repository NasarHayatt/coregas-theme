<?php
/**
 * Section: Image and text. Large photo on one side, heading + copy on the other.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

$sid     = (int) $args['id'];
$bg      = coregas_meta( $sid, 'sec_bg' );
$left    = 'left' === coregas_meta( $sid, 'split_side' );
$caption = coregas_meta( $sid, 'split_caption' );
$img     = coregas_image( coregas_meta( $sid, 'split_image' ), 'large', coregas_stock_photo( (int) $args['post']->menu_order + ( $left ? 4 : 0 ) ), array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) );
?>
<section class="<?php echo esc_attr( coregas_section_classes( $sid ) ); ?> sec sec--<?php echo esc_attr( $bg ); ?>"<?php echo coregas_section_anchor( $sid ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="sec__inner split<?php echo $left ? ' split--img-left' : ''; ?>">
		<div class="split__body">
			<?php coregas_section_head( $sid ); ?>
			<?php if ( '' !== trim( $args['post']->post_content ) ) : ?>
				<div class="prose"><?php echo apply_filters( 'the_content', $args['post']->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput -- core content filter. ?></div>
			<?php endif; ?>
			<?php coregas_section_links( $sid, in_array( $bg, array( 'dark', 'maroon' ), true ) ? 'light' : 'maroon' ); ?>
		</div>
		<figure class="split__media">
			<?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $caption ) : ?>
				<figcaption class="mono"><?php echo esc_html( $caption ); ?></figcaption>
			<?php endif; ?>
		</figure>
	</div>
</section>
