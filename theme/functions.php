<?php
/**
 * EuTeAjudo theme bootstrap.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Theme;

defined( 'ABSPATH' ) || exit;

const VERSION = '0.2.0';

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
	// The .mo is named euteajudo-theme-<locale>.mo (as wp i18n make-mo writes it), while
	// load_theme_textdomain() looks for <locale>.mo, so it is loaded by its full name.
	load_textdomain( 'euteajudo-theme', get_template_directory() . '/languages/euteajudo-theme-' . determine_locale() . '.mo' );
	add_editor_style( array( 'assets/css/color-schemes.css', 'assets/css/theme.css' ) );
}
add_action( 'after_setup_theme', __NAMESPACE__ . '\setup' );

/**
 * Applies the visitor's saved Night/Day choice before the page paints, to avoid a flash.
 *
 * The choice is stored only in the browser (localStorage key "eta-scheme"), never on the server.
 * Without a choice, color-schemes.css follows the device preference, then Night.
 */
function print_scheme_script(): void {
	echo "<script>try{var s=localStorage.getItem('eta-scheme');if(s==='night'||s==='day'){document.documentElement.setAttribute('data-eta-scheme',s);}}catch(e){}</script>\n";
}
add_action( 'wp_head', __NAMESPACE__ . '\\print_scheme_script', 0 );

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

/**
 * WordPress caches the list of pattern files per theme. FTP deploys replace files without
 * telling WordPress, so new patterns would stay invisible: the cache is cleared once per
 * theme version.
 */
function refresh_pattern_cache(): void {
	if ( get_option( 'euteajudo_theme_version' ) !== VERSION ) {
		wp_get_theme()->delete_pattern_cache();
		update_option( 'euteajudo_theme_version', VERSION );
	}
}
add_action( 'init', __NAMESPACE__ . '\refresh_pattern_cache', 1 );

/**
 * Pattern category and the "link" style for buttons (a secondary action that reads as a link).
 */
function register_patterns_and_styles(): void {
	register_block_pattern_category( 'euteajudo', array( 'label' => __( 'EuTeAjudo', 'euteajudo-theme' ) ) );
	register_block_style(
		'core/button',
		array(
			'name'  => 'link',
			'label' => __( 'Link', 'euteajudo-theme' ),
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\register_patterns_and_styles' );

/**
 * Creates the Home page once, from the euteajudo/home pattern, and makes it the static front
 * page. After that it is an ordinary page, edited in Pages. Runs on init (FTP deploys and an
 * already active theme never fire the activation hooks) and respects a front page the
 * administrator already chose.
 */
function ensure_home_page(): void {
	$stored = (int) get_option( 'euteajudo_home_page', 0 );
	if ( $stored > 0 && null !== get_post( $stored ) ) {
		return;
	}
	if ( 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' ) > 0 ) {
		update_option( 'euteajudo_home_page', (int) get_option( 'page_on_front' ) );
		return;
	}
	if ( ! add_option( 'euteajudo_home_page_lock', time(), '', false ) ) {
		// Another request is creating it; a lock older than a minute was left by a failure.
		if ( time() - (int) get_option( 'euteajudo_home_page_lock' ) > MINUTE_IN_SECONDS ) {
			delete_option( 'euteajudo_home_page_lock' );
		}
		return;
	}
	$pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered( 'euteajudo/home' );
	$content = is_array( $pattern ) && is_string( $pattern['content'] ?? null ) ? $pattern['content'] : '';
	if ( '' !== $content ) {
		$switched = switch_to_locale( get_locale() );
		kses_remove_filters(); // Trusted markup from the theme; visitors' requests would filter it.
		$page_id = wp_insert_post(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'post_title'     => __( 'Home', 'euteajudo-theme' ),
				'post_name'      => 'inicio',
				'post_author'    => (int) ( get_users(
					array(
						'role'   => 'administrator',
						'number' => 1,
						'fields' => 'ID',
					)
				)[0] ?? 0 ),
				'post_content'   => $content,
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			)
		);
		kses_init();
		if ( $switched ) {
			restore_previous_locale();
		}
		if ( is_int( $page_id ) && $page_id > 0 ) {
			update_option( 'euteajudo_home_page', $page_id );
			update_option( 'page_on_front', $page_id );
			update_option( 'show_on_front', 'page' );
		}
	}
	delete_option( 'euteajudo_home_page_lock' );
}
add_action( 'init', __NAMESPACE__ . '\ensure_home_page', 20 );
