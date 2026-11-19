<?php
/**
 * Alternating image/text rows for the main query, with pagination.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="sec sec--light">
	<div class="sec__inner">
		<?php if ( have_posts() ) : ?>
			<div class="rows">
				<?php
				$i = 0;
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/card', null, array( 'post' => get_post(), 'index' => $i++ ) );
				}
				?>
			</div>
			<div class="pager">
				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 1,
						'prev_text' => '←',
						'next_text' => '→',
					)
				);
				?>
			</div>
		<?php else : ?>
			<p class="empty-note"><?php echo esc_html( coregas_label( 'empty_list' ) ); ?></p>
		<?php endif; ?>
	</div>
</section>
