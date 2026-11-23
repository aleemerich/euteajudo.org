<?php
/**
 * Reply service.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\HelpRequests;

use EuTeAjudo\Accounts\AccountDeletion;
use EuTeAjudo\Accounts\Aliases;

/**
 * Replies are native comments of type eta_reply, stored without email, IP or user agent, with
 * the alias as author name and personal data masked.
 */
final class Replies {

	public const THANKS_META = '_eta_thanks';

	/**
	 * Hooks the status flow: a reply from the author sends the request back to triage; a reply
	 * from anyone who helps puts it in conversation.
	 */
	public static function register(): void {
		add_action( 'wp_insert_comment', array( self::class, 'move_status' ), 10, 2 );
	}

	/**
	 * Adds a reply.
	 *
	 * @param int    $request_id Request ID.
	 * @param int    $user_id    Who writes: the author, or someone who helps.
	 * @param string $text       Reply text.
	 * @return int|\WP_Error Reply ID or error (not_allowed, closed, empty).
	 */
	public static function add( int $request_id, int $user_id, string $text ): int|\WP_Error {
		$post = HelpRequests::get( $request_id );
		if ( null === $post || $user_id <= 0 || AccountDeletion::is_deleted( $user_id ) ) {
			return new \WP_Error( 'not_allowed' );
		}
		$is_author = (int) $post->post_author === $user_id;
		if ( ! $is_author && ! self::can_help( $user_id ) ) {
			return new \WP_Error( 'not_allowed' );
		}
		if ( HelpRequestType::CLOSED === $post->post_status ) {
			return new \WP_Error( 'closed' );
		}
		$text = mb_substr( trim( sanitize_textarea_field( $text ) ), 0, HelpRequests::MAX_TEXT );
		if ( '' === $text ) {
			return new \WP_Error( 'empty' );
		}

		$id = wp_insert_comment(
			array(
				'comment_post_ID'      => $request_id,
				'comment_content'      => HelpRequests::mask()->mask( $text ),
				'comment_type'         => HelpRequestType::REPLY,
				'user_id'              => $user_id,
				'comment_author'       => Aliases::label( Aliases::number_of( $user_id ) ),
				'comment_author_email' => '',
				'comment_author_url'   => '',
				'comment_author_IP'    => '',
				'comment_agent'        => '',
				'comment_approved'     => 1,
			)
		);
		return false === $id ? new \WP_Error( 'unexpected' ) : $id;
	}

	/**
	 * Moves the request status when a reply is stored (by any path, including the REST API).
	 *
	 * @param int         $comment_id Comment ID.
	 * @param \WP_Comment $comment    Comment.
	 */
	public static function move_status( int $comment_id, \WP_Comment $comment ): void {
		unset( $comment_id );
		if ( HelpRequestType::REPLY !== $comment->comment_type ) {
			return;
		}
		$post = HelpRequests::get( (int) $comment->comment_post_ID );
		if ( null === $post || HelpRequestType::CLOSED === $post->post_status ) {
			return;
		}
		if ( (int) $comment->user_id === (int) $post->post_author ) {
			HelpRequests::set_status( $post->ID, HelpRequestType::AWAITING_TRIAGE );
			update_post_meta( $post->ID, HelpRequests::AUTHOR_ACTIVE_META, time() );
			return;
		}
		HelpRequests::set_status( $post->ID, HelpRequestType::IN_CONVERSATION );
	}

	/**
	 * Marks or unmarks "this helped me". Only the request author, only on others' replies.
	 *
	 * @param int $reply_id Reply ID.
	 * @param int $user_id  Request author.
	 * @return bool Whether the mark was changed.
	 */
	public static function toggle_thanks( int $reply_id, int $user_id ): bool {
		$reply = get_comment( $reply_id );
		if ( ! $reply instanceof \WP_Comment || HelpRequestType::REPLY !== $reply->comment_type ) {
			return false;
		}
		$post = HelpRequests::get( (int) $reply->comment_post_ID );
		if ( null === $post || (int) $post->post_author !== $user_id || (int) $reply->user_id === $user_id ) {
			return false;
		}
		if ( self::is_thanked( $reply_id ) ) {
			delete_comment_meta( $reply_id, self::THANKS_META );
		} else {
			update_comment_meta( $reply_id, self::THANKS_META, time() );
		}
		return true;
	}

	/**
	 * Whether the author marked a reply as helpful.
	 *
	 * @param int $reply_id Reply ID.
	 */
	public static function is_thanked( int $reply_id ): bool {
		return (int) get_comment_meta( $reply_id, self::THANKS_META, true ) > 0;
	}

	/**
	 * Whether a user can reply to requests of others (administrators now; Agents from E3).
	 *
	 * @param int $user_id User ID.
	 */
	private static function can_help( int $user_id ): bool {
		return user_can( $user_id, 'edit_others_eta_help_requests' );
	}
}
