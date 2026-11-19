<?php
/**
 * Section: Photo gallery.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

$sid    = (int) $args['id'];
$bg     = coregas_meta( $sid, 'sec_bg' );
$cols   = max( 2, min( 4, (int) coregas_meta( $sid, 'gal_columns' ) ) );
$images = coregas_meta( $sid, 'gal_images' );
$images = is_array( $images ) ? $images : array();
?>
<section class="<?php echo esc_attr( coregas_section_classes( $sid ) ); ?> sec sec--<?php echo esc_attr( $bg ); ?>"<?php echo coregas_section_anchor( $sid ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="sec__inner">
		<?php coregas_section_head( $sid ); ?>
		<?php if ( $images ) : ?>
			<div class="gallery" style="--cols:<?php echo (int) $cols; ?>">
				<?php
				foreach ( $images as $img ) :
					if ( empty( $img['image'] ) ) {
						continue;
					}
					$full = wp_get_attachment_image_url( (int) $img['image'], 'full' );
					?>
					<figure class="gallery__item">
						<a href="<?php echo esc_url( $full ); ?>" target="_blank" rel="noopener"><?php echo wp_get_attachment_image( (int) $img['image'], 'large', false, array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?></a>
						<?php if ( ! empty( $img['caption'] ) ) : ?>
							<figcaption><?php echo esc_html( $img['caption'] ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
		<?php elseif ( current_user_can( 'edit_posts' ) ) : ?>
			<p class="empty-note"><?php esc_html_e( 'No photos added yet. This note is only visible to logged-in editors.', 'coregas' ); ?></p>
		<?php endif; ?>
	</div>
	<?php coregas_section_links( $sid, in_array( $bg, array( 'dark', 'maroon' ), true ) ? 'light' : 'maroon' ); ?>
</section>
