<?php
/**
 * Email confirmation.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts;

/**
 * Proves that the person owns the email address: a single-use link valid for 48 hours. Only a
 * hash of the token is stored. Google accounts are confirmed by Google's email_verified claim.
 */
final class EmailConfirmation {

	public const TOKEN_META     = '_eta_email_token';
	public const CONFIRMED_META = '_eta_email_confirmed_at';
	public const TTL            = 2 * DAY_IN_SECONDS;

	/**
	 * Sends a new confirmation link, replacing any previous one.
	 *
	 * @param int $user_id User ID.
	 */
	public static function send( int $user_id ): void {
		$user = get_userdata( $user_id );
		if ( ! $user instanceof \WP_User ) {
			return;
		}
		$token = wp_generate_password( 32, false );
		update_user_meta(
			$user_id,
			self::TOKEN_META,
			array(
				'hash'    => hash( 'sha256', $token ),
				'expires' => time() + self::TTL,
			)
		);
		$link = add_query_arg(
			array(
				'u' => $user_id,
				't' => $token,
			),
			Pages::url( Pages::EMAIL_CONFIRMATION )
		);

		$switched = switch_to_locale( get_locale() );
		wp_mail(
			$user->user_email,
			__( 'EuTeAjudo: confirm your email', 'euteajudo' ),
			sprintf(
				/* translators: 1: alias label, 2: confirmation link. */
				__( "Hello, %1\$s.\n\nTo confirm this email at EuTeAjudo.org, open the link below within 48 hours:\n\n%2\$s\n\nIf you did not create an account, you can ignore this message.", 'euteajudo' ),
				Aliases::label( Aliases::number_of( $user_id ) ),
				$link
			)
		);
		if ( $switched ) {
			restore_previous_locale();
		}
	}

	/**
	 * Confirms the email when the token matches and has not expired. Single use.
	 *
	 * @param int    $user_id User ID.
	 * @param string $token   Token from the link.
	 */
	public static function confirm( int $user_id, string $token ): bool {
		$record = get_user_meta( $user_id, self::TOKEN_META, true );
		if ( ! is_array( $record ) || ! is_string( $record['hash'] ?? null ) || (int) ( $record['expires'] ?? 0 ) < time() ) {
			return false;
		}
		if ( ! hash_equals( $record['hash'], hash( 'sha256', $token ) ) ) {
			return false;
		}
		delete_user_meta( $user_id, self::TOKEN_META );
		self::mark_confirmed( $user_id );
		return true;
	}

	/**
	 * Records that the email was proven (link, Google, or a password reset by email).
	 *
	 * @param int $user_id User ID.
	 */
	public static function mark_confirmed( int $user_id ): void {
		update_user_meta( $user_id, self::CONFIRMED_META, time() );
	}

	/**
	 * Whether the user's email was proven.
	 *
	 * @param int $user_id User ID.
	 */
	public static function is_confirmed( int $user_id ): bool {
		return (int) get_user_meta( $user_id, self::CONFIRMED_META, true ) > 0;
	}
}
