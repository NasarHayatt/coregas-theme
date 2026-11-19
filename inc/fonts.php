<?php
/**
 * Font catalogue. Heading fonts carry the typographic tuning that keeps
 * every option visually balanced in the same layout.
 *
 * @package CoreGas
 */

defined( 'ABSPATH' ) || exit;

/**
 * Heading fonts. Keys are stored in Theme Settings.
 *
 * @return array
 */
function coregas_heading_fonts() {
	return array(
		'barlow'        => array(
			'label'  => 'Barlow (NVIDIA style)',
			'family' => 'Barlow',
			'google' => 'Barlow:wght@600;700;800',
			'vars'   => array( 'weight' => 700, 'hero' => 800, 'lh' => '1.0', 'track' => '-.015em', 'case' => 'none', 'scale' => '.62', 'scale-sm' => '.8' ),
		),
		'inter-tight'   => array(
			'label'  => 'Inter Tight (Apple style)',
			'family' => 'Inter Tight',
			'google' => 'Inter+Tight:wght@600;700;800',
			'vars'   => array( 'weight' => 700, 'hero' => 800, 'lh' => '1.0', 'track' => '-.035em', 'case' => 'none', 'scale' => '.6', 'scale-sm' => '.78' ),
		),
		'open-sans'     => array(
			'label'  => 'Open Sans (Microsoft style)',
			'family' => 'Open Sans',
			'google' => 'Open+Sans:wght@600;700;800',
			'vars'   => array( 'weight' => 700, 'hero' => 800, 'lh' => '1.0', 'track' => '-.025em', 'case' => 'none', 'scale' => '.58', 'scale-sm' => '.76' ),
		),
		'ibm-plex-sans' => array(
			'label'  => 'IBM Plex Sans (IBM)',
			'family' => 'IBM Plex Sans',
			'google' => 'IBM+Plex+Sans:wght@600;700',
			'vars'   => array( 'weight' => 700, 'hero' => 700, 'lh' => '1.0', 'track' => '-.025em', 'case' => 'none', 'scale' => '.6', 'scale-sm' => '.78' ),
		),
		'roboto'        => array(
			'label'  => 'Roboto (Google)',
			'family' => 'Roboto',
			'google' => 'Roboto:wght@700;900',
			'vars'   => array( 'weight' => 700, 'hero' => 900, 'lh' => '1.0', 'track' => '-.03em', 'case' => 'none', 'scale' => '.6', 'scale-sm' => '.78' ),
		),
		'jost'          => array(
			'label'  => 'Jost (Shell style)',
			'family' => 'Jost',
			'google' => 'Jost:wght@600;700',
			'vars'   => array( 'weight' => 700, 'hero' => 700, 'lh' => '1.0', 'track' => '-.02em', 'case' => 'none', 'scale' => '.62', 'scale-sm' => '.8' ),
		),
		'geist'         => array(
			'label'  => 'Geist (Vercel)',
			'family' => 'Geist',
			'google' => 'Geist:wght@600..800',
			'vars'   => array( 'weight' => 700, 'hero' => 800, 'lh' => '1.0', 'track' => '-.04em', 'case' => 'none', 'scale' => '.6', 'scale-sm' => '.78' ),
		),
		'montserrat'    => array(
			'label'  => 'Montserrat (Tesla / Gotham style)',
			'family' => 'Montserrat',
			'google' => 'Montserrat:wght@700;800',
			'vars'   => array( 'weight' => 700, 'hero' => 800, 'lh' => '1.0', 'track' => '-.025em', 'case' => 'none', 'scale' => '.56', 'scale-sm' => '.74' ),
		),
		'big-shoulders' => array(
			'label'  => 'Big Shoulders Display (condensed, uppercase)',
			'family' => 'Big Shoulders Display',
			'google' => 'Big+Shoulders+Display:wght@700;800;900',
			'vars'   => array( 'weight' => 800, 'hero' => 900, 'lh' => '.88', 'track' => '-.01em', 'case' => 'uppercase', 'scale' => '1', 'scale-sm' => '1' ),
		),
	);
}

/**
 * Body fonts.
 *
 * @return array
 */
function coregas_body_fonts() {
	return array(
		'geist'         => array( 'label' => 'Geist', 'family' => 'Geist', 'google' => 'Geist:wght@300..700' ),
		'inter'         => array( 'label' => 'Inter', 'family' => 'Inter', 'google' => 'Inter:wght@300..700' ),
		'ibm-plex-sans' => array( 'label' => 'IBM Plex Sans', 'family' => 'IBM Plex Sans', 'google' => 'IBM+Plex+Sans:wght@300;400;500;600' ),
		'open-sans'     => array( 'label' => 'Open Sans', 'family' => 'Open Sans', 'google' => 'Open+Sans:wght@300..700' ),
		'roboto'        => array( 'label' => 'Roboto', 'family' => 'Roboto', 'google' => 'Roboto:wght@300;400;500;700' ),
		'barlow'        => array( 'label' => 'Barlow', 'family' => 'Barlow', 'google' => 'Barlow:wght@300;400;500;600' ),
	);
}

/**
 * Select-field choices built from a catalogue.
 *
 * @param array $catalogue Font catalogue.
 * @return array
 */
function coregas_font_choices( $catalogue ) {
	return wp_list_pluck( $catalogue, 'label' );
}
