<?php
/**
 * Title: Lit window
 * Slug: euteajudo/lit-window
 * Categories: euteajudo
 * Inserter: no
 * Description: The identity's illustration: a facade with one lit window on phones, a night street across the page on wide screens. The lit window turns on once, the only automatic animation of the site.
 *
 * @package EuTeAjudo
 */

// Buildings as [x, width, height] in a 1200 x 300 street; one window is lit.
$euteajudo_buildings = array( array( 0, 110, 170 ), array( 118, 150, 230 ), array( 276, 90, 140 ), array( 374, 170, 260 ), array( 552, 120, 190 ), array( 680, 160, 240 ), array( 848, 100, 160 ), array( 956, 140, 215 ), array( 1104, 96, 175 ) );
$euteajudo_lit       = array( 5, 2, 2 );
$euteajudo_svg       = '<svg viewBox="0 0 1200 300" preserveAspectRatio="xMidYMax slice" focusable="false">';
$euteajudo_glow      = '';
foreach ( $euteajudo_buildings as $euteajudo_index => [ $euteajudo_x, $euteajudo_w, $euteajudo_h ] ) {
	$euteajudo_top  = 300 - $euteajudo_h;
	$euteajudo_svg .= sprintf( '<rect class="eta-building" x="%d" y="%d" width="%d" height="%d" rx="4"/>', $euteajudo_x, $euteajudo_top, $euteajudo_w, $euteajudo_h + 4 );
	$euteajudo_cols = intdiv( $euteajudo_w - 20, 30 );
	$euteajudo_left = $euteajudo_x + intdiv( $euteajudo_w - ( $euteajudo_cols * 30 - 12 ), 2 );
	$euteajudo_rows = intdiv( $euteajudo_h - 30, 40 );
	for ( $euteajudo_row = 0; $euteajudo_row < $euteajudo_rows; $euteajudo_row++ ) {
		for ( $euteajudo_col = 0; $euteajudo_col < $euteajudo_cols; $euteajudo_col++ ) {
			$euteajudo_rect = sprintf( 'x="%d" y="%d" width="18" height="26" rx="3"', $euteajudo_left + $euteajudo_col * 30, $euteajudo_top + 22 + $euteajudo_row * 40 );
			if ( array( $euteajudo_index, $euteajudo_col, $euteajudo_row ) === $euteajudo_lit ) {
				$euteajudo_glow = '<rect class="eta-street-lit" ' . $euteajudo_rect . '/>';
				continue;
			}
			$euteajudo_svg .= '<rect class="eta-street-window" ' . $euteajudo_rect . '/>';
		}
	}
}
$euteajudo_svg .= $euteajudo_glow . '</svg>';
?>
<!-- wp:html -->
<div class="eta-lit-window" aria-hidden="true">
<div class="eta-facade"><?php echo str_repeat( '<span class="eta-win"></span>', 6 ) . '<span class="eta-win is-lit"></span>' . str_repeat( '<span class="eta-win"></span>', 5 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static markup. ?></div>
<div class="eta-street"><?php echo $euteajudo_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built from numbers above. ?></div>
</div>
<!-- /wp:html -->
