<?php
/**
 * Single item: news posts and every custom content type.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$pid  = get_the_ID();
	$type = get_post_type();
	$pto  = get_post_type_object( $type );

	$eyebrow = 'post' === $type ? get_the_date() : $pto->labels->singular_name;
	coregas_banner(
		array(
			'fallback' => coregas_current_banner_placeholder(),
			'eyebrow' => $eyebrow,
			'title'   => get_the_title(),
			'text'    => has_excerpt() ? get_the_excerpt() : '',
		)
	);
	?>
	<section class="sec sec--light">
		<div class="sec__inner item">
			<div class="item__main">
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="item__img"><?php the_post_thumbnail( 'large' ); ?></figure>
				<?php endif; ?>
				<div class="prose"><?php the_content(); ?></div>
			</div>

			<aside class="item__aside">
				<?php if ( 'cg_licence' === $type ) : ?>
					<dl class="facts-list">
						<?php
						$status = coregas_meta( $pid, 'status' );
						$rows   = array(
							coregas_label( 'licence_no' )    => coregas_meta( $pid, 'licence_no' ),
							coregas_label( 'issued_by' )     => coregas_meta( $pid, 'issuer' ),
							coregas_label( 'date_of_grant' ) => coregas_meta( $pid, 'granted' ),
							coregas_label( 'activity' )      => coregas_meta( $pid, 'activity' ),
							coregas_label( 'status' )        => $status ? ucfirst( $status ) : '',
						);
						foreach ( $rows as $label => $val ) :
							if ( ! $val ) {
								continue;
							}
							?>
							<div><dt class="mono"><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $val ); ?></dd></div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>

				<?php
				$archive = 'post' === $type ? get_permalink( (int) get_option( 'page_for_posts' ) ) : get_post_type_archive_link( $type );
				if ( $archive && ( 'post' !== $type || get_option( 'page_for_posts' ) ) ) :
					?>
					<a class="back-link" href="<?php echo esc_url( $archive ); ?>">← <?php echo esc_html( coregas_label( 'back_to_list', array( 'list' => strtolower( $pto->labels->name ) ) ) ); ?></a>
				<?php endif; ?>
			</aside>
		</div>
	</section>
	<?php
endwhile;

get_footer();
