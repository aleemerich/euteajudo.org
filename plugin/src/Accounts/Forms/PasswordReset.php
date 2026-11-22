<?php
/**
 * Password reset handlers.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts\Forms;

use EuTeAjudo\Accounts\EmailConfirmation;
use EuTeAjudo\Accounts\Pages;

/**
 * Password reset through our own pages, using WordPress reset keys. The request always gives
 * the same answer, so it never reveals whether an email has an account.
 */
final class PasswordReset {

	/**
	 * Sends a reset link when the email has an account.
	 *
	 * @param array<string, string> $input Submitted fields: email.
	 */
	public static function request( array $input ): string {
		$user = get_user_by( 'email', sanitize_email( $input['email'] ?? '' ) );
		if ( $user instanceof \WP_User ) {
			$key = get_password_reset_key( $user );
			if ( is_string( $key ) ) {
				Notices::password_reset(
					$user,
					Pages::url(
						Pages::PASSWORD_RESET,
						array(
							'login' => $user->user_login,
							'key'   => $key,
						)
					)
				);
			}
		}
		return Pages::url( Pages::PASSWORD_REQUEST, array( 'eta_step' => 'reset_sent' ) );
	}

	/**
	 * Sets the new password when the key is valid.
	 *
	 * @param array<string, string> $input Submitted fields: login, key, password.
	 */
	public static function reset( array $input ): string {
		$login = $input['login'] ?? '';
		$key   = $input['key'] ?? '';
		$user  = check_password_reset_key( $key, $login );
		if ( ! $user instanceof \WP_User ) {
			return Pages::url( Pages::PASSWORD_REQUEST, array( 'eta_auth_error' => 'reset_link_invalid' ) );
		}
		$password = $input['password'] ?? '';
		if ( mb_strlen( $password ) < SignUp::MIN_PASSWORD ) {
			return Pages::url(
				Pages::PASSWORD_RESET,
				array(
					'login'          => $login,
					'key'            => $key,
					'eta_auth_error' => 'password_short',
				)
			);
		}
		reset_password( $user, $password );
		// The link reached the inbox, which proves the email too.
		EmailConfirmation::mark_confirmed( $user->ID );
		return Pages::url( Pages::SIGN_IN, array( 'eta_step' => 'password_changed' ) );
	}
}
