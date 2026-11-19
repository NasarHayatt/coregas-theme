<?php
/**
 * Not found.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="sec sec--light notfound">
	<div class="sec__inner">
		<p class="mono label"><span class="dot"></span><?php echo esc_html( coregas_setting( 'nf_eyebrow' ) ); ?></p>
		<h1><?php echo esc_html( coregas_setting( 'nf_title' ) ); ?></h1>
		<p class="notfound__text"><?php echo esc_html( coregas_setting( 'nf_text' ) ); ?></p>
		<?php echo coregas_button( coregas_setting( 'nf_button' ), home_url( '/' ), 'maroon', '←' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
</section>
<?php
get_footer();
