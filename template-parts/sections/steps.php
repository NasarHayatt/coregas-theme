<?php
/**
 * Section: Steps strip from the section's own steps.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

$sid   = (int) $args['id'];
$bg    = coregas_meta( $sid, 'sec_bg' );
$steps = coregas_meta( $sid, 'steps' );
?>
<section class="<?php echo esc_attr( coregas_section_classes( $sid ) ); ?> sec sec--<?php echo esc_attr( $bg ); ?>"<?php echo coregas_section_anchor( $sid ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="sec__inner">
		<?php coregas_section_head( $sid ); ?>
		<?php coregas_render_strip( is_array( $steps ) ? $steps : array(), '0' !== (string) coregas_meta( $sid, 'steps_photos' ) ); ?>
	</div>
</section>
