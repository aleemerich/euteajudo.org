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
		return $this->form_state( $url )['errors'][0] ?? '';
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

		$this->assertSame( Pages::url( Pages::WRITE ), $url, 'Without redirect_to and without requests, goes to Write.' );
		$this->assertSame( $user->ID, get_current_user_id() );
	}

	public function test_people_with_requests_land_on_my_requests_and_the_home_page_is_no_preference(): void {
		$user = $this->new_user(
			array(
				'user_email' => 'someone@example.org',
				'user_pass'  => 'long enough secret',
			)
		);
		EmailConfirmation::mark_confirmed( $user->ID );
		\EuTeAjudo\HelpRequests\HelpRequests::create( $user->ID, 'Título', 'Texto' );

		$url = SignIn::handle(
			array(
				'method'      => 'email',
				'email'       => 'someone@example.org',
				'password'    => 'long enough secret',
				'redirect_to' => home_url( '/' ),
			)
		);

		$this->assertSame( Pages::url( Pages::MY_REQUESTS ), $url );
	}

	public function test_wrong_passwords_pause_sign_in_for_that_email_and_keep_the_email_typed(): void {
		$user = $this->new_user(
			array(
				'user_email' => 'someone@example.org',
				'user_pass'  => 'right one',
			)
		);
		EmailConfirmation::mark_confirmed( $user->ID );
		$try = static fn ( string $password ): string => SignIn::handle(
			array(
				'method'   => 'email',
				'email'    => 'someone@example.org',
				'password' => $password,
			)
		);
		for ( $i = 0; $i < \EuTeAjudo\Accounts\Forms\SignInAttempts::PER_EMAIL; $i++ ) {
			$this->assertSame( 'credentials', $this->error_in( $try( 'wrong' ) ) );
		}

		$url = $try( 'right one' );

		$this->assertSame( 'too_many_attempts', $this->error_in( $url ) );
		$this->assertSame( 'someone@example.org', $this->form_state( $url )['values']['email'] );
		$this->assertSame( 0, get_current_user_id() );
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
