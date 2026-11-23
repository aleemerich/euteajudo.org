<?php
/**
 * Google ID token.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts\Google;

/**
 * The two facts we keep from Google: the stable subject identifier and the verified email.
 *
 * The signature is not checked: Google's OpenID Connect guide allows this when the token comes
 * directly from its token endpoint over HTTPS, authenticated with the client secret, which is the
 * only way this class is used. Every claim below is still checked.
 */
final class IdToken {

	private const ISSUERS = array( 'https://accounts.google.com', 'accounts.google.com' );
	private const LEEWAY  = 300;

	/**
	 * Creates a validated token.
	 *
	 * @param string $subject Google's stable user identifier (`sub`).
	 * @param string $email   Verified email address.
	 */
	private function __construct(
		public readonly string $subject,
		public readonly string $email,
	) {}

	/**
	 * Parses and validates an ID token.
	 *
	 * @param string $jwt       Raw token.
	 * @param string $client_id Our OAuth client ID (expected audience).
	 * @param string $nonce     Nonce sent in the authorization request.
	 * @param int    $now       Current Unix time.
	 *
	 * @throws InvalidIdToken When any check fails.
	 */
	public static function validate( string $jwt, string $client_id, string $nonce, int $now ): self {
		$claims = self::claims( $jwt );

		if ( ! in_array( $claims['iss'] ?? null, self::ISSUERS, true ) ) {
			throw new InvalidIdToken( 'issuer' );
		}
		if ( ( $claims['aud'] ?? null ) !== $client_id ) {
			throw new InvalidIdToken( 'audience' );
		}
		if ( ! is_int( $claims['exp'] ?? null ) || $claims['exp'] <= $now ) {
			throw new InvalidIdToken( 'expired' );
		}
		if ( ! is_int( $claims['iat'] ?? null ) || $claims['iat'] > $now + self::LEEWAY ) {
			throw new InvalidIdToken( 'issued_in_future' );
		}
		if ( ! is_string( $claims['nonce'] ?? null ) || ! hash_equals( $nonce, $claims['nonce'] ) ) {
			throw new InvalidIdToken( 'nonce' );
		}
		if ( true !== ( $claims['email_verified'] ?? null ) ) {
			throw new InvalidIdToken( 'email_not_verified' );
		}
		if ( ! is_string( $claims['sub'] ?? null ) || '' === $claims['sub'] ) {
			throw new InvalidIdToken( 'subject' );
		}
		if ( ! is_string( $claims['email'] ?? null ) || '' === $claims['email'] ) {
			throw new InvalidIdToken( 'email' );
		}

		return new self( $claims['sub'], strtolower( $claims['email'] ) );
	}

	/**
	 * Decodes the payload of a JWT.
	 *
	 * @param string $jwt Raw token.
	 * @return array<string, mixed>
	 *
	 * @throws InvalidIdToken When the token is malformed.
	 */
	private static function claims( string $jwt ): array {
		$parts = explode( '.', $jwt );
		if ( 3 !== count( $parts ) ) {
			throw new InvalidIdToken( 'malformed' );
		}
		$json   = base64_decode( strtr( $parts[1], '-_', '+/' ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- JWT payload.
		$claims = false === $json ? null : json_decode( $json, true );
		if ( ! is_array( $claims ) ) {
			throw new InvalidIdToken( 'malformed' );
		}
		return $claims;
	}
}
