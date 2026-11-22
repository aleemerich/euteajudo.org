<?php
/**
 * Plugin bootstrap.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo;

/**
 * Wires the plugin modules into WordPress hooks.
 */
final class Plugin {

	/**
	 * Option that stores the schema version already applied to this site.
	 */
	public const SCHEMA_OPTION = 'eta_schema_version';

	/**
	 * Entry point, called on `plugins_loaded`.
	 */
	public static function boot(): void {
		add_action( 'init', array( self::class, 'load_textdomain' ) );

		Accounts\Aliases::register();
		Accounts\AdminAccess::register();
		Accounts\Google\GoogleAuth::register();
		Privacy\Avatars::register();
		Privacy\UserDirectory::register();

		// Migrations may translate strings, which WordPress only allows from init on.
		add_action( 'init', array( self::class, 'migrate' ), 5 );
	}

	/**
	 * Runs pending migrations.
	 */
	public static function migrate(): void {
		self::migrator()->run();
	}

	/**
	 * Loads translations shipped with the plugin.
	 */
	public static function load_textdomain(): void {
		load_plugin_textdomain( 'euteajudo', false, dirname( plugin_basename( PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Builds the migrator with the site's stored schema version.
	 *
	 * Deploys are file copies over FTPS, which never trigger the activation hook, so pending
	 * migrations run on load whenever the code version is ahead of the stored one.
	 */
	public static function migrator(): Migrator {
		return new Migrator(
			VERSION,
			static fn (): string => (string) get_option( self::SCHEMA_OPTION, '0.0.0' ),
			static function ( string $version ): void {
				update_option( self::SCHEMA_OPTION, $version, false );
			},
			Migrations::all()
		);
	}
}
