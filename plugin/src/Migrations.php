<?php
/**
 * Migration list.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo;

/**
 * Registry of schema migrations, keyed by the plugin version that introduces each one.
 * Every migration must be idempotent: it may run again if a deploy is repeated.
 */
final class Migrations {

	/**
	 * All migrations.
	 *
	 * @return array<string, callable(): void>
	 */
	public static function all(): array {
		return array(
			'0.2.0' => static function (): void {
				Accounts\Roles::install();
				update_option( 'show_avatars', 0 );
				Accounts\Aliases::backfill();
			},
		);
	}
}
