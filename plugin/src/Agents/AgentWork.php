<?php
/**
 * What an Agent can do with a help request.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Agents;

use EuTeAjudo\HelpRequests\HelpRequests;
use EuTeAjudo\HelpRequests\HelpRequestType as Type;
use EuTeAjudo\HelpRequests\Replies;

/**
 * The pending queue, reservations, triage, replies and failures. The REST controller only
 * translates HTTP to these calls, so the rules are tested once, here and through the API.
 */
final class AgentWork {

	public const CAP = 'eta_work_as_agent';

	public const RESERVATION_META   = '_eta_reservation';
	public const FAILURES_META      = '_eta_failures';
	public const NEXT_ATTEMPT_META  = '_eta_next_attempt_at';
	public const FAILURE_ALERT_META = '_eta_failure_alerted';
	public const MAIN_CATEGORY_META = '_eta_main_category';
	public const TRIAGED_AT_META    = '_eta_triaged_at';
	public const LAST_SEEN_META     = '_eta_last_seen_at';
	public const LAST_SEEN_OPTION   = 'eta_agent_last_seen';

	public const RESERVATION_TTL = 600;
	public const FIRST_BACKOFF   = 300;
	public const MAX_BACKOFF     = 3600;
	public const MAX_SUMMARY     = 300;
	public const MAX_CATEGORIES  = 3;

	public const OPEN_STATUSES = array( Type::AWAITING_TRIAGE, Type::AWAITING_REPLY, Type::IN_CONVERSATION );

	/**
	 * Hooks the failure reset on a new message from the author.
	 */
	public static function register(): void {
		add_action( 'wp_insert_comment', array( self::class, 'reset_failures_on_author_reply' ), 20, 2 );
	}

	/**
	 * Requests waiting for an Agent: awaiting triage or reply, not reserved by someone else and
	 * not waiting for a retry. Oldest author activity first.
	 *
	 * @param int $agent_id Agent asking.
	 * @param int $limit    Maximum items.
	 * @return \WP_Post[]
	 */
	public static function pending( int $agent_id, int $limit ): array {
		$posts = get_posts(
			array(
				'post_type'   => Type::POST_TYPE,
				'post_status' => array( Type::AWAITING_TRIAGE, Type::AWAITING_REPLY ),
				'numberposts' => 100,
				'orderby'     => 'modified',
				'order'       => 'ASC',
			)
		);
		$now   = time();
		$ready = array_filter(
			$posts,
			static fn ( \WP_Post $post ): bool => (int) get_post_meta( $post->ID, self::NEXT_ATTEMPT_META, true ) <= $now
				&& self::reservation_free_for( $post->ID, $agent_id )
		);
		usort( $ready, static fn ( \WP_Post $a, \WP_Post $b ): int => self::author_active_at( $a->ID ) <=> self::author_active_at( $b->ID ) );
		return array_slice( $ready, 0, max( 1, $limit ) );
	}

