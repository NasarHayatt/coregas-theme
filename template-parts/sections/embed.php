<?php
/**
 * Section: Video (YouTube / Vimeo) or map (Google Maps embed address).
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

$sid     = (int) $args['id'];
$bg      = coregas_meta( $sid, 'sec_bg' );
$url     = (string) coregas_meta( $sid, 'emb_url' );
$ratio   = in_array( coregas_meta( $sid, 'emb_ratio' ), array( '16/9', '4/3', '21/9' ), true ) ? coregas_meta( $sid, 'emb_ratio' ) : '16/9';
$caption = coregas_meta( $sid, 'emb_caption' );

$html = '';
if ( $url ) {
	$host = (string) wp_parse_url( $url, PHP_URL_HOST );
	if ( false !== strpos( $host, 'google.' ) && false !== strpos( $url, '/maps' ) ) {
		$html = '<iframe src="' . esc_url( $url ) . '" title="' . esc_attr__( 'Map', 'coregas' ) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>';
	} else {
		$html = wp_oembed_get( $url ); // YouTube, Vimeo and other providers WordPress trusts.
	}
}
?>
<section class="<?php echo esc_attr( coregas_section_classes( $sid ) ); ?> sec sec--<?php echo esc_attr( $bg ); ?>"<?php echo coregas_section_anchor( $sid ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="sec__inner">
		<?php coregas_section_head( $sid ); ?>
		<?php if ( $html ) : ?>
			<figure class="embed" style="--ratio:<?php echo esc_attr( $ratio ); ?>">
				<div class="embed__frame"><?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- core oEmbed output or an escaped iframe. ?></div>
				<?php if ( $caption ) : ?>
					<figcaption><?php echo esc_html( $caption ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php elseif ( current_user_can( 'edit_posts' ) ) : ?>
			<p class="empty-note"><?php esc_html_e( 'Add a YouTube, Vimeo or Google Maps link to this section. This note is only visible to logged-in editors.', 'coregas' ); ?></p>
		<?php endif; ?>
	</div>
	<?php coregas_section_links( $sid, in_array( $bg, array( 'dark', 'maroon' ), true ) ? 'light' : 'maroon' ); ?>
</section>
