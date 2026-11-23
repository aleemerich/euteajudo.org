<?php
/**
 * WP-CLI commands for Agent accounts.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Agents;

use EuTeAjudo\Accounts\Roles;

/**
 * `wp euteajudo agent create|list`.
 */
final class AgentCli {

	/**
	 * Registers the command when WP-CLI is running.
	 */
	public static function register(): void {
		if ( defined( 'WP_CLI' ) && \WP_CLI ) {
			\WP_CLI::add_command( 'euteajudo agent', self::class );
		}
	}

	/**
	 * Creates an Agent account and prints its Application Password once.
	 *
	 * The login becomes the account's alias. Store the password in the Agent's .env; it cannot
	 * be shown again (create a new one in wp-admin, Users, if it is lost).
	 *
	 * ## EXAMPLES
	 *
	 *     wp euteajudo agent create
	 *
	 * @param string[]              $args       Positional arguments.
	 * @param array<string, string> $assoc_args Options.
	 */
	public function create( array $args, array $assoc_args ): void {
		unset( $args, $assoc_args );
		$user_id = wp_insert_user(
			array(
				'user_login' => 'agent' . strtolower( wp_generate_password( 10, false ) ),
				'user_email' => 'agent-' . strtolower( wp_generate_password( 10, false ) ) . '@agents.euteajudo.invalid',
				'user_pass'  => wp_generate_password( 32 ),
				'role'       => Roles::AGENT,
			)
		);
		if ( is_wp_error( $user_id ) ) {
			\WP_CLI::error( $user_id->get_error_message() );
			return;
		}
		$created = \WP_Application_Passwords::create_new_application_password( $user_id, array( 'name' => 'agent' ) );
		if ( is_wp_error( $created ) ) {
			\WP_CLI::error( $created->get_error_message() );
			return;
		}
		$user = get_userdata( $user_id );
		\WP_CLI::log( 'EUTEAJUDO_API_USER=' . ( $user ? $user->user_login : '' ) );
		\WP_CLI::log( 'EUTEAJUDO_API_PASSWORD=' . $created[0] );
		\WP_CLI::log( 'EUTEAJUDO_API_URL=' . rest_url( AgentApi::NAMESPACE ) );
		\WP_CLI::success( 'Agent created. Copy the password now; it will not be shown again.' );
	}

	/**
	 * Lists Agent accounts and when each last called the API.
	 *
	 * @param string[]              $args       Positional arguments.
	 * @param array<string, string> $assoc_args Options.
	 *
	 * @subcommand list
	 */
	public function list_( array $args, array $assoc_args ): void {
		unset( $args, $assoc_args );
		$rows = array();
		foreach ( get_users( array( 'role' => Roles::AGENT ) ) as $user ) {
			$seen   = AgentWork::last_seen( $user->ID );
			$rows[] = array(
				'login'     => $user->user_login,
				'last_seen' => $seen > 0 ? gmdate( 'Y-m-d H:i:s', $seen ) . ' UTC' : 'never',
			);
		}
		\WP_CLI\Utils\format_items( 'table', $rows, array( 'login', 'last_seen' ) );
	}
}
