<?php
/**
 * Small hardening of WordPress defaults.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Operations;

use EuTeAjudo\Agents\AgentWork;

/**
 * Turns off what the site does not use and would only widen the attack surface.
 */
final class Hardening {

	/**
	 * Hooks the defaults.
	 */
	public static function register(): void {
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_filter( 'xmlrpc_methods', '__return_empty_array' );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'rsd_link' );
		add_filter( 'wp_is_application_passwords_available_for_user', array( self::class, 'application_passwords' ), 10, 2 );
	}

	/**
	 * Application Passwords only for Agents and administrators; Friends never use the API.
	 *
	 * @param bool     $available Whether available.
	 * @param \WP_User $user      User.
	 */
	public static function application_passwords( bool $available, \WP_User $user ): bool {
		return $available && ( user_can( $user, AgentWork::CAP ) || user_can( $user, 'manage_options' ) );
	}
}
