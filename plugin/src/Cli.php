<?php
/**
 * WP-CLI commands for development and operation.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo;

use EuTeAjudo\Accounts\EmailConfirmation;
use EuTeAjudo\Accounts\Roles;
use EuTeAjudo\HelpRequests\HelpRequests;
use EuTeAjudo\HelpRequests\HelpRequestType;
use EuTeAjudo\HelpRequests\Replies;

/**
 * `wp euteajudo ...`: seed fictitious data and reply to requests from the command line.
 * Seeded records carry a marker meta so `seed --clean` removes exactly them.
 */
final class Cli {

	public const SEED_META = '_eta_seed';

	private const SAMPLES = array(
		array( 'Não consigo dormir desde que perdi o emprego', "Faz três semanas que fui demitido. Durante o dia eu até disfarço: mando currículo, arrumo a casa.\n\nMas quando deito a cabeça não para. Acordo às 3h e não durmo mais." ),
		array( 'Briga com minha irmã por causa da casa da nossa mãe', 'Desde que minha mãe faleceu, eu e minha irmã não nos falamos direito. Ela quer vender a casa e eu não consigo pensar nisso agora.' ),
		array( 'Ansiedade antes das provas', 'Tenho prova na semana que vem e travo toda vez que sento para estudar. Meu coração dispara e eu desisto.' ),
		array( 'Me sinto sozinho na cidade nova', 'Mudei para outra cidade por causa do trabalho e não conheço ninguém. Os fins de semana são os piores.' ),
	);

	/**
	 * Registers the command when WP-CLI is running.
	 */
	public static function register(): void {
		if ( defined( 'WP_CLI' ) && \WP_CLI ) {
			\WP_CLI::add_command( 'euteajudo', self::class );
		}
	}

	/**
	 * Creates fictitious Friends with help requests and replies, for local testing.
	 *
	 * ## OPTIONS
	 *
	 * [--friends=<count>]
	 * : Number of Friends to create.
	 * ---
	 * default: 3
	 * ---
	 *
	 * [--clean]
	 * : Remove every seeded Friend and request instead.
	 *
	 * ## EXAMPLES
	 *
	 *     wp euteajudo seed --friends=5
	 *     wp euteajudo seed --clean
	 *
	 * @param string[]              $args       Positional arguments.
	 * @param array<string, string> $assoc_args Options.
	 */
	public function seed( array $args, array $assoc_args ): void {
		unset( $args );
		if ( isset( $assoc_args['clean'] ) ) {
			$this->clean();
			return;
		}
		$admin = $this->first_admin();
		$count = max( 1, (int) ( $assoc_args['friends'] ?? 3 ) );
		for ( $i = 0; $i < $count; $i++ ) {
			$user_id = wp_insert_user(
				array(
					'user_login' => 'seed' . strtolower( wp_generate_password( 10, false ) ),
					'user_email' => 'seed-' . strtolower( wp_generate_password( 8, false ) ) . '@example.org',
					'user_pass'  => 'seed password 123',
					'role'       => Roles::FRIEND,
				)
			);
			if ( is_wp_error( $user_id ) ) {
				\WP_CLI::warning( $user_id->get_error_message() );
				continue;
			}
			update_user_meta( $user_id, self::SEED_META, 1 );
			EmailConfirmation::mark_confirmed( $user_id );
			$user = get_userdata( $user_id );
			\WP_CLI::log( sprintf( 'Friend %s (%s, password "seed password 123")', $user ? $user->display_name : $user_id, $user ? $user->user_email : '' ) );

			foreach ( array_slice( self::SAMPLES, $i % 2, 2 ) as $n => [ $title, $body ] ) {
				$request = HelpRequests::create( $user_id, $title, $body );
				if ( is_wp_error( $request ) ) {
					continue;
				}
				update_post_meta( $request, self::SEED_META, 1 );
				if ( 0 === $n && $admin > 0 ) {
					Replies::add( $request, $admin, "Oi. Obrigado por contar isso com tanto detalhe.\n\nO que mais pesa para você agora?" );
				}
				\WP_CLI::log( '  request: ' . ( get_post( $request )->post_name ?? '' ) );
			}
		}
		\WP_CLI::success( 'Seeded.' );
	}

