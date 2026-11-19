<?php
/**
 * Licence record panel: licence number (or "Licensed") large, details beside it. For dark / maroon sections.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

$item   = $args['post'];
$no     = coregas_meta( $item->ID, 'licence_no' );
$status = coregas_meta( $item->ID, 'status' );
$rows   = array(
	coregas_label( 'issued_by' )     => coregas_meta( $item->ID, 'issuer' ),
	coregas_label( 'date_of_grant' ) => coregas_meta( $item->ID, 'granted' ),
	coregas_label( 'activity' )      => coregas_meta( $item->ID, 'activity' ),
	coregas_label( 'licensee' )      => coregas_setting( 'company_name' ),
);
?>
<article class="record">
	<div class="record__main">
		<p class="mono record__kicker"><?php echo esc_html( get_the_title( $item ) ); ?></p>
		<p class="record__no"><?php echo esc_html( $no ? $no : coregas_label( 'licensed' ) ); ?></p>
		<?php if ( $status ) : ?>
			<span class="record__status record__status--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( ucfirst( $status ) ); ?></span>
		<?php endif; ?>
	</div>
	<dl class="record__grid">
		<?php
		foreach ( $rows as $label => $val ) :
			if ( ! $val ) {
				continue;
			}
			?>
			<div><dt class="mono"><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $val ); ?></dd></div>
		<?php endforeach; ?>
	</dl>
</article>
