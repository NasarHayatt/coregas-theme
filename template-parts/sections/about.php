<?php
/**
 * Section: About.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

$sid     = (int) $args['id'];
$m       = function ( $k ) use ( $sid ) {
	return coregas_meta( $sid, $k );
};
$pillars = $m( 'about_pillars' );
$img     = coregas_image( $m( 'about_image' ), 'large', 'https://images.unsplash.com/photo-1627752633728-85ebd86dfe4a?w=1400&q=80&auto=format&fit=crop', array( 'alt' => '', 'loading' => 'lazy' ) );
?>
<section class="about <?php echo esc_attr( coregas_section_classes( $sid ) ); ?>"<?php echo coregas_section_anchor( $sid, 'about' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>

	<div class="about__head">
		<div>
			<?php if ( $m( 'about_eyebrow' ) ) : ?>
				<p class="mono label"><span class="dot"></span><?php echo esc_html( $m( 'about_eyebrow' ) ); ?></p>
			<?php endif; ?>
			<h2><?php echo esc_html( $m( 'about_heading' ) ); ?></h2>
		</div>
		<div class="about__intro"><?php echo coregas_paragraphs( $m( 'about_intro' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	</div>

	<div class="about__body">
		<?php if ( $img ) : ?>
			<figure class="about__img">
				<?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( $m( 'about_caption' ) ) : ?>
					<figcaption class="mono"><?php echo esc_html( $m( 'about_caption' ) ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endif; ?>

		<?php if ( is_array( $pillars ) && $pillars ) : ?>
			<div class="pillars">
				<?php foreach ( $pillars as $p ) : ?>
					<article class="pillar">
						<?php if ( ! empty( $p['tag'] ) ) : ?>
							<p class="mono pillar__tag"><?php echo esc_html( $p['tag'] ); ?></p>
						<?php endif; ?>
						<h3><?php echo esc_html( $p['title'] ); ?></h3>
						<?php if ( ! empty( $p['text'] ) ) : ?>
							<p><?php echo esc_html( $p['text'] ); ?></p>
						<?php endif; ?>
						<?php
						$chips = array_filter( array_map( 'trim', explode( ',', (string) ( isset( $p['chips'] ) ? $p['chips'] : '' ) ) ) );
						if ( $chips ) :
							?>
							<ul class="chain">
								<?php foreach ( $chips as $chip ) : ?>
									<li><?php echo esc_html( $chip ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>


</section>
