<?php
/**
 * Section: Columns (2–4 columns of text, optional photo and link each).
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

$sid   = (int) $args['id'];
$bg    = coregas_meta( $sid, 'sec_bg' );
$cols  = max( 2, min( 4, (int) coregas_meta( $sid, 'cols_count' ) ) );
$items = coregas_meta( $sid, 'cols_items' );
$items = is_array( $items ) ? $items : array();
?>
<section class="<?php echo esc_attr( coregas_section_classes( $sid ) ); ?> sec sec--<?php echo esc_attr( $bg ); ?>"<?php echo coregas_section_anchor( $sid ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="sec__inner">
		<?php coregas_section_head( $sid ); ?>
		<?php if ( $items ) : ?>
			<div class="cols" style="--cols:<?php echo (int) $cols; ?>">
				<?php foreach ( $items as $col ) : ?>
					<article class="col">
						<?php if ( ! empty( $col['image'] ) ) : ?>
							<figure class="col__media"><?php echo coregas_image( $col['image'], 'large', '', array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></figure>
						<?php endif; ?>
						<?php if ( ! empty( $col['tag'] ) ) : ?>
							<p class="mono col__tag"><?php echo esc_html( $col['tag'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $col['title'] ) ) : ?>
							<h3><?php echo esc_html( $col['title'] ); ?></h3>
						<?php endif; ?>
						<?php if ( ! empty( $col['text'] ) ) : ?>
							<div class="col__text"><?php echo coregas_paragraphs( $col['text'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
						<?php endif; ?>
						<?php if ( ! empty( $col['link_label'] ) && ! empty( $col['link_url'] ) ) : ?>
							<a class="row__more" href="<?php echo esc_url( coregas_link( $col['link_url'] ) ); ?>"><?php echo esc_html( $col['link_label'] ); ?> <span aria-hidden="true">→</span></a>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php coregas_section_links( $sid, in_array( $bg, array( 'dark', 'maroon' ), true ) ? 'light' : 'maroon' ); ?>
</section>
