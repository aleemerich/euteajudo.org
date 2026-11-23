<?php
/**
 * Scheduled jobs.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\HelpRequests;

use EuTeAjudo\Agents\AgentWork;

/**
 * WP-Cron jobs (run by the hosting's cron calling wp-cron.php every 5 minutes, E7):
 * close requests inactive for 30 days, and alert the administrator about requests waiting more
 * than 24 hours for an Agent.
 */
final class Schedules {

	public const CLOSE_HOOK = 'euteajudo_close_inactive';
	public const LATE_HOOK  = 'euteajudo_alert_late';

	public const INACTIVE_AFTER = 30 * DAY_IN_SECONDS;
	public const LATE_AFTER     = DAY_IN_SECONDS;

	public const LATE_ALERTED_META = '_eta_late_alerted_at';

	/**
	 * Hooks the jobs and makes sure they are scheduled (FTP deploys skip activation hooks).
	 */
	public static function register(): void {
		add_action(
			self::CLOSE_HOOK,
			static function (): void {
				self::close_inactive();
			}
		);
		add_action(
			self::LATE_HOOK,
			static function (): void {
				self::alert_late();
			}
		);
		add_action( 'init', array( self::class, 'schedule' ) );
	}

	/**
	 * Schedules missing events.
	 */
	public static function schedule(): void {
		if ( false === wp_next_scheduled( self::CLOSE_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CLOSE_HOOK );
		}
		if ( false === wp_next_scheduled( self::LATE_HOOK ) ) {
			wp_schedule_event( time() + 300, 'hourly', self::LATE_HOOK );
		}
	}

	/**
	 * Closes open requests without new messages for 30 days and tells their authors.
	 *
	 * @return int Requests closed.
	 */
	public static function close_inactive(): int {
		$limit  = time() - self::INACTIVE_AFTER;
		$closed = 0;
		$posts  = get_posts(
			array(
				'post_type'   => HelpRequestType::POST_TYPE,
				'post_status' => AgentWork::OPEN_STATUSES,
				'numberposts' => 100,
				'date_query'  => array(
					array(
						'column' => 'post_date_gmt',
						'before' => gmdate( 'Y-m-d H:i:s', $limit ),
					),
				),
			)
		);
		foreach ( $posts as $post ) {
			if ( self::last_activity( $post ) >= $limit ) {
				continue;
			}
			HelpRequests::set_status( $post->ID, HelpRequestType::CLOSED );
			update_post_meta( $post->ID, HelpRequests::CLOSED_REASON_META, 'inactive' );
			update_post_meta( $post->ID, HelpRequests::CLOSED_AT_META, time() );
			update_post_meta( $post->ID, HelpRequests::CLOSED_BY_META, 'system' );
			Notifications::closed_for_inactivity( $post );
			++$closed;
		}
		return $closed;
	}

	/**
	 * Emails the administrator about requests waiting more than 24 hours for an Agent, once per
	 * message of the author.
	 *
	 * @return int Requests in the alert.
	 */
	public static function alert_late(): int {
		$late  = get_posts(
			array(
				'post_type'   => HelpRequestType::POST_TYPE,
				'numberposts' => 100,
			) + self::late_query()
		);
		$links = array();
		foreach ( $late as $post ) {
			$active = (int) get_post_meta( $post->ID, HelpRequests::AUTHOR_ACTIVE_META, true );
			if ( (int) get_post_meta( $post->ID, self::LATE_ALERTED_META, true ) >= $active ) {
				continue;
			}
			update_post_meta( $post->ID, self::LATE_ALERTED_META, time() );
			$links[] = admin_url( 'post.php?post=' . $post->ID . '&action=edit' );
		}
		if ( array() !== $links ) {
			wp_mail(
				(string) get_option( 'admin_email' ),
				__( 'EuTeAjudo: requests waiting more than 24 hours', 'euteajudo' ),
				sprintf(
					/* translators: %s: list of links to requests in the admin. */
					__( "These requests have waited more than 24 hours for an Agent reply. Check whether the Agent is running (Dashboard) or reply yourself:\n\n%s", 'euteajudo' ),
					implode( "\n", $links )
				)
			);
		}
		return count( $links );
	}

	/**
	 * Query arguments for requests waiting more than 24 hours for an Agent.
	 *
	 * @return array<string, mixed>
	 */
	public static function late_query(): array {
		return array(
			'post_status' => array( HelpRequestType::AWAITING_TRIAGE, HelpRequestType::AWAITING_REPLY ),
			'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Few open requests.
				array(
					'key'     => HelpRequests::AUTHOR_ACTIVE_META,
					'value'   => time() - self::LATE_AFTER,
					'compare' => '<',
					'type'    => 'NUMERIC',
				),
			),
		);
	}

	/**
	 * Time of the latest message in a request.
	 *
	 * @param \WP_Post $post Request.
	 */
	private static function last_activity( \WP_Post $post ): int {
		$latest = (int) strtotime( $post->post_date_gmt . ' UTC' );
		$dates  = get_comments(
			array(
				'post_id' => $post->ID,
				'type'    => HelpRequestType::REPLY,
				'orderby' => 'comment_date_gmt',
				'order'   => 'DESC',
				'number'  => 1,
			)
		);
		if ( is_array( $dates ) && isset( $dates[0] ) && $dates[0] instanceof \WP_Comment ) {
			$latest = max( $latest, (int) strtotime( $dates[0]->comment_date_gmt . ' UTC' ) );
		}
		return $latest;
	}
}
