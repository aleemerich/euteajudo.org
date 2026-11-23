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
			'0.2.0'  => static function (): void {
				Accounts\Roles::install();
				update_option( 'show_avatars', 0 );
				Accounts\Aliases::backfill();
			},
			'0.3.0'  => static function (): void {
				add_option( Accounts\AliasRange::OPTION, Accounts\AliasRange::default()->to_option(), '', false );
			},
			'0.4.0'  => static function (): void {
				// Labels stored before plugin and theme had separate text domains may be untranslated.
				Accounts\Aliases::refresh_all();
			},
			'0.5.0'  => static function (): void {
				// Logins of Friends and Agents become their alias ("amigo-48213").
				Accounts\Aliases::refresh_all();
			},
			'0.6.0'  => static function (): void {
				Accounts\Pages::install();
				// Accounts that already exist were created before email confirmation existed.
				foreach ( get_users( array( 'fields' => 'ID' ) ) as $user_id ) {
					Accounts\EmailConfirmation::mark_confirmed( (int) $user_id );
				}
			},
			'0.7.0'  => static function (): void {
				Accounts\Pages::install(); // Adds "My account".
			},
			'0.8.0'  => static function (): void {
				HelpRequests\TriageTerms::install();
				$admin = get_role( 'administrator' );
				if ( null !== $admin ) {
					foreach ( HelpRequests\HelpRequestType::ADMIN_CAPS as $cap ) {
						$admin->add_cap( $cap );
					}
				}
			},
			'0.9.0'  => static function (): void {
				Accounts\Pages::install(); // Adds "Write" and "My requests".
				HelpRequests\Conversations::add_rule();
				flush_rewrite_rules( false );
			},
			'0.10.0' => static function (): void {
				self::agent_capabilities();
				Agents\AgentAliases::install();
				SupportServices\SupportServices::install();
			},
		);
	}

	/**
	 * Agents work through the API; administrators manage support services. Idempotent.
	 */
	public static function agent_capabilities(): void {
		get_role( Accounts\Roles::AGENT )?->add_cap( Agents\AgentWork::CAP );
		$admin = get_role( 'administrator' );
		if ( null !== $admin ) {
			foreach ( SupportServices\SupportServiceType::ADMIN_CAPS as $cap ) {
				$admin->add_cap( $cap );
			}
		}
	}
}
