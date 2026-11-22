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
	 * A same-site URL, or the home page. wp_validate_redirect() returns an empty string for an
	 * empty input, which would redirect to a blank page, so empty means home here.
	 *
	 * @param string $requested Requested target.
	 */
	public static function after_sign_in( string $requested ): string {
		$home = home_url( '/' );
		if ( '' === trim( $requested ) ) {
			return $home;
		}
		$safe = wp_validate_redirect( $requested, $home );
		return '' === $safe ? $home : $safe;
	}
}
