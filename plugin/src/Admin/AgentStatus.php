<?php
/**
 * Agent status on the dashboard.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Admin;

use EuTeAjudo\Agents\AgentWork;
use EuTeAjudo\HelpRequests\HelpRequestType as Type;
use EuTeAjudo\HelpRequests\Schedules;

/**
 * Dashboard widget: when an Agent last called the API and how much work is waiting.
 */
final class AgentStatus {

	public const QUIET_AFTER = 30 * MINUTE_IN_SECONDS;

	/**
	 * Hooks the widget.
	 */
	public static function register(): void {
		add_action( 'wp_dashboard_setup', array( self::class, 'add' ) );
	}

	/**
	 * Adds the widget for administrators.
	 */
	public static function add(): void {
		if ( current_user_can( 'edit_others_eta_help_requests' ) ) {
			wp_add_dashboard_widget( 'eta_agent_status', __( 'EuTeAjudo: Agent', 'euteajudo' ), array( self::class, 'render' ) );
		}
	}

	/**
	 * Numbers shown in the widget.
	 *
	 * @return array{last_seen: int, waiting: int, late: int, failed: int}
	 */
	public static function numbers(): array {
		$count = static fn ( array $args ): int => count(
			get_posts(
				$args + array(
					'post_type'   => Type::POST_TYPE,
					'numberposts' => 100,
					'fields'      => 'ids',
				)
			)
		);
		return array(
			'last_seen' => (int) get_option( AgentWork::LAST_SEEN_OPTION, 0 ),
			'waiting'   => $count( array( 'post_status' => array( Type::AWAITING_TRIAGE, Type::AWAITING_REPLY ) ) ),
			'late'      => $count( Schedules::late_query() ),
			'failed'    => $count( array( 'post_status' => AgentWork::OPEN_STATUSES ) + HelpRequestsAdmin::situation_query( 'failed' ) ),
		);
	}

	/**
	 * Renders the widget.
	 */
	public static function render(): void {
		$n    = self::numbers();
		$list = admin_url( 'edit.php?post_type=' . Type::POST_TYPE );
		if ( 0 === $n['last_seen'] ) {
			$seen = __( 'No Agent has called the API yet.', 'euteajudo' );
		} else {
			/* translators: %s: time since the last call, e.g. "5 mins". */
			$seen = sprintf( __( 'Last call from an Agent: %s ago.', 'euteajudo' ), human_time_diff( $n['last_seen'] ) );
		}
		$quiet = 0 === $n['last_seen'] || time() - $n['last_seen'] > self::QUIET_AFTER;
		echo '<p' . ( $quiet ? ' style="color:#b32d2e;font-weight:600"' : '' ) . '>' . esc_html( $seen ) . '</p><ul>';
		/* translators: %d: number of requests. */
		echo '<li><a href="' . esc_url( $list ) . '">' . esc_html( sprintf( _n( '%d request waiting for an Agent', '%d requests waiting for an Agent', $n['waiting'], 'euteajudo' ), $n['waiting'] ) ) . '</a></li>';
		/* translators: %d: number of requests. */
		echo '<li><a href="' . esc_url( add_query_arg( 'eta_situation', 'late', $list ) ) . '">' . esc_html( sprintf( _n( '%d waiting more than 24h', '%d waiting more than 24h', $n['late'], 'euteajudo' ), $n['late'] ) ) . '</a></li>';
		/* translators: %d: number of requests. */
		echo '<li><a href="' . esc_url( add_query_arg( 'eta_situation', 'failed', $list ) ) . '">' . esc_html( sprintf( _n( '%d with failed verification', '%d with failed verification', $n['failed'], 'euteajudo' ), $n['failed'] ) ) . '</a></li></ul>';
	}
}