	/**
	 * Adds a reply to a help request, as an administrator (or another user).
	 *
	 * ## OPTIONS
	 *
	 * <code>
	 * : Request code (the last part of its address).
	 *
	 * <text>
	 * : Reply text.
	 *
	 * [--user=<id>]
	 * : Who replies. Defaults to the first administrator.
	 *
	 * @param string[]              $args       Positional arguments.
	 * @param array<string, string> $assoc_args Options.
	 */
	public function reply( array $args, array $assoc_args ): void {
		$request = HelpRequests::find_by_code( $args[0] ?? '' );
		if ( null === $request ) {
			\WP_CLI::error( 'Request not found.' );
			return;
		}
		$result = Replies::add( $request->ID, (int) ( $assoc_args['user'] ?? $this->first_admin() ), $args[1] ?? '' );
		if ( is_wp_error( $result ) ) {
			\WP_CLI::error( (string) $result->get_error_code() );
			return;
		}
		\WP_CLI::success( 'Reply ' . $result . ' added.' );
	}

	/**
	 * Moves a request and its replies into the past, to try the scheduled jobs by hand.
	 *
	 * ## OPTIONS
	 *
	 * <code>
	 * : Request code (the last part of its address).
	 *
	 * [--days=<days>]
	 * : How many days back.
	 * ---
	 * default: 0
	 * ---
	 *
	 * [--hours=<hours>]
	 * : How many hours back, added to the days.
	 * ---
	 * default: 0
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp euteajudo age abc123def456 --days=31
	 *     wp cron event run euteajudo_close_inactive
	 *
	 * @param string[]              $args       Positional arguments.
	 * @param array<string, string> $assoc_args Options.
	 */
	public function age( array $args, array $assoc_args ): void {
		$request = HelpRequests::find_by_code( $args[0] ?? '' );
		if ( null === $request ) {
			\WP_CLI::error( 'Request not found.' );
			return;
		}
		$seconds = (int) ( $assoc_args['days'] ?? 0 ) * DAY_IN_SECONDS + (int) ( $assoc_args['hours'] ?? 0 ) * HOUR_IN_SECONDS;
		$gmt     = gmdate( 'Y-m-d H:i:s', time() - $seconds );
		$local   = get_date_from_gmt( $gmt );
		global $wpdb;
		// phpcs:disable WordPress.DB.DirectDatabaseQuery -- Development command; caches are cleared below.
		$wpdb->update(
			$wpdb->posts,
			array(
				'post_date_gmt' => $gmt,
				'post_date'     => $local,
			),
			array( 'ID' => $request->ID )
		);
		$wpdb->update(
			$wpdb->comments,
			array(
				'comment_date_gmt' => $gmt,
				'comment_date'     => $local,
			),
			array( 'comment_post_ID' => $request->ID )
		);
		$replies = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_post_ID = %d", $request->ID ) );
		// phpcs:enable
		clean_post_cache( $request->ID );
		clean_comment_cache( array_map( 'intval', $replies ) );
		update_post_meta( $request->ID, HelpRequests::AUTHOR_ACTIVE_META, time() - $seconds );
		delete_post_meta( $request->ID, \EuTeAjudo\HelpRequests\Schedules::LATE_ALERTED_META );
		\WP_CLI::success( sprintf( 'Request %s now dates from %s UTC.', $request->post_name, $gmt ) );
	}

	/**
	 * Removes seeded data.
	 */
	private function clean(): void {
		$requests = get_posts(
			array(
				'post_type'   => HelpRequestType::POST_TYPE,
				'post_status' => HelpRequestType::STATUSES,
				'numberposts' => -1,
				'meta_key'    => self::SEED_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Development command.
				'fields'      => 'ids',
			)
		);
		foreach ( $requests as $id ) {
			wp_delete_post( (int) $id, true );
		}
		require_once ABSPATH . 'wp-admin/includes/user.php';
		$users = get_users(
			array(
				'meta_key' => self::SEED_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Development command.
				'fields'   => 'ID',
			)
		);
		foreach ( $users as $id ) {
			wp_delete_user( (int) $id );
		}
		\WP_CLI::success( sprintf( 'Removed %d requests and %d Friends.', count( $requests ), count( $users ) ) );
	}

	/**
	 * ID of the first administrator, or 0.
	 */
	private function first_admin(): int {
		$ids = get_users(
			array(
				'role'   => 'administrator',
				'number' => 1,
				'fields' => 'ID',
			)
		);
		return (int) ( $ids[0] ?? 0 );
	}
}
