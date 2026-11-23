<?php
/**
 * Limit on wrong passwords.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts\Forms;

/**
 * Short passwords are allowed (6 characters), so guessing must be slow: after 5 wrong attempts
 * for one email, or 20 from one network address, sign-in waits 15 minutes. Only keyed hashes
 * are stored, for 15 minutes, never the email or the address themselves.
 */
final class SignInAttempts {

	public const PER_EMAIL   = 5;
	public const PER_ADDRESS = 20;
	public const WINDOW      = 15 * MINUTE_IN_SECONDS;

	/**
	 * Whether sign-in is paused for this email or for the current address.
	 *
	 * @param string $email Email typed.
	 */
	public static function blocked( string $email ): bool {
		return self::count( self::email_key( $email ) ) >= self::PER_EMAIL
			|| self::count( self::address_key() ) >= self::PER_ADDRESS;
	}

	/**
	 * Counts a wrong attempt.
	 *
	 * @param string $email Email typed.
	 */
	public static function failed( string $email ): void {
		foreach ( array( self::email_key( $email ), self::address_key() ) as $key ) {
			set_transient( $key, self::count( $key ) + 1, self::WINDOW );
		}
	}

	/**
	 * Clears the count for an email after a right password.
	 *
	 * @param string $email Email typed.
	 */
	public static function succeeded( string $email ): void {
		delete_transient( self::email_key( $email ) );
	}

	/**
	 * Current count for a key.
	 *
	 * @param string $key Transient key.
	 */
	private static function count( string $key ): int {
		return (int) get_transient( $key );
	}

	/**
	 * Key for an email.
	 *
	 * @param string $email Email.
	 */
	private static function email_key( string $email ): string {
		return 'eta_signin_e_' . substr( hash_hmac( 'sha256', strtolower( trim( $email ) ), wp_salt( 'auth' ) ), 0, 32 );
	}

	/**
	 * Key for the current network address.
	 */
	private static function address_key(): string {
		$address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return 'eta_signin_a_' . substr( hash_hmac( 'sha256', $address, wp_salt( 'auth' ) ), 0, 32 );
	}
}
