<?php
/**
 * Emails to authors.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\HelpRequests;

use EuTeAjudo\Accounts\AccountDeletion;
use EuTeAjudo\Accounts\Aliases;

/**
 * Neutral emails: the subject and body never carry the title or any text of a request, because
 * an inbox is often seen by other people.
 */
final class Notifications {

	/**
	 * Hooks the new reply email.
	 */
	public static function register(): void {
		add_action( 'wp_insert_comment', array( self::class, 'new_reply' ), 30, 2 );
	}

	/**
	 * Tells the author that someone replied.
	 *
	 * @param int         $comment_id Comment ID.
	 * @param \WP_Comment $comment    Comment.
	 */
	public static function new_reply( int $comment_id, \WP_Comment $comment ): void {
		unset( $comment_id );
		$post = HelpRequests::get( (int) $comment->comment_post_ID );
		if ( null === $post || HelpRequestType::REPLY !== $comment->comment_type || (int) $comment->user_id === (int) $post->post_author ) {
			return;
		}
		self::send(
			$post,
			__( 'EuTeAjudo: you have a new message', 'euteajudo' ),
			/* translators: 1: author's alias, 2: link to the conversation. */
			__( "Hi, %1\$s.\n\nThere is a new reply in one of your requests. To read it, sign in:\n%2\$s\n\nEuTeAjudo.org", 'euteajudo' )
		);
	}

	/**
	 * Tells the author that a request was closed after 30 days without new messages.
	 *
	 * @param \WP_Post $post Request.
	 */
	public static function closed_for_inactivity( \WP_Post $post ): void {
		self::send(
			$post,
			__( 'EuTeAjudo: one of your requests was closed', 'euteajudo' ),
			/* translators: 1: author's alias, 2: link to the conversation. */
			__( "Hi, %1\$s.\n\nOne of your requests had no new messages for 30 days and was closed. You can reopen it whenever you want:\n%2\$s\n\nEuTeAjudo.org", 'euteajudo' )
		);
	}

	/**
	 * Sends an email to the author of a request, in the site language.
	 *
	 * @param \WP_Post $post    Request.
	 * @param string   $subject Subject.
	 * @param string   $body    Body with %1$s (alias) and %2$s (link).
	 */
	private static function send( \WP_Post $post, string $subject, string $body ): void {
		$author = get_userdata( (int) $post->post_author );
		if ( ! $author instanceof \WP_User || AccountDeletion::is_deleted( $author->ID ) || ! is_email( $author->user_email ) ) {
			return;
		}
		wp_mail( $author->user_email, $subject, sprintf( $body, Aliases::label( Aliases::number_of( $author->ID ) ), Conversations::url( $post ) ) );
	}
}
