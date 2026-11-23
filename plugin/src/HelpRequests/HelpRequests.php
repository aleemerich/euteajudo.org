<?php
/**
 * Help request service.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\HelpRequests;

use EuTeAjudo\Accounts\AccountDeletion;
use EuTeAjudo\Accounts\EmailConfirmation;

/**
 * Creating, finding, closing and reopening help requests, and who may see them.
 */
final class HelpRequests {

	public const CLOSED_REASON_META = '_eta_closed_reason';
	public const CLOSED_AT_META     = '_eta_closed_at';
	public const CLOSED_BY_META     = '_eta_closed_by';
	public const SEEN_META          = '_eta_author_seen_at';
	public const AUTHOR_ACTIVE_META = '_eta_author_active_at';

	public const CLOSE_REASONS = array( 'better', 'no_more', 'unspecified' );

	public const MAX_TITLE = 120;
	public const MAX_TEXT  = 20000;

	/**
	 * Creates a help request awaiting triage.
	 *
	 * @param int    $user_id Author.
	 * @param string $title   Title written by the author.
	 * @param string $body    Text written by the author.
	 * @return int|\WP_Error Request ID or error (not_allowed, email_unconfirmed, title_required, body_required).
	 */
	public static function create( int $user_id, string $title, string $body ): int|\WP_Error {
		$user = get_userdata( $user_id );
		if ( ! $user instanceof \WP_User || AccountDeletion::is_deleted( $user_id ) ) {
			return new \WP_Error( 'not_allowed' );
		}
		if ( ! EmailConfirmation::is_confirmed( $user_id ) && ! user_can( $user, 'edit_posts' ) ) {
			return new \WP_Error( 'email_unconfirmed' );
		}
		$title = mb_substr( trim( sanitize_text_field( $title ) ), 0, self::MAX_TITLE );
		if ( '' === $title ) {
			return new \WP_Error( 'title_required' );
		}
		$body = mb_substr( trim( sanitize_textarea_field( $body ) ), 0, self::MAX_TEXT );
		if ( '' === $body ) {
			return new \WP_Error( 'body_required' );
		}

		$mask = self::mask();
		$id   = wp_insert_post(
			array(
				'post_type'      => HelpRequestType::POST_TYPE,
				'post_status'    => HelpRequestType::AWAITING_TRIAGE,
				'post_author'    => $user_id,
				'post_title'     => $mask->mask( $title ),
				'post_content'   => $mask->mask( $body ),
				'post_name'      => self::new_code(),
				'comment_status' => 'open',
				'ping_status'    => 'closed',
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		update_post_meta( $id, self::AUTHOR_ACTIVE_META, time() );
		return $id;
	}

	/**
	 * Request by its URL code.
	 *
	 * @param string $code Code (post_name).
	 */
	public static function find_by_code( string $code ): ?\WP_Post {
		if ( ! preg_match( '/^[a-z0-9]{12}$/', $code ) ) {
			return null;
		}
		$posts = get_posts(
			array(
				'post_type'   => HelpRequestType::POST_TYPE,
				'post_status' => HelpRequestType::STATUSES,
				'name'        => $code,
				'numberposts' => 1,
			)
		);
		return $posts[0] ?? null;
	}

	/**
	 * Whether a user may see a request: its author or an administrator.
	 *
	 * @param int $request_id Request ID.
	 * @param int $user_id    User ID.
	 */
	public static function can_view( int $request_id, int $user_id ): bool {
		$post = self::get( $request_id );
		if ( null === $post || $user_id <= 0 ) {
			return false;
		}
		return (int) $post->post_author === $user_id || user_can( $user_id, 'edit_others_eta_help_requests' );
	}

	/**
	 * Closes a request at its author's request.
	 *
	 * @param int    $request_id Request ID.
	 * @param int    $user_id    Author.
	 * @param string $reason     One of CLOSE_REASONS.
	 */
	public static function close( int $request_id, int $user_id, string $reason ): true|\WP_Error {
		$post = self::get( $request_id );
		if ( null === $post || (int) $post->post_author !== $user_id ) {
			return new \WP_Error( 'not_allowed' );
		}
		self::set_status( $request_id, HelpRequestType::CLOSED );
		update_post_meta( $request_id, self::CLOSED_REASON_META, in_array( $reason, self::CLOSE_REASONS, true ) ? $reason : 'unspecified' );
		update_post_meta( $request_id, self::CLOSED_AT_META, time() );
		update_post_meta( $request_id, self::CLOSED_BY_META, 'author' );
		return true;
	}

	/**
	 * Reopens a closed request: back to triage if the author spoke last, otherwise in conversation.
	 *
	 * @param int $request_id Request ID.
	 * @param int $user_id    Author.
	 */
	public static function reopen( int $request_id, int $user_id ): true|\WP_Error {
		$post = self::get( $request_id );
		if ( null === $post || (int) $post->post_author !== $user_id || HelpRequestType::CLOSED !== $post->post_status ) {
			return new \WP_Error( 'not_allowed' );
		}
		$last        = self::last_reply( $request_id );
		$from_author = null === $last || (int) $last->user_id === (int) $post->post_author;
		self::set_status( $request_id, $from_author ? HelpRequestType::AWAITING_TRIAGE : HelpRequestType::IN_CONVERSATION );
		delete_post_meta( $request_id, self::CLOSED_REASON_META );
		delete_post_meta( $request_id, self::CLOSED_AT_META );
		delete_post_meta( $request_id, self::CLOSED_BY_META );
		return true;
	}

	/**
	 * Whether a reply from someone other than the author arrived after the author's last visit.
	 *
	 * @param int $request_id Request ID.
	 */
	public static function has_unseen_reply( int $request_id ): bool {
		$post = self::get( $request_id );
		if ( null === $post ) {
			return false;
		}
		$replies = get_comments(
			array(
				'post_id'        => $request_id,
				'type'           => HelpRequestType::REPLY,
				'author__not_in' => array( (int) $post->post_author ),
				'orderby'        => 'comment_date_gmt',
				'order'          => 'DESC',
				'number'         => 1,
			)
		);
		if ( ! is_array( $replies ) || array() === $replies || ! $replies[0] instanceof \WP_Comment ) {
			return false;
		}
		$replied_at = (int) strtotime( $replies[0]->comment_date_gmt . ' UTC' );
		return $replied_at > (int) get_post_meta( $request_id, self::SEEN_META, true );
	}

	/**
	 * Records that the author has just seen the conversation.
	 *
	 * @param int $request_id Request ID.
	 */
	public static function mark_seen( int $request_id ): void {
		update_post_meta( $request_id, self::SEEN_META, time() );
	}

	/**
	 * Mask with the placeholder in the site language.
	 */
	public static function mask(): PersonalDataMask {
		$switched    = switch_to_locale( get_locale() );
		$placeholder = __( '[removed to protect you]', 'euteajudo' );
		if ( $switched ) {
			restore_previous_locale();
		}
		return new PersonalDataMask( $placeholder );
	}

	/**
	 * A help request, or null.
	 *
	 * @param int $request_id Request ID.
	 */
	public static function get( int $request_id ): ?\WP_Post {
		$post = get_post( $request_id );
		return $post instanceof \WP_Post && HelpRequestType::POST_TYPE === $post->post_type ? $post : null;
	}

	/**
	 * Sets the status without touching anything else.
	 *
	 * @param int    $request_id Request ID.
	 * @param string $status     New status.
	 */
	public static function set_status( int $request_id, string $status ): void {
		wp_update_post(
			array(
				'ID'          => $request_id,
				'post_status' => $status,
			)
		);
	}

	/**
	 * Most recent reply, or null.
	 *
	 * @param int $request_id Request ID.
	 */
	private static function last_reply( int $request_id ): ?\WP_Comment {
		$replies = get_comments(
			array(
				'post_id' => $request_id,
				'type'    => HelpRequestType::REPLY,
				'orderby' => array( 'comment_date_gmt', 'comment_ID' ),
				'order'   => 'DESC',
				'number'  => 1,
			)
		);
		return is_array( $replies ) && isset( $replies[0] ) && $replies[0] instanceof \WP_Comment ? $replies[0] : null;
	}

	/**
	 * Random, unused 12-character code for the request URL (ADR 0005).
	 */
	private static function new_code(): string {
		do {
			$code = strtolower( wp_generate_password( 12, false ) );
		} while ( null !== self::find_by_code( $code ) );
		return $code;
	}
}
