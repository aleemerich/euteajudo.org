<?php
/**
 * Account emails that are not confirmations.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts\Forms;

use EuTeAjudo\Accounts\Pages;

/**
 * Neutral account emails. Subjects never mention help requests or anything personal.
 */
final class Notices {

	/**
	 * Tells the owner of an email that someone tried to create an account with it.
	 *
	 * @param \WP_User $user Existing account.
	 */
	public static function account_exists( \WP_User $user ): void {
		self::send(
			$user->user_email,
			__( 'EuTeAjudo: account access', 'euteajudo' ),
			sprintf(
				/* translators: 1: sign-in page link, 2: password page link. */
				__( "Someone tried to create an account at EuTeAjudo.org with this email, which already has one.\n\nIf it was you, sign in here:\n%1\$s\n\nForgot your password? Create a new one here:\n%2\$s\n\nIf it was not you, you can ignore this message. Nothing was changed.", 'euteajudo' ),
				Pages::url( Pages::SIGN_IN ),
				Pages::url( Pages::PASSWORD_REQUEST )
			)
		);
	}

	/**
	 * Sends the password reset link.
	 *
	 * @param \WP_User $user User.
	 * @param string   $link Reset link.
	 */
	public static function password_reset( \WP_User $user, string $link ): void {
		self::send(
			$user->user_email,
			__( 'EuTeAjudo: create a new password', 'euteajudo' ),
			sprintf(
				/* translators: %s: reset link. */
				__( "To create a new password at EuTeAjudo.org, open the link below:\n\n%s\n\nIf you did not ask for this, you can ignore this message. Your password stays the same.", 'euteajudo' ),
				$link
			)
		);
	}

	/**
	 * Sends an email in the site language.
	 *
	 * @param string $to      Recipient.
	 * @param string $subject Subject.
	 * @param string $body    Plain-text body.
	 */
	private static function send( string $to, string $subject, string $body ): void {
		wp_mail( $to, $subject, $body );
	}
}
