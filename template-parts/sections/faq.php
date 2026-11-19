<?php
/**
 * Section: Questions & answers (click a question to open its answer).
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

$sid   = (int) $args['id'];
$bg    = coregas_meta( $sid, 'sec_bg' );
$items = coregas_meta( $sid, 'faq_items' );
$items = is_array( $items ) ? $items : array();
?>
<section class="<?php echo esc_attr( coregas_section_classes( $sid ) ); ?> sec sec--<?php echo esc_attr( $bg ); ?>"<?php echo coregas_section_anchor( $sid ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="sec__inner sec__inner--split">
		<?php coregas_section_head( $sid ); ?>
		<div class="faq">
			<?php
			foreach ( $items as $qa ) :
				if ( empty( $qa['q'] ) ) {
					continue;
				}
				?>
				<details class="faq__item">
					<summary><?php echo esc_html( $qa['q'] ); ?></summary>
					<div class="faq__a"><?php echo coregas_paragraphs( isset( $qa['a'] ) ? $qa['a'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
	<?php coregas_section_links( $sid, in_array( $bg, array( 'dark', 'maroon' ), true ) ? 'light' : 'maroon' ); ?>
</section>
