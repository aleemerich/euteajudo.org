<?php
/**
 * Integration tests: sign-in form.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration\Accounts\Forms;

use EuTeAjudo\Accounts\EmailConfirmation;
use EuTeAjudo\Accounts\Forms\SignIn;
use EuTeAjudo\Accounts\Pages;
use EuTeAjudo\Tests\Integration\TestCase;

/**
 * Email and password sign-in.
 */
final class SignInTest extends TestCase {

	public function set_up(): void {
		parent::set_up();
		Pages::install();
		add_filter( 'send_auth_cookies', '__return_false' );
	}

	/**
	 * Error code in a redirect URL.
	 *
	 * @param string $url URL.
	 */
	private function error_in( string $url ): string {
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
		return is_string( $query['eta_auth_error'] ?? null ) ? $query['eta_auth_error'] : '';
	}

	public function test_confirmed_account_signs_in_with_email_and_password(): void {
		$user = $this->new_user(
			array(
				'user_email' => 'someone@example.org',
				'user_pass'  => 'long enough secret',
			)
		);
		EmailConfirmation::mark_confirmed( $user->ID );

		$url = SignIn::handle(
			array(
				'method'   => 'email',
				'email'    => 'someone@example.org',
				'password' => 'long enough secret',
			)
		);

		$this->assertSame( home_url( '/' ), $url, 'Without redirect_to, goes home (never to an empty URL).' );
		$this->assertSame( $user->ID, get_current_user_id() );
	}

	public function test_wrong_password_gives_one_generic_error(): void {
		$user = $this->new_user(
			array(
				'user_email' => 'someone@example.org',
				'user_pass'  => 'long enough secret',
			)
		);
		EmailConfirmation::mark_confirmed( $user->ID );

		$this->assertSame(
			'credentials',
			$this->error_in(
				SignIn::handle(
					array(
						'method'   => 'email',
						'email'    => 'someone@example.org',
						'password' => 'wrong',
					)
				)
			)
		);
		$this->assertSame(
			'credentials',
			$this->error_in(
				SignIn::handle(
					array(
						'method'   => 'email',
						'email'    => 'nobody@example.org',
						'password' => 'wrong',
					)
				)
			)
		);
		$this->assertSame( 0, get_current_user_id() );
	}

	public function test_unconfirmed_account_cannot_sign_in_yet(): void {
		$this->new_user(
			array(
				'user_email' => 'someone@example.org',
				'user_pass'  => 'long enough secret',
			)
		);

		$url = SignIn::handle(
			array(
				'method'   => 'email',
				'email'    => 'someone@example.org',
				'password' => 'long enough secret',
			)
		);

		$this->assertSame( 'email_unconfirmed', $this->error_in( $url ) );
		$this->assertSame( 0, get_current_user_id() );
	}
}
