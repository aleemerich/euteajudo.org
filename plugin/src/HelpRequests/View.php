<?php
/**
 * Display helpers for help requests.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\HelpRequests;

/**
 * Labels and formatting shared by the request blocks.
 */
final class View {

	/**
	 * Status as the author sees it. Triage is internal: the author just waits for a reply.
	 *
	 * @param \WP_Post $request Request.
	 */
	public static function status_label( \WP_Post $request ): string {
		if ( HelpRequestType::CLOSED !== $request->post_status && HelpRequests::has_unseen_reply( $request->ID ) ) {
			return __( 'New reply', 'euteajudo' );
		}
		return match ( $request->post_status ) {
			HelpRequestType::IN_CONVERSATION => __( 'In conversation', 'euteajudo' ),
			HelpRequestType::CLOSED          => __( 'Closed', 'euteajudo' ),
			default                          => __( 'Awaiting reply', 'euteajudo' ),
		};
	}

	/**
	 * Short date in the site language, e.g. "18 de novembro".
	 *
	 * @param string $gmt Date in GMT (Y-m-d H:i:s).
	 */
	public static function date( string $gmt ): string {
		/* translators: Short date format, see https://www.php.net/manual/datetime.format.php */
		return (string) wp_date( __( 'F j', 'euteajudo' ), (int) strtotime( $gmt . ' UTC' ) );
	}

	/**
	 * Short date and time, e.g. "18 de nov., 21h40".
	 *
	 * @param string $gmt Date in GMT (Y-m-d H:i:s).
	 */
	public static function date_time( string $gmt ): string {
		/* translators: Short date and time format, see https://www.php.net/manual/datetime.format.php */
		return (string) wp_date( __( 'M j, g:i a', 'euteajudo' ), (int) strtotime( $gmt . ' UTC' ) );
	}

	/**
	 * Stored text as escaped paragraphs.
	 *
	 * @param string $text Text.
	 */
	public static function paragraphs( string $text ): string {
		return wpautop( esc_html( $text ) );
	}

	/**
	 * The AI disclosure required by ADR 0002.
	 */
	public static function disclosure(): string {
		return '<p class="eta-disclosure">' . esc_html__( 'The replies here are written by an artificial intelligence made to welcome people. If you are at risk right now, call 188.', 'euteajudo' ) . '</p>';
	}

	/**
	 * Replies of a request, oldest first.
	 *
	 * @param int $request_id Request ID.
	 * @return \WP_Comment[]
	 */
	public static function replies( int $request_id ): array {
		$replies = get_comments(
			array(
				'post_id' => $request_id,
				'type'    => HelpRequestType::REPLY,
				'orderby' => array( 'comment_date_gmt', 'comment_ID' ),
				'order'   => 'ASC',
			)
		);
		return is_array( $replies ) ? array_values( array_filter( $replies, static fn ( $reply ): bool => $reply instanceof \WP_Comment ) ) : array();
	}
}
