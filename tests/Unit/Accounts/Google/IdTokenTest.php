<?php
/**
 * Unit tests for Google ID token validation.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Unit\Accounts\Google;

use EuTeAjudo\Accounts\Google\IdToken;
use EuTeAjudo\Accounts\Google\InvalidIdToken;
use EuTeAjudo\Tests\Support\FakeGoogle;
use PHPUnit\Framework\TestCase;

/**
 * Claims checks required by Google's OpenID Connect guide.
 */
final class IdTokenTest extends TestCase {

	private const CLIENT = FakeGoogle::CLIENT_ID;
	private const NOW    = 1_800_000_000;

	/**
	 * Builds a token with the given claims.
	 *
	 * @param array<string, mixed> $overrides Claims to change.
	 */
	public static function jwt( array $overrides = array() ): string {
		return FakeGoogle::jwt( $overrides, self::NOW );
	}

	/**
	 * Validates a token with the default expectations.
	 *
	 * @param string $jwt Token.
	 */
	private function validate( string $jwt ): IdToken {
		return IdToken::validate( $jwt, self::CLIENT, 'nonce-abc', self::NOW );
	}

	public function test_valid_token_gives_subject_and_email(): void {
		$token = $this->validate( self::jwt() );

		$this->assertSame( '110169484474386276334', $token->subject );
		$this->assertSame( 'someone@example.org', $token->email );
	}

	public function test_accepts_issuer_without_scheme(): void {
		$this->assertSame( 'someone@example.org', $this->validate( self::jwt( array( 'iss' => 'accounts.google.com' ) ) )->email );
	}

	/**
	 * Tokens that must be refused, with the reason.
	 *
	 * @return array<string, array{0: array<string, mixed>, 1: string}>
	 */
	public static function invalid_claims(): array {
		return array(
			'foreign issuer'     => array( array( 'iss' => 'https://evil.example' ), 'issuer' ),
			'other audience'     => array( array( 'aud' => 'other-client' ), 'audience' ),
			'expired'            => array( array( 'exp' => self::NOW - 1 ), 'expired' ),
			'issued in future'   => array( array( 'iat' => self::NOW + 600 ), 'issued_in_future' ),
			'replayed nonce'     => array( array( 'nonce' => 'other-nonce' ), 'nonce' ),
			'unverified email'   => array( array( 'email_verified' => false ), 'email_not_verified' ),
			'string false email' => array( array( 'email_verified' => 'false' ), 'email_not_verified' ),
			'missing subject'    => array( array( 'sub' => '' ), 'subject' ),
			'missing email'      => array( array( 'email' => '' ), 'email' ),
		);
	}

	/**
	 * Refuses each invalid token.
	 *
	 * @dataProvider invalid_claims
	 *
	 * @param array<string, mixed> $claims Claims that break the token.
	 * @param string               $reason Expected reason.
	 */
	public function test_refuses_invalid_tokens( array $claims, string $reason ): void {
		try {
			$this->validate( self::jwt( $claims ) );
			$this->fail( 'Token should have been refused.' );
		} catch ( InvalidIdToken $error ) {
			$this->assertSame( $reason, $error->reason );
		}
	}

	public function test_refuses_malformed_tokens(): void {
		$this->expectException( InvalidIdToken::class );
		$this->validate( 'not-a-jwt' );
	}
}
