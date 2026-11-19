<?php
/**
 * Section: Contact details + enquiry form (+ optional map), for any page.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

$sid     = (int) $args['id'];
$bg      = coregas_meta( $sid, 'sec_bg' );
$details = '1' === coregas_meta( $sid, 'ct_show_details' );
$form    = '1' === coregas_meta( $sid, 'ct_show_form' );
$map     = '1' === coregas_meta( $sid, 'ct_show_map' ) ? coregas_setting( 'contact_map' ) : '';
?>
<section class="<?php echo esc_attr( coregas_section_classes( $sid ) ); ?> sec sec--<?php echo esc_attr( $bg ); ?>"<?php echo coregas_section_anchor( $sid ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="sec__inner contact<?php echo ( $details && $form ) ? '' : ' contact--single'; ?>">
		<div class="contact__info">
			<?php coregas_section_head( $sid ); ?>
			<?php
			if ( $details ) {
				coregas_contact_details();
			}
			?>
		</div>
		<?php if ( $form ) : ?>
			<div class="contact__form"><?php coregas_contact_form(); ?></div>
		<?php endif; ?>
	</div>
	<?php if ( $map ) : ?>
		<div class="sec__inner contact__map">
			<iframe src="<?php echo esc_url( $map ); ?>" title="<?php esc_attr_e( 'Office location map', 'coregas' ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
		</div>
	<?php endif; ?>
</section>
