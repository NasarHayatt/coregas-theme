<?php
/**
 * Default page: banner, editor content, then any Page Sections assigned to the page.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$pid = get_the_ID();

	if ( '1' === coregas_meta( $pid, 'banner_show' ) ) {
		$title = coregas_meta( $pid, 'banner_title' );
		coregas_banner(
			array(
				'fallback' => coregas_current_banner_placeholder(),
				'eyebrow' => coregas_meta( $pid, 'banner_eyebrow' ),
				'title'   => $title ? $title : get_the_title(),
				'text'    => coregas_meta( $pid, 'banner_text' ),
				'image'   => coregas_meta( $pid, 'banner_image' ),
			)
		);
	} else {
		echo '<h1 class="sr">' . esc_html( get_the_title() ) . '</h1>';
	}

	if ( '' !== trim( get_the_content() ) ) :
		?>
		<section class="sec sec--light">
			<div class="sec__inner prose prose--narrow"><?php the_content(); ?></div>
		</section>
		<?php
	endif;

	coregas_render_sections( $pid );
endwhile;

get_footer();
