<?php
/**
 * Sign-in form handler.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts\Forms;

use EuTeAjudo\Accounts\EmailConfirmation;
use EuTeAjudo\Accounts\Google\GoogleAuth;
use EuTeAjudo\Accounts\Pages;
use EuTeAjudo\Accounts\Redirects;

/**
 * Signs people in with email and password, or sends them to Google.
 */
final class SignIn {

	private const RESEND_GAP = 5 * MINUTE_IN_SECONDS;

	/**
	 * Handles a submission and returns where to send the browser.
	 *
	 * @param array<string, string> $input Submitted fields: method, email, password, redirect_to.
	 */
	public static function handle( array $input ): string {
		$redirect = Redirects::after_sign_in( $input['redirect_to'] ?? '' );

		if ( 'google' === ( $input['method'] ?? '' ) ) {
			return GoogleAuth::begin( 'login', $redirect );
		}

		$user = get_user_by( 'email', sanitize_email( $input['email'] ?? '' ) );
		if ( ! $user instanceof \WP_User ) {
			return self::error( 'credentials' );
		}
		$authenticated = wp_authenticate( $user->user_login, $input['password'] ?? '' );
		if ( ! $authenticated instanceof \WP_User ) {
			return self::error( 'credentials' );
		}

		if ( ! EmailConfirmation::is_confirmed( $authenticated->ID ) && ! user_can( $authenticated, 'edit_posts' ) ) {
			self::resend_confirmation( $authenticated->ID );
			return self::error( 'email_unconfirmed' );
		}

		wp_set_current_user( $authenticated->ID );
		wp_set_auth_cookie( $authenticated->ID, true, is_ssl() );
		do_action( 'wp_login', $authenticated->user_login, $authenticated ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core action, fired as wp_signon does.

		return $redirect;
	}

	/**
	 * Sends a fresh confirmation link, at most once every few minutes.
	 *
	 * @param int $user_id User ID.
	 */
	private static function resend_confirmation( int $user_id ): void {
		$key = 'eta_confirm_resend_' . $user_id;
		if ( false !== get_transient( $key ) ) {
			return;
		}
		set_transient( $key, 1, self::RESEND_GAP );
		EmailConfirmation::send( $user_id );
	}

	/**
	 * Back to the sign-in page with an error code.
	 *
	 * @param string $code Error code.
	 */
	private static function error( string $code ): string {
		return Pages::url( Pages::SIGN_IN, array( 'eta_auth_error' => $code ) );
	}
}
