<?php
/**
 * Site footer: brand column (logo, description, licence, button, contact, social),
 * link columns from the footer menus, then copyright and legal links.
 * Everything is set in Theme Settings → Footer and Appearance → Menus.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

$columns = array();
foreach ( array( 'footer_1', 'footer_2', 'footer_3', 'footer_4' ) as $loc ) {
	$links = coregas_menu_links( $loc );
	if ( $links ) {
		$columns[] = array( 'title' => coregas_menu_name( $loc ), 'links' => $links );
	}
}
$social     = '1' === coregas_setting( 'footer_show_social' ) ? coregas_social_links() : '';
$about      = trim( (string) coregas_setting( 'footer_about' ) );
$badge      = trim( (string) coregas_setting( 'footer_badge' ) );
$badge_url  = coregas_link( coregas_setting( 'footer_badge_url' ) );
$btn_label  = trim( (string) coregas_setting( 'footer_btn_label' ) );
$btn_url    = coregas_link( coregas_setting( 'footer_btn_url' ) );
$phone      = '1' === coregas_setting( 'footer_show_contact' ) ? coregas_setting( 'contact_phone' ) : '';
$email      = '1' === coregas_setting( 'footer_show_contact' ) ? coregas_setting( 'contact_email' ) : '';
$legal      = coregas_menu_links( 'legal' );
$copyright  = str_replace( '{year}', wp_date( 'Y' ), (string) coregas_setting( 'footer_copyright' ) );
?>
</main>

<footer class="ftr">
	<div class="ftr__top">
		<div class="ftr__intro">
			<?php if ( '1' === coregas_setting( 'footer_show_logo' ) ) : ?>
				<a class="ftr__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( coregas_setting( 'company_name' ) ); ?>"><?php echo coregas_logo( 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<?php endif; ?>
			<?php if ( $about ) : ?>
				<p class="ftr__about"><?php echo esc_html( $about ); ?></p>
			<?php endif; ?>
			<?php if ( $badge ) : ?>
				<?php if ( $badge_url ) : ?>
					<a class="ftr__badge mono" href="<?php echo esc_url( $badge_url ); ?>"><span class="ftr__dot" aria-hidden="true"></span><?php echo esc_html( $badge ); ?></a>
				<?php else : ?>
					<p class="ftr__badge mono"><span class="ftr__dot" aria-hidden="true"></span><?php echo esc_html( $badge ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
			<?php if ( coregas_profile_enabled() ) : ?>
				<a class="ftr__profile" href="<?php echo esc_url( home_url( '/company-profile/' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( coregas_setting( 'profile_label' ) ); ?> <span aria-hidden="true">↓</span></a>
			<?php endif; ?>
			<?php if ( $phone || $email ) : ?>
				<p class="ftr__contact">
					<?php if ( $phone ) : ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a>
					<?php endif; ?>
				</p>
			<?php endif; ?>
			<?php if ( $btn_label && $btn_url ) : ?>
				<a class="pill pill--flame ftr__btn" href="<?php echo esc_url( $btn_url ); ?>"><span><?php echo esc_html( $btn_label ); ?></span><i aria-hidden="true">↗</i></a>
			<?php endif; ?>
			<?php if ( $social ) : ?>
				<div class="ftr__social"><?php echo $social; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php endif; ?>
		</div>

		<?php if ( $columns ) : ?>
			<div class="ftr__cols">
				<?php foreach ( $columns as $col ) : ?>
					<nav aria-label="<?php echo esc_attr( $col['title'] ); ?>">
						<h4><?php echo esc_html( $col['title'] ); ?></h4>
						<?php echo $col['links']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</nav>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="ftr__base">
		<p class="ftr__copy"><?php echo esc_html( $copyright ); ?></p>
		<?php if ( $legal ) : ?>
			<nav class="ftr__legal" aria-label="<?php esc_attr_e( 'Legal', 'coregas' ); ?>"><?php echo $legal; // phpcs:ignore WordPress.Security.EscapeOutput ?></nav>
		<?php endif; ?>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
