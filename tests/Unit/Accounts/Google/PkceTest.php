<?php
/**
 * Unit tests for PKCE.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Unit\Accounts\Google;

use EuTeAjudo\Accounts\Google\Pkce;
use PHPUnit\Framework\TestCase;

/**
 * Code verifier and S256 challenge (RFC 7636).
 */
final class PkceTest extends TestCase {

	public function test_challenge_matches_the_rfc_7636_example(): void {
		$this->assertSame(
			'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM',
			Pkce::challenge( 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk' )
		);
	}

	public function test_verifier_is_long_url_safe_and_random(): void {
		$first  = Pkce::random_token();
		$second = Pkce::random_token();

		$this->assertMatchesRegularExpression( '/^[A-Za-z0-9_-]{43,128}$/', $first );
		$this->assertNotSame( $first, $second );
	}
}
