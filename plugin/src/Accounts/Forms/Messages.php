<?php
/**
 * Messages shown on account pages.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts\Forms;

/**
 * Turns error codes (eta_auth_error) into sentences that explain and guide.
 */
final class Messages {

	/**
	 * Error message for a code, or an empty string for unknown codes.
	 *
	 * @param string $code Error code.
	 */
	public static function error( string $code ): string {
		return match ( $code ) {
			'credentials'          => __( 'Email or password does not match. Check and try again, or use "Forgot password".', 'euteajudo' ),
			'email_unconfirmed'    => __( 'Confirm your email first. We sent a new link to your inbox.', 'euteajudo' ),
			'age_required'         => __( 'Tell us whether you are 18 or older.', 'euteajudo' ),
			'consent_required'     => __( 'Check the agreement box to continue.', 'euteajudo' ),
			'too_many_attempts'    => __( 'Many attempts in a row. Wait 15 minutes and try again, or create a new password with "Forgot password".', 'euteajudo' ),
			'email_invalid'        => __( 'Type a valid email address.', 'euteajudo' ),
			/* translators: %d: minimum number of characters. */
			'password_short'       => sprintf( __( 'The password needs at least %d characters.', 'euteajudo' ), SignUp::MIN_PASSWORD ),
			'needs_registration'   => __( 'This Google account has no account here yet. Create your account to continue.', 'euteajudo' ),
			'not_configured'       => __( 'Sign in with Google is not available right now. Use your email and password.', 'euteajudo' ),
			'denied'               => __( 'Sign in with Google was cancelled.', 'euteajudo' ),
			'id_token'             => __( 'This Google account does not have a verified email. Use another account, or your email and password.', 'euteajudo' ),
			'state', 'expired', 'token_exchange' => __( 'Sign in with Google did not finish. Try again.', 'euteajudo' ),
			'expired_form'         => __( 'This page stayed open for too long. Try again.', 'euteajudo' ),
			'reset_link_invalid'   => __( 'This link is invalid or has expired. Ask for a new one below.', 'euteajudo' ),
			'confirmation_invalid' => __( 'This confirmation link is invalid or has expired. Sign in to get a new one.', 'euteajudo' ),
			'unexpected'           => __( 'Something did not work on our side. Try again in a few minutes.', 'euteajudo' ),
			'delete_unconfirmed'   => __( 'Check the box to confirm that you want to delete your account.', 'euteajudo' ),
			'delete_not_allowed'   => __( 'Accounts that manage the site cannot be deleted here.', 'euteajudo' ),
			'title_required'       => __( 'Give your request a title, so you can find it later.', 'euteajudo' ),
			'body_required'        => __( 'The text is empty. Write a few lines to send your request.', 'euteajudo' ),
			'empty'                => __( 'The reply is empty. Write something to send it.', 'euteajudo' ),
			'closed'               => __( 'This request is closed. Reopen it to keep talking.', 'euteajudo' ),
			'not_allowed'          => __( 'This cannot be done here. Your requests are in My requests.', 'euteajudo' ),
			'not_found'            => __( 'We could not find this request. Your requests are in My requests.', 'euteajudo' ),
			default                => '',
		};
	}

	/**
	 * Current error code from the URL.
	 */
	public static function current_error(): string {
		return isset( $_GET['eta_auth_error'] ) ? sanitize_key( wp_unslash( $_GET['eta_auth_error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display only.
	}

	/**
	 * Current step from the URL.
	 */
	public static function current_step(): string {
		return isset( $_GET['eta_step'] ) ? sanitize_key( wp_unslash( $_GET['eta_step'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display only.
	}
}
