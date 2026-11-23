<?php
/**
 * Server settings.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo;

/**
 * Secrets and server settings come from the environment (local, `.ddev/.env`) or from constants
 * in the server's wp-config.php (production). Never from the database or the repository.
 */
final class Settings {

	/**
	 * Reads a setting from the environment, then from a wp-config.php constant.
	 *
	 * @param string $name Setting name, e.g. EUTEAJUDO_SMTP_HOST.
	 */
	public static function get( string $name ): string {
		$value = getenv( $name );
		if ( is_string( $value ) && '' !== $value ) {
			return $value;
		}
		return defined( $name ) ? (string) constant( $name ) : '';
	}
}
