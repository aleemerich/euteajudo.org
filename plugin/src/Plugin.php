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
	 * Option used as a lock while migrations run. add_option() is an atomic insert, so only one
	 * request gets it; a lock older than LOCK_TTL seconds is considered abandoned.
	 */
	public const LOCK_OPTION = 'eta_migration_lock';
	public const LOCK_TTL    = 300;

	/**
	 * Entry point, called on `plugins_loaded`.
	 */
	public static function boot(): void {
		add_action( 'init', array( self::class, 'load_textdomain' ) );

		Accounts\Aliases::register();
		Accounts\AdminAccess::register();
		Accounts\Google\GoogleAuth::register();
		Accounts\Forms\Dispatcher::register();
		Accounts\Blocks::register();
		Admin\UsersList::register();
		Privacy\Avatars::register();
		Privacy\UserDirectory::register();
		Privacy\Comments::register();
		HelpRequests\HelpRequestType::register();
		HelpRequests\Replies::register();
		HelpRequests\Conversations::register();
		SupportServices\SupportServiceType::register();
		Agents\AgentWork::register();
		Agents\AgentApi::register();
		Agents\AgentCli::register();
		HelpRequests\EditAudit::register();
		HelpRequests\Notifications::register();
		HelpRequests\Schedules::register();
		Admin\HelpRequestsAdmin::register();
		Admin\AgentStatus::register();
		Operations\Mail::register();
		Operations\Hardening::register();
		Cli::register();

		// Migrations may translate strings, which WordPress only allows from init on.
		add_action( 'init', array( self::class, 'migrate' ), 5 );
	}

	/**
	 * Runs pending migrations.
	 */
	public static function migrate(): void {
		if ( version_compare( (string) get_option( self::SCHEMA_OPTION, '0.0.0' ), VERSION, '>=' ) ) {
			return;
		}
		if ( ! self::acquire_lock() ) {
			return;
		}
		try {
			// Another request may have finished migrating after this one read the version at
			// startup; drop the cached value so the migrator reads the database.
			wp_cache_delete( self::SCHEMA_OPTION, 'options' );
			wp_cache_delete( 'alloptions', 'options' );
			wp_cache_delete( 'notoptions', 'options' );
			wp_cache_delete( Accounts\Pages::OPTION, 'options' );
			self::migrator()->run();
		} finally {
			delete_option( self::LOCK_OPTION );
		}
	}

	/**
	 * Takes the migration lock, replacing an abandoned one.
	 */
	private static function acquire_lock(): bool {
		if ( add_option( self::LOCK_OPTION, time(), '', false ) ) {
			return true;
		}
		$since = (int) get_option( self::LOCK_OPTION );
		if ( $since > time() - self::LOCK_TTL ) {
			return false;
		}
		delete_option( self::LOCK_OPTION );
		return add_option( self::LOCK_OPTION, time(), '', false );
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
