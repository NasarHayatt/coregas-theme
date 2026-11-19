<?php
/**
 * Section: Rich text (main editor content).
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

$sid = (int) $args['id'];
$bg  = coregas_meta( $sid, 'sec_bg' );
?>
<section class="<?php echo esc_attr( coregas_section_classes( $sid ) ); ?> sec sec--<?php echo esc_attr( $bg ); ?>"<?php echo coregas_section_anchor( $sid ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="sec__inner sec__inner--split">
		<?php coregas_section_head( $sid ); ?>
		<div class="prose">
			<?php echo apply_filters( 'the_content', $args['post']->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput -- core content filter. ?>
		</div>
	</div>
	<?php coregas_section_links( $sid, in_array( $bg, array( 'dark', 'maroon' ), true ) ? 'light' : 'maroon' ); ?>
</section>
