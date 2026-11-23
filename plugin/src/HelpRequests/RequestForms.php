<?php
/**
 * Help request form handlers.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\HelpRequests;

use EuTeAjudo\Accounts\Pages;

/**
 * Write, reply, thank, close and reopen. Each returns where to send the browser.
 */
final class RequestForms {

	/**
	 * Creates a request.
	 *
	 * @param array<string, string> $input Fields: title, body.
	 */
	public static function write( array $input ): string {
		$result = HelpRequests::create( get_current_user_id(), $input['title'] ?? '', $input['body'] ?? '' );
		if ( is_wp_error( $result ) ) {
			return Pages::url( Pages::WRITE, array( 'eta_auth_error' => (string) $result->get_error_code() ) );
		}
		$post = HelpRequests::get( $result );
		return null === $post ? Pages::url( Pages::MY_REQUESTS ) : add_query_arg( 'eta_step', 'sent', Conversations::url( $post ) );
	}

	/**
	 * Adds a reply.
	 *
	 * @param array<string, string> $input Fields: code, text.
	 */
	public static function reply( array $input ): string {
		$post = Conversations::visible_request( $input['code'] ?? '' );
		if ( null === $post ) {
			return self::not_found();
		}
		$result = Replies::add( $post->ID, get_current_user_id(), $input['text'] ?? '' );
		return is_wp_error( $result )
			? self::back( $post, array( 'eta_auth_error' => (string) $result->get_error_code() ) )
			: self::back( $post, array( 'eta_step' => 'reply_sent' ) ) . '#latest';
	}

	/**
	 * Toggles "this helped me".
	 *
	 * @param array<string, string> $input Fields: code, reply.
	 */
	public static function thanks( array $input ): string {
		$post  = Conversations::visible_request( $input['code'] ?? '' );
		$reply = get_comment( (int) ( $input['reply'] ?? 0 ) );
		if ( null === $post || ! $reply instanceof \WP_Comment || (int) $reply->comment_post_ID !== $post->ID ) {
			return self::not_found();
		}
		Replies::toggle_thanks( (int) $reply->comment_ID, get_current_user_id() );
		return self::back( $post, array() ) . '#reply-' . (int) $reply->comment_ID;
	}

	/**
	 * Closes the request.
	 *
	 * @param array<string, string> $input Fields: code, reason.
	 */
	public static function close( array $input ): string {
		$post = Conversations::visible_request( $input['code'] ?? '' );
		if ( null === $post ) {
			return self::not_found();
		}
		$result = HelpRequests::close( $post->ID, get_current_user_id(), $input['reason'] ?? 'unspecified' );
		return self::back( $post, is_wp_error( $result ) ? array( 'eta_auth_error' => 'not_allowed' ) : array( 'eta_step' => 'closed' ) );
	}

	/**
	 * Reopens the request.
	 *
	 * @param array<string, string> $input Fields: code.
	 */
	public static function reopen( array $input ): string {
		$post = Conversations::visible_request( $input['code'] ?? '' );
		if ( null === $post ) {
			return self::not_found();
		}
		$result = HelpRequests::reopen( $post->ID, get_current_user_id() );
		return self::back( $post, is_wp_error( $result ) ? array( 'eta_auth_error' => 'not_allowed' ) : array( 'eta_step' => 'reopened' ) );
	}

	/**
	 * Conversation address with query arguments.
	 *
	 * @param \WP_Post              $post Request.
	 * @param array<string, string> $args Query arguments.
	 */
	private static function back( \WP_Post $post, array $args ): string {
		return array() === $args ? Conversations::url( $post ) : add_query_arg( $args, Conversations::url( $post ) );
	}

	/**
	 * My requests with a "not found" message.
	 */
	private static function not_found(): string {
		return Pages::url( Pages::MY_REQUESTS, array( 'eta_auth_error' => 'not_found' ) );
	}
}
