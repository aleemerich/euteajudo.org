<?php
/**
 * PKCE helpers (RFC 7636).
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts\Google;

/**
 * Proof Key for Code Exchange: binds the authorization code to this server.
 */
final class Pkce {

	/**
	 * Random URL-safe token, used for the code verifier, the state and the nonce.
	 */
	public static function random_token(): string {
		return self::base64url( random_bytes( 48 ) );
	}

	/**
	 * S256 code challenge for a verifier.
	 *
	 * @param string $verifier Code verifier.
	 */
	public static function challenge( string $verifier ): string {
		return self::base64url( hash( 'sha256', $verifier, true ) );
	}

	/**
	 * Base64url without padding.
	 *
	 * @param string $bytes Raw bytes.
	 */
	public static function base64url( string $bytes ): string {
		return rtrim( strtr( base64_encode( $bytes ), '+/', '-_' ), '=' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Encoding required by the OAuth spec.
	}
}
