<?php
/**
 * Local-only WordPress constants, loaded through auto_prepend_file (see .ddev/php/hosting.ini).
 * Web requests only: CLI tools (PHPUnit, WP-CLI) define their own constants.
 */
if ( PHP_SAPI === 'cli' ) {
	return;
}
defined( 'WP_DEBUG_LOG' ) || define( 'WP_DEBUG_LOG', true );
defined( 'WP_DEBUG_DISPLAY' ) || define( 'WP_DEBUG_DISPLAY', false );
defined( 'SCRIPT_DEBUG' ) || define( 'SCRIPT_DEBUG', true );
