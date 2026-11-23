<?php
/**
 * Roles.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts;

/**
 * Friend and Agent roles. Capabilities grow with each delivery (help requests in E2, API in E3).
 */
final class Roles {

	public const FRIEND = 'eta_friend';
	public const AGENT  = 'eta_agent';

	/**
	 * Creates the roles and makes new accounts Friends. Idempotent.
	 */
	public static function install(): void {
		// Role names are stored untranslated, as WordPress does with its own roles.
		if ( null === get_role( self::FRIEND ) ) {
			add_role( self::FRIEND, 'Friend', array( 'read' => true ) );
		}
		if ( null === get_role( self::AGENT ) ) {
			add_role( self::AGENT, 'Agent', array( 'read' => true ) );
		}
		update_option( 'default_role', self::FRIEND );
	}
}
