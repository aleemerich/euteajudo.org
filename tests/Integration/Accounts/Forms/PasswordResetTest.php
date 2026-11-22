<?php
/**
 * Integration tests: password reset.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration\Accounts\Forms;

use EuTeAjudo\Accounts\Forms\PasswordReset;
use EuTeAjudo\Accounts\Pages;
use EuTeAjudo\Tests\Integration\TestCase;

/**
 * Reset links go to our own page and never reveal whether an email has an account.
 */
final class PasswordResetTest extends TestCase {

	public function set_up(): void {
		parent::set_up();
		Pages::install();
		reset_phpmailer_instance();
	}

	/**
	 * Query parameters of a URL.
	 *
	 * @param string $url URL.
	 * @return array<string, string>
	 */
	private function query( string $url ): array {
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
		$strings = array();
		foreach ( $query as $key => $value ) {
			if ( is_string( $value ) ) {
				$strings[ (string) $key ] = $value;
			}
		}
		return $strings;
	}

	public function test_request_for_an_account_sends_a_link_to_our_page(): void {
		$this->new_user( array( 'user_email' => 'someone@example.org' ) );

		$url = PasswordReset::request( array( 'email' => 'someone@example.org' ) );

		$this->assertSame( 'reset_sent', $this->query( $url )['eta_step'] ?? '' );
		$this->assertStringContainsString( Pages::url( Pages::PASSWORD_RESET ), $this->last_email()['body'] );
	}

	public function test_request_for_an_unknown_email_looks_the_same_and_sends_nothing(): void {
		$url = PasswordReset::request( array( 'email' => 'nobody@example.org' ) );

		$this->assertSame( 'reset_sent', $this->query( $url )['eta_step'] ?? '' );
		$this->assertTrue( $this->no_email_sent() );
	}

	public function test_valid_key_sets_the_new_password(): void {
		$user = $this->new_user( array( 'user_email' => 'someone@example.org' ) );
		$key  = get_password_reset_key( $user );
		$this->assertIsString( $key );

		$url = PasswordReset::reset(
			array(
				'login'    => $user->user_login,
				'key'      => $key,
				'password' => 'brand new secret',
			)
		);

		$this->assertSame( 'password_changed', $this->query( $url )['eta_step'] ?? '' );
		$this->assertTrue( wp_check_password( 'brand new secret', $this->reload( $user->ID )->user_pass, $user->ID ) );
	}

	public function test_invalid_key_or_short_password_is_refused(): void {
		$user = $this->new_user();
		$key  = get_password_reset_key( $user );
		$this->assertIsString( $key );

		$this->assertSame(
			'reset_link_invalid',
			$this->query(
				PasswordReset::reset(
					array(
						'login'    => $user->user_login,
						'key'      => 'wrong',
						'password' => 'brand new secret',
					)
				)
			)['eta_auth_error'] ?? ''
		);
		$this->assertSame(
			'password_short',
			$this->query(
				PasswordReset::reset(
					array(
						'login'    => $user->user_login,
						'key'      => $key,
						'password' => 'short',
					)
				)
			)['eta_auth_error'] ?? ''
		);
	}
}