	/**
	 * Reserves a request for RESERVATION_TTL seconds. A single conditional UPDATE, so two Agents
	 * asking at the same time cannot both win.
	 *
	 * @param int $request_id Request ID.
	 * @param int $agent_id   Agent.
	 * @return int|\WP_Error Expiry timestamp, or error (eta_not_pending, eta_reserved).
	 */
	public static function reserve( int $request_id, int $agent_id ): int|\WP_Error {
		$post = self::open_request( $request_id );
		if ( null === $post || ! in_array( $post->post_status, array( Type::AWAITING_TRIAGE, Type::AWAITING_REPLY ), true ) ) {
			return new \WP_Error( 'eta_not_pending', 'This request is not waiting for an Agent.', array( 'status' => 409 ) );
		}
		global $wpdb;
		add_post_meta( $request_id, self::RESERVATION_META, self::encode( 0, 0 ), true );
		$until   = time() + self::RESERVATION_TTL;
		$updated = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Atomic compare-and-set; the cache is cleared below.
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE post_id = %d AND meta_key = %s AND ( meta_value < %s OR meta_value LIKE %s )",
				self::encode( $until, $agent_id ),
				$request_id,
				self::RESERVATION_META,
				sprintf( '%010d|', time() ),
				'%|' . $wpdb->esc_like( (string) $agent_id )
			)
		);
		wp_cache_delete( (string) $request_id, 'post_meta' );
		if ( 1 !== $updated && ! self::holds( $request_id, $agent_id ) ) {
			return new \WP_Error( 'eta_reserved', 'Another Agent is working on this request.', array( 'status' => 409 ) );
		}
		return $until;
	}

	/**
	 * Gives a reservation back.
	 *
	 * @param int $request_id Request ID.
	 * @param int $agent_id   Agent.
	 */
	public static function release( int $request_id, int $agent_id ): void {
		if ( self::holds( $request_id, $agent_id ) ) {
			update_post_meta( $request_id, self::RESERVATION_META, self::encode( 0, 0 ) );
		}
	}

	/**
	 * Whether the agent holds a valid reservation.
	 *
	 * @param int $request_id Request ID.
	 * @param int $agent_id   Agent.
	 */
	public static function holds( int $request_id, int $agent_id ): bool {
		[ $until, $holder ] = self::reservation( $request_id );
		return $holder === $agent_id && $until >= time();
	}

	/**
	 * Records the triage: categories (main first), severity, risk signals and summary.
	 *
	 * @param int      $request_id   Request ID.
	 * @param int      $agent_id     Agent.
	 * @param string[] $categories   Category slugs, main first.
	 * @param string   $severity     Severity slug.
	 * @param string[] $risk_signals Risk signal slugs.
	 * @param string   $summary      One-sentence summary.
	 */
	public static function triage( int $request_id, int $agent_id, array $categories, string $severity, array $risk_signals, string $summary ): true|\WP_Error {
		$post = self::open_request( $request_id );
		if ( null === $post || ! in_array( $post->post_status, array( Type::AWAITING_TRIAGE, Type::AWAITING_REPLY ), true ) ) {
			return new \WP_Error( 'eta_not_pending', 'This request is not waiting for an Agent.', array( 'status' => 409 ) );
		}
		if ( ! self::holds( $request_id, $agent_id ) ) {
			return self::not_reserved();
		}
		$categories   = array_values( array_unique( $categories ) );
		$risk_signals = array_values( array_unique( $risk_signals ) );
		$summary      = mb_substr( trim( sanitize_text_field( $summary ) ), 0, self::MAX_SUMMARY );
		if ( array() === $categories || count( $categories ) > self::MAX_CATEGORIES || ! self::terms_exist( Type::CATEGORY, $categories )
			|| ! self::terms_exist( Type::SEVERITY, array( $severity ) ) || ! self::terms_exist( Type::RISK_SIGNAL, $risk_signals ) || '' === $summary ) {
			return new \WP_Error( 'eta_invalid_triage', 'Unknown terms, too many categories or empty summary.', array( 'status' => 400 ) );
		}
		wp_set_object_terms( $request_id, $categories, Type::CATEGORY );
		wp_set_object_terms( $request_id, array( $severity ), Type::SEVERITY );
		wp_set_object_terms( $request_id, $risk_signals, Type::RISK_SIGNAL );
		update_post_meta( $request_id, self::MAIN_CATEGORY_META, $categories[0] );
		update_post_meta( $request_id, self::TRIAGED_AT_META, time() );
		wp_update_post(
			array(
				'ID'           => $request_id,
				'post_excerpt' => HelpRequests::mask()->mask( $summary ),
				'post_status'  => Type::AWAITING_REPLY,
			)
		);
		return true;
	}

	/**
	 * Publishes an Agent reply with the request's Agent alias.
	 *
	 * @param int    $request_id  Request ID.
	 * @param int    $agent_id    Agent.
	 * @param string $text        Reply text.
	 * @param int    $in_reply_to Last author message the Agent read (0 for the request itself).
	 * @return int|\WP_Error Reply ID, or error.
	 */
	public static function reply( int $request_id, int $agent_id, string $text, int $in_reply_to ): int|\WP_Error {
		$post = self::open_request( $request_id );
		if ( null === $post ) {
			return new \WP_Error( 'eta_not_pending', 'This request is not waiting for an Agent.', array( 'status' => 409 ) );
		}
		if ( self::last_author_message_id( $post ) !== $in_reply_to ) {
			return new \WP_Error( 'eta_author_wrote_again', 'The author wrote after the message you replied to. Read the request again.', array( 'status' => 409 ) );
		}
		if ( Type::AWAITING_REPLY !== $post->post_status ) {
			return new \WP_Error( 'eta_triage_required', 'Triage the request before replying.', array( 'status' => 409 ) );
		}
		if ( ! self::holds( $request_id, $agent_id ) ) {
			return self::not_reserved();
		}
		$id = Replies::add( $request_id, $agent_id, $text, AgentAliases::for_request( $request_id ) );
		if ( is_wp_error( $id ) ) {
			return new \WP_Error( 'eta_' . $id->get_error_code(), 'The reply was not accepted.', array( 'status' => 400 ) );
		}
		self::release( $request_id, $agent_id );
		self::clear_failures( $request_id );
		return $id;
	}

	/**
	 * Records a failed verification: the retry waits twice as long each time, up to an hour, and
	 * the administrator gets one email per request.
	 *
	 * @param int $request_id Request ID.
	 * @param int $agent_id   Agent.
	 * @return array{failures: int, retry_in: int}|\WP_Error
	 */
	public static function fail( int $request_id, int $agent_id ): array|\WP_Error {
		if ( null === self::open_request( $request_id ) ) {
			return new \WP_Error( 'eta_not_pending', 'This request is not waiting for an Agent.', array( 'status' => 409 ) );
		}
		if ( ! self::holds( $request_id, $agent_id ) ) {
			return self::not_reserved();
		}
		$failures = self::failures( $request_id ) + 1;
		$retry_in = (int) min( self::MAX_BACKOFF, self::FIRST_BACKOFF * 2 ** ( $failures - 1 ) );
		update_post_meta( $request_id, self::FAILURES_META, $failures );
		update_post_meta( $request_id, self::NEXT_ATTEMPT_META, time() + $retry_in );
		self::release( $request_id, $agent_id );
		if ( ! get_post_meta( $request_id, self::FAILURE_ALERT_META, true ) ) {
			update_post_meta( $request_id, self::FAILURE_ALERT_META, time() );
			self::alert_failure( $request_id );
		}
		return array(
			'failures' => $failures,
			'retry_in' => $retry_in,
		);
	}

	/**
	 * Number of failed verifications since the author last wrote.
	 *
	 * @param int $request_id Request ID.
	 */
	public static function failures( int $request_id ): int {
		return (int) get_post_meta( $request_id, self::FAILURES_META, true );
	}

	/**
	 * A new message from the author gives the Agent a fresh start.
	 *
	 * @param int         $comment_id Comment ID.
	 * @param \WP_Comment $comment    Comment.
	 */
	public static function reset_failures_on_author_reply( int $comment_id, \WP_Comment $comment ): void {
		unset( $comment_id );
		$post = HelpRequests::get( (int) $comment->comment_post_ID );
		if ( null !== $post && Type::REPLY === $comment->comment_type && (int) $comment->user_id === (int) $post->post_author ) {
			self::clear_failures( $post->ID );
		}
	}

	/**
	 * ID of the author's latest reply, or 0 when the author has only written the request.
	 *
	 * @param \WP_Post $post Request.
	 */
	public static function last_author_message_id( \WP_Post $post ): int {
		$ids = get_comments(
			array(
				'post_id' => $post->ID,
				'type'    => Type::REPLY,
				'user_id' => (int) $post->post_author,
				'orderby' => array( 'comment_date_gmt', 'comment_ID' ),
				'order'   => 'DESC',
				'number'  => 1,
				'fields'  => 'ids',
			)
		);
		return is_array( $ids ) && isset( $ids[0] ) && is_numeric( $ids[0] ) ? (int) $ids[0] : 0;
	}

	/**
	 * Records that an Agent called the API.
	 *
	 * @param int $agent_id Agent.
	 */
	public static function touch( int $agent_id ): void {
		$now = time();
		if ( (int) get_user_meta( $agent_id, self::LAST_SEEN_META, true ) < $now - 30 ) {
			update_user_meta( $agent_id, self::LAST_SEEN_META, $now );
			update_option( self::LAST_SEEN_OPTION, $now, false );
		}
	}

	/**
	 * When an Agent last called the API (0 if never).
	 *
	 * @param int $agent_id Agent.
	 */
	public static function last_seen( int $agent_id ): int {
		return (int) get_user_meta( $agent_id, self::LAST_SEEN_META, true );
	}

	/**
	 * Open request (not closed), or null.
	 *
	 * @param int $request_id Request ID.
	 */
	public static function open_request( int $request_id ): ?\WP_Post {
		$post = HelpRequests::get( $request_id );
		return null !== $post && in_array( $post->post_status, self::OPEN_STATUSES, true ) ? $post : null;
	}

	/**
	 * When the author last wrote.
	 *
	 * @param int $request_id Request ID.
	 */
	private static function author_active_at( int $request_id ): int {
		return (int) get_post_meta( $request_id, HelpRequests::AUTHOR_ACTIVE_META, true );
	}

	/**
	 * Whether the request is free for this agent (no valid reservation by someone else).
	 *
	 * @param int $request_id Request ID.
	 * @param int $agent_id   Agent.
	 */
	private static function reservation_free_for( int $request_id, int $agent_id ): bool {
		[ $until, $holder ] = self::reservation( $request_id );
		return $until < time() || $holder === $agent_id;
	}

	/**
	 * Current reservation as [expiry, agent].
	 *
	 * @param int $request_id Request ID.
	 * @return array{0: int, 1: int}
	 */
	private static function reservation( int $request_id ): array {
		$value = (string) get_post_meta( $request_id, self::RESERVATION_META, true );
		$parts = explode( '|', $value );
		return array( (int) $parts[0], (int) ( $parts[1] ?? 0 ) );
	}

	/**
	 * Reservation value; the zero-padded expiry makes string comparison match time order.
	 *
	 * @param int $until    Expiry.
	 * @param int $agent_id Agent.
	 */
	private static function encode( int $until, int $agent_id ): string {
		return sprintf( '%010d|%d', $until, $agent_id );
	}

	/**
	 * Clears the failure counter and retry time.
	 *
	 * @param int $request_id Request ID.
	 */
	private static function clear_failures( int $request_id ): void {
		delete_post_meta( $request_id, self::FAILURES_META );
		delete_post_meta( $request_id, self::NEXT_ATTEMPT_META );
		delete_post_meta( $request_id, self::FAILURE_ALERT_META );
	}

	/**
	 * Whether every slug exists in the taxonomy.
	 *
	 * @param string   $taxonomy Taxonomy.
	 * @param string[] $slugs    Slugs.
	 */
	private static function terms_exist( string $taxonomy, array $slugs ): bool {
		foreach ( $slugs as $slug ) {
			if ( ! is_string( $slug ) || ! term_exists( $slug, $taxonomy ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Error for actions without a valid reservation.
	 */
	private static function not_reserved(): \WP_Error {
		return new \WP_Error( 'eta_not_reserved', 'Reserve the request first; reservations last 10 minutes.', array( 'status' => 409 ) );
	}

	/**
	 * Emails the administrator about a failed verification, without any request content.
	 *
	 * @param int $request_id Request ID.
	 */
	private static function alert_failure( int $request_id ): void {
		wp_mail(
			(string) get_option( 'admin_email' ),
			__( 'EuTeAjudo: an Agent reply failed verification', 'euteajudo' ),
			sprintf(
				/* translators: %s: link to the request in the admin. */
				__( "The Agent could not write a reply that passed verification. It will try again later, with longer waits.\n\nOpen the request: %s", 'euteajudo' ),
				admin_url( 'post.php?post=' . $request_id . '&action=edit' )
			)
		);
	}
}
