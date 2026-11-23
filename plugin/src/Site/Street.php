<?php
/**
 * The night street of the home page.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Site;

/**
 * A row of buildings in silhouette with dark windows and a single lit one. Built from fixed
 * numbers, so it is always the same street. Colors come from the theme presets.
 */
final class Street {

	private const WIDTH  = 1200;
	private const HEIGHT = 300;

	/**
	 * Buildings as [x, width, height].
	 */
	private const BUILDINGS = array(
		array( 0, 110, 170 ),
		array( 118, 150, 230 ),
		array( 276, 90, 140 ),
		array( 374, 170, 260 ),
		array( 552, 120, 190 ),
		array( 680, 160, 240 ),
		array( 848, 100, 160 ),
		array( 956, 140, 215 ),
		array( 1104, 96, 175 ),
	);

	/**
	 * Building index and window [column, row] that is lit.
	 */
	private const LIT = array( 5, 2, 2 );

	/**
	 * The street as inline SVG.
	 */
	public static function svg(): string {
		$out = '<svg viewBox="0 0 ' . self::WIDTH . ' ' . self::HEIGHT . '" preserveAspectRatio="xMidYMax slice" focusable="false">';
		$lit = '';
		foreach ( self::BUILDINGS as $index => [ $x, $width, $height ] ) {
			$top  = self::HEIGHT - $height;
			$out .= sprintf( '<rect class="eta-building" x="%d" y="%d" width="%d" height="%d" rx="4"/>', $x, $top, $width, $height + 4 );
			$cols = intdiv( $width - 20, 30 );
			$rows = intdiv( $height - 30, 40 );
			$left = $x + intdiv( $width - ( $cols * 30 - 12 ), 2 );
			for ( $row = 0; $row < $rows; $row++ ) {
				for ( $col = 0; $col < $cols; $col++ ) {
					$wx = $left + $col * 30;
					$wy = $top + 22 + $row * 40;
					if ( array( $index, $col, $row ) === self::LIT ) {
						$lit = sprintf( '<rect class="eta-street-lit" x="%d" y="%d" width="18" height="26" rx="3"/>', $wx, $wy );
						continue;
					}
					$out .= sprintf( '<rect class="eta-street-window" x="%d" y="%d" width="18" height="26" rx="3"/>', $wx, $wy );
				}
			}
		}
		return $out . $lit . '</svg>';
	}
}
