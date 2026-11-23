<?php
/**
 * Safe redirect targets.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts;

/**
 * Where to send someone after signing in.
 */
final class Redirects {

	/**
	 * The page someone asked for (same site only); without one, the place that fits the person:
	 * Write when they have no request yet, My requests otherwise. The home page counts as no
	 * preference, since it only invites to sign in.
	 *
	 * @param string $requested Requested target.
	 * @param int    $user_id   Person who just signed in.
	 */
	public static function after_sign_in( string $requested, int $user_id ): string {
		$home = home_url( '/' );
		$safe = '' === trim( $requested ) ? '' : wp_validate_redirect( $requested, '' );
		if ( '' !== $safe && untrailingslashit( $safe ) !== untrailingslashit( $home ) ) {
			return $safe;
		}
		return self::landing( $user_id );
	}

	/**
	 * Write for someone without requests, My requests for everyone else.
	 *
	 * @param int $user_id User ID.
	 */
	public static function landing( int $user_id ): string {
		$has_requests = array() !== get_posts(
			array(
				'post_type'   => \EuTeAjudo\HelpRequests\HelpRequestType::POST_TYPE,
				'post_status' => \EuTeAjudo\HelpRequests\HelpRequestType::STATUSES,
				'author'      => $user_id,
				'numberposts' => 1,
				'fields'      => 'ids',
			)
		);
		return Pages::url( $has_requests ? Pages::MY_REQUESTS : Pages::WRITE );
	}
}
