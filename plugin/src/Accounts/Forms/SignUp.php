<?php
/**
 * Sign-up form handler.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts\Forms;

use EuTeAjudo\Accounts\Consent;
use EuTeAjudo\Accounts\EmailConfirmation;
use EuTeAjudo\Accounts\Google\GoogleAuth;
use EuTeAjudo\Accounts\Pages;
use EuTeAjudo\Accounts\Roles;

/**
 * Creates accounts. The answer is the same whether or not the email already has an account,
 * so the form never reveals who uses the site.
 */
final class SignUp {

	public const MIN_PASSWORD = 10;

	/**
	 * Handles a submission and returns where to send the browser.
	 *
	 * @param array<string, string> $input Submitted fields: age, consent, method, email, password.
	 */
	public static function handle( array $input ): string {
		$age = $input['age'] ?? '';
		if ( 'minor' === $age ) {
			return Pages::url( Pages::SIGN_UP, array( 'eta_step' => 'minor' ) );
		}
		if ( 'adult' !== $age || '1' !== ( $input['consent'] ?? '' ) ) {
			return self::error( 'consent_required' );
		}

		if ( 'google' === ( $input['method'] ?? '' ) ) {
			return GoogleAuth::begin( 'register', '' );
		}

		$email    = sanitize_email( $input['email'] ?? '' );
		$password = $input['password'] ?? '';
		if ( ! is_email( $email ) ) {
			return self::error( 'email_invalid' );
		}
		if ( mb_strlen( $password ) < self::MIN_PASSWORD ) {
			return self::error( 'password_short' );
		}

		$done = Pages::url( Pages::SIGN_UP, array( 'eta_step' => 'check_email' ) );

		$existing = get_user_by( 'email', $email );
		if ( $existing instanceof \WP_User ) {
			Notices::account_exists( $existing );
			return $done;
		}

		$user_id = wp_insert_user(
			array(
				'user_login' => 'u' . strtolower( wp_generate_password( 15, false ) ),
				'user_email' => $email,
				'user_pass'  => $password,
				'role'       => Roles::FRIEND,
			)
		);
		if ( is_wp_error( $user_id ) ) {
			return self::error( 'unexpected' );
		}
		Consent::record( $user_id, time() );
		EmailConfirmation::send( $user_id );

		return $done;
	}

	/**
	 * Back to the sign-up page with an error code.
	 *
	 * @param string $code Error code.
	 */
	private static function error( string $code ): string {
		return Pages::url( Pages::SIGN_UP, array( 'eta_auth_error' => $code ) );
	}
}
