<?php
/**
 * Section: Call to action.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

$sid = (int) $args['id'];
$bg  = coregas_meta( $sid, 'sec_bg' );
?>
<section class="<?php echo esc_attr( coregas_section_classes( $sid ) ); ?> sec sec--<?php echo esc_attr( $bg ); ?> sec--cta"<?php echo coregas_section_anchor( $sid ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="sec__inner cta-box">
		<?php coregas_section_head( $sid ); ?>
		<?php echo coregas_button( coregas_meta( $sid, 'cta_btn_label' ), coregas_meta( $sid, 'cta_btn_url' ), in_array( $bg, array( 'dark', 'maroon' ), true ) ? 'flame' : 'maroon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
</section>
