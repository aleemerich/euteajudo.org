<?php
/**
 * Test double for Google's token endpoint.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Support;

/**
 * Builds Google-like ID tokens.
 */
final class FakeGoogle {

	public const CLIENT_ID = 'client-123.apps.googleusercontent.com';

	/**
	 * Builds an unsigned JWT (Google tokens are trusted by transport, not by signature).
	 *
	 * @param array<string, mixed> $overrides Claims to change.
	 * @param int                  $now       Current Unix time.
	 */
	public static function jwt( array $overrides, int $now ): string {
		$claims = array_merge(
			array(
				'iss'            => 'https://accounts.google.com',
				'aud'            => self::CLIENT_ID,
				'sub'            => '110169484474386276334',
				'email'          => 'someone@example.org',
				'email_verified' => true,
				'nonce'          => 'nonce-abc',
				'iat'            => $now - 10,
				'exp'            => $now + 3600,
			),
			$overrides
		);
		// phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode, WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- JWT encoding, also used by unit tests without WordPress.
		$encode = static fn ( array $part ): string => rtrim( strtr( base64_encode( (string) json_encode( $part ) ), '+/', '-_' ), '=' );
		return $encode( array( 'alg' => 'RS256' ) ) . '.' . $encode( $claims ) . '.signature';
	}
}
