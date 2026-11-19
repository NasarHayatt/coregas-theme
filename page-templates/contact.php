<?php
/**
 * Template Name: Contact
 *
 * Contact details from Theme Settings, the supply-request form and an optional map.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$pid   = get_the_ID();
	$title = coregas_meta( $pid, 'banner_title' );
	coregas_banner(
		array(
			'fallback' => coregas_current_banner_placeholder(),
			'eyebrow' => coregas_meta( $pid, 'banner_eyebrow' ),
			'title'   => $title ? $title : get_the_title(),
			'text'    => coregas_meta( $pid, 'banner_text' ),
			'image'   => coregas_meta( $pid, 'banner_image' ),
		)
	);

	$map = coregas_setting( 'contact_map' );
	?>
	<section class="sec sec--light">
		<div class="sec__inner contact">
			<div class="contact__info">
				<?php if ( '' !== trim( get_the_content() ) ) : ?>
					<div class="prose"><?php the_content(); ?></div>
				<?php endif; ?>
				<?php coregas_contact_details(); ?>
			</div>
			<div class="contact__form">
				<?php coregas_contact_form(); ?>
			</div>
		</div>
		<?php if ( $map ) : ?>
			<div class="sec__inner contact__map">
				<iframe src="<?php echo esc_url( $map ); ?>" title="<?php esc_attr_e( 'Office location map', 'coregas' ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
			</div>
		<?php endif; ?>
	</section>
	<?php
	coregas_render_sections( $pid );
endwhile;

get_footer();
