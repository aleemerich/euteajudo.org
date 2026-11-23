<?php
/**
 * Comment privacy.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Privacy;

use EuTeAjudo\HelpRequests\HelpRequestType;

/**
 * Replies are comments; WordPress records the commenter's IP and browser, emails the text to the
 * post author and moderators, and lists comments in feeds and the REST API. None of that may
 * happen for replies (ADR 0003).
 */
final class Comments {

	/**
	 * Hooks the filters.
	 */
	public static function register(): void {
		add_filter( 'pre_comment_user_ip', '__return_empty_string' );
		add_filter( 'pre_comment_user_agent', '__return_empty_string' );
		add_filter( 'notify_post_author', array( self::class, 'no_reply_notification' ), 10, 2 );
		add_filter( 'notify_moderator', array( self::class, 'no_reply_notification' ), 10, 2 );
		add_filter( 'comment_feed_where', array( self::class, 'exclude_replies_from_feeds' ) );
		add_filter( 'rest_comment_query', array( self::class, 'exclude_replies_from_rest' ) );
	}

	/**
	 * Disables core comment emails for replies.
	 *
	 * @param bool       $maybe_notify Whether to notify.
	 * @param int|string $comment_id   Comment ID.
	 */
	public static function no_reply_notification( bool $maybe_notify, int|string $comment_id ): bool {
		$comment = get_comment( (int) $comment_id );
		return $comment instanceof \WP_Comment && HelpRequestType::REPLY === $comment->comment_type ? false : $maybe_notify;
	}

	/**
	 * Keeps replies out of comment feeds.
	 *
	 * @param string $where SQL WHERE clause.
	 */
	public static function exclude_replies_from_feeds( string $where ): string {
		global $wpdb;
		return $where . $wpdb->prepare( " AND {$wpdb->comments}.comment_type <> %s", HelpRequestType::REPLY ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name.
	}

	/**
	 * Keeps replies out of the core comments endpoint for people who cannot see help requests.
	 *
	 * @param array<string, mixed> $args Query arguments.
	 * @return array<string, mixed>
	 */
	public static function exclude_replies_from_rest( array $args ): array {
		if ( ! current_user_can( 'edit_others_eta_help_requests' ) ) {
			$excluded             = is_array( $args['type__not_in'] ?? null ) ? $args['type__not_in'] : array();
			$excluded[]           = HelpRequestType::REPLY;
			$args['type__not_in'] = $excluded;
		}
		return $args;
	}
}
