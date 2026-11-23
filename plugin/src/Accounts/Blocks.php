<?php
/**
 * Account blocks.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts;

use const EuTeAjudo\PLUGIN_FILE;
use const EuTeAjudo\VERSION;

/**
 * Server-rendered blocks for the account pages and the header link. No build step: the editor
 * shows a server-side preview through a small script.
 */
final class Blocks {

	public const NAMES = array( 'sign-in', 'sign-up', 'password-request', 'password-reset', 'email-confirmation', 'account-link', 'account' );

	/**
	 * Hooks block registration.
	 */
	public static function register(): void {
		add_action( 'init', array( self::class, 'register_blocks' ) );
	}

	/**
	 * Registers assets and blocks.
	 */
	public static function register_blocks(): void {
		$base = plugin_dir_url( PLUGIN_FILE );
		wp_register_style( 'euteajudo-accounts', $base . 'assets/css/accounts.css', array(), VERSION );
		wp_register_script( 'euteajudo-accounts', $base . 'assets/js/accounts.js', array(), VERSION, array( 'strategy' => 'defer' ) );
		wp_register_script( 'euteajudo-blocks-editor', $base . 'assets/js/blocks-editor.js', array( 'wp-blocks', 'wp-element', 'wp-server-side-render' ), VERSION, true );
		wp_add_inline_script(
			'euteajudo-blocks-editor',
			'window.euteajudoBlocks = ' . wp_json_encode( array_map( static fn ( string $name ): string => 'euteajudo/' . $name, self::NAMES ) ) . ';',
			'before'
		);
		$dir = dirname( PLUGIN_FILE ) . '/blocks/';
		foreach ( self::NAMES as $name ) {
			register_block_type( $dir . $name );
		}
	}
}
