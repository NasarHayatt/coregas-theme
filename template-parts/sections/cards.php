<?php
/**
 * Section: items from a post type, shown as alternating image/text rows.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

$sid    = (int) $args['id'];
$bg     = coregas_meta( $sid, 'sec_bg' );
$source = coregas_meta( $sid, 'cards_source' );
$count  = (int) coregas_meta( $sid, 'cards_count' );
$layout = coregas_meta( $sid, 'cards_layout' );

$items = get_posts(
	array(
		'post_type'      => post_type_exists( $source ) ? $source : 'post',
		'post_status'    => 'publish',
		'posts_per_page' => $count > 0 ? $count : 100,
		'orderby'        => 'post' === $source ? 'date' : array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'order'          => 'post' === $source ? 'DESC' : 'ASC',
	)
);
?>
<section class="<?php echo esc_attr( coregas_section_classes( $sid ) ); ?> sec sec--<?php echo esc_attr( $bg ); ?>"<?php echo coregas_section_anchor( $sid ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="sec__inner">
		<?php coregas_section_head( $sid ); ?>
		<?php if ( $items ) : ?>
			<?php if ( 'strip' === $layout ) : ?>
				<?php
				$steps = array();
				foreach ( $items as $i => $item ) {
					$tag = '';
					if ( 'cg_service' === $item->post_type ) {
						$tag = coregas_meta( $item->ID, 'service_tag' );
					}
					$steps[] = array(
						'tag'      => $tag,
						'title'    => get_the_title( $item ),
						'text'     => has_excerpt( $item ) ? get_the_excerpt( $item ) : wp_trim_words( wp_strip_all_tags( $item->post_content ), 24 ),
						'image'    => get_post_thumbnail_id( $item ),
						'fallback' => coregas_placeholder_image( $item, $i ),
					);
				}
				coregas_render_strip( $steps );
				?>
			<?php elseif ( 'record' === $layout ) : ?>
				<?php foreach ( $items as $item ) : ?>
					<?php get_template_part( 'template-parts/record', null, array( 'post' => $item ) ); ?>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="rows">
					<?php
					foreach ( $items as $i => $item ) {
						get_template_part( 'template-parts/card', null, array( 'post' => $item, 'index' => $i ) );
					}
					?>
				</div>
			<?php endif; ?>
		<?php elseif ( current_user_can( 'edit_posts' ) ) : ?>
			<p class="empty-note"><?php esc_html_e( 'No items published yet. This note is only visible to logged-in editors.', 'coregas' ); ?></p>
		<?php endif; ?>
	</div>
	<?php coregas_section_links( $sid, in_array( $bg, array( 'dark', 'maroon' ), true ) ? 'light' : 'maroon' ); ?>
</section>
