<?php
/**
 * Edit audit for requests and replies.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\HelpRequests;

/**
 * Administrators may edit any request or reply (for safety, e.g. removing personal data the mask
 * missed). The first version is kept in meta, with who edited and when, so nothing is lost.
 */
final class EditAudit {

	public const ORIGINAL_TITLE_META   = '_eta_original_title';
	public const ORIGINAL_CONTENT_META = '_eta_original_content';
	public const LOG_META              = '_eta_edit_log';
	public const EDITED_BY_META        = '_eta_edited_by';
	public const EDITED_AT_META        = '_eta_edited_at';

	/**
	 * Hooks the audit.
	 */
	public static function register(): void {
		add_action( 'pre_post_update', array( self::class, 'before_request_update' ), 10, 2 );
		add_filter( 'wp_update_comment_data', array( self::class, 'before_reply_update' ), 10, 2 );
	}

	/**
	 * Keeps the original title and text of a request on its first edit, and logs every edit.
	 *
	 * @param int                  $post_id Post ID.
	 * @param array<string, mixed> $data    New post data (slashed).
	 */
	public static function before_request_update( int $post_id, array $data ): void {
		$post = HelpRequests::get( $post_id );
		if ( null === $post ) {
			return;
		}
		$title   = wp_unslash( (string) ( $data['post_title'] ?? $post->post_title ) );
		$content = wp_unslash( (string) ( $data['post_content'] ?? $post->post_content ) );
		$changed = array_keys(
			array_filter(
				array(
					'title'   => $title !== $post->post_title,
					'content' => $content !== $post->post_content,
				)
			)
		);
		if ( array() === $changed ) {
			return;
		}
		add_post_meta( $post_id, self::ORIGINAL_TITLE_META, $post->post_title, true );
		add_post_meta( $post_id, self::ORIGINAL_CONTENT_META, $post->post_content, true );
		$log   = self::log( $post_id );
		$log[] = array(
			'user'   => get_current_user_id(),
			'time'   => time(),
			'fields' => $changed,
		);
		update_post_meta( $post_id, self::LOG_META, $log );
	}

	/**
	 * Keeps the original text of a reply on its first edit.
	 *
	 * @param array<string, mixed> $data    New comment data.
	 * @param array<string, mixed> $comment Comment before the update.
	 * @return array<string, mixed>
	 */
	public static function before_reply_update( array $data, array $comment ): array {
		if ( HelpRequestType::REPLY !== ( $comment['comment_type'] ?? '' ) || ! isset( $data['comment_content'] ) ) {
			return $data;
		}
		$before = (string) ( $comment['comment_content'] ?? '' );
		if ( (string) $data['comment_content'] !== $before ) {
			$id = (int) ( $comment['comment_ID'] ?? 0 );
			add_comment_meta( $id, self::ORIGINAL_CONTENT_META, $before, true );
			update_comment_meta( $id, self::EDITED_BY_META, get_current_user_id() );
			update_comment_meta( $id, self::EDITED_AT_META, time() );
		}
		return $data;
	}

	/**
	 * Edit log of a request.
	 *
	 * @param int $post_id Request ID.
	 * @return list<array{user: int, time: int, fields: string[]}>
	 */
	public static function log( int $post_id ): array {
		$log = get_post_meta( $post_id, self::LOG_META, true );
		return is_array( $log ) ? array_values( $log ) : array();
	}
}
