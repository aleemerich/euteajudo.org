<?php
/**
 * EuTeAjudo theme bootstrap.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Theme;

defined( 'ABSPATH' ) || exit;

const VERSION = '0.1.0';

/**
 * Loads the Night/Day color scheme variables and the component styles.
 */
function enqueue_color_schemes(): void {
	wp_enqueue_style(
		'euteajudo-color-schemes',
		get_theme_file_uri( 'assets/css/color-schemes.css' ),
		array(),
		VERSION
	);
	wp_enqueue_style(
		'euteajudo-theme',
		get_theme_file_uri( 'assets/css/theme.css' ),
		array( 'euteajudo-color-schemes' ),
		VERSION
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_color_schemes' );

/**
 * Adds theme supports and editor styles.
 */
function setup(): void {
	load_theme_textdomain( 'euteajudo', get_template_directory() . '/languages' );
	add_editor_style( array( 'assets/css/color-schemes.css', 'assets/css/theme.css' ) );
}
add_action( 'after_setup_theme', __NAMESPACE__ . '\setup' );

/**
 * Prints the favicon. Served from the theme, never from a third party.
 */
function print_favicon(): void {
	printf(
		'<link rel="icon" href="%s" type="image/svg+xml">' . "\n",
		esc_url( get_theme_file_uri( 'assets/images/favicon.svg' ) )
	);
}
add_action( 'wp_head', __NAMESPACE__ . '\print_favicon' );
