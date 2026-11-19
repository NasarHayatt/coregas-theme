<?php
/**
 * One item as an alternating feature row: image on one side, content on the other.
 * Args: post (WP_Post), index (int, decides which side the image is on).
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

$item  = $args['post'];
$index = isset( $args['index'] ) ? (int) $args['index'] : 0;
$type  = $item->post_type;

$tag = '';
switch ( $type ) {
	case 'cg_service':
		$tag = coregas_meta( $item->ID, 'service_tag' );
		break;
	case 'cg_industry':
		$tag = coregas_meta( $item->ID, 'industry_tag' );
		break;
	case 'cg_licence':
		$tag = coregas_meta( $item->ID, 'issuer' );
		break;
	case 'post':
		$tag = get_the_date( '', $item );
		break;
}

$image = get_the_post_thumbnail( $item, 'large', array( 'loading' => 'lazy', 'decoding' => 'async' ) );
if ( ! $image ) {
	$image = coregas_image( 0, 'large', coregas_placeholder_image( $item, $index ), array( 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) );
}
$text = has_excerpt( $item ) ? get_the_excerpt( $item ) : wp_trim_words( wp_strip_all_tags( $item->post_content ), 40 );
$link = get_permalink( $item );
?>
<article class="row<?php echo $index % 2 ? ' row--flip' : ''; ?>">
	<figure class="row__media">
		<a href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
	</figure>
	<div class="row__body">
		<?php if ( $tag ) : ?>
			<p class="mono row__tag"><?php echo esc_html( $tag ); ?></p>
		<?php endif; ?>
		<h3><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a></h3>
		<?php if ( $text ) : ?>
			<p class="row__text"><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>
		<?php if ( 'cg_licence' === $type ) : ?>
			<dl class="row__facts">
				<?php
				foreach ( array( 'licence_no' => coregas_label( 'licence_no' ), 'granted' => coregas_label( 'date_of_grant' ), 'activity' => coregas_label( 'activity' ) ) as $k => $label ) :
					$val = coregas_meta( $item->ID, $k );
					if ( ! $val ) {
						continue;
					}
					?>
					<div><dt class="mono"><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $val ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>
		<a class="row__more" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( coregas_label( 'read_more' ) ); ?> <span aria-hidden="true">→</span></a>
	</div>
</article>
