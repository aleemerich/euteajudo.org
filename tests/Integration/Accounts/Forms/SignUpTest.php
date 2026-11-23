<?php
/**
 * Integration tests: sign-up form.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration\Accounts\Forms;

use EuTeAjudo\Accounts\Consent;
use EuTeAjudo\Accounts\EmailConfirmation;
use EuTeAjudo\Accounts\Forms\SignUp;
use EuTeAjudo\Accounts\Pages;
use EuTeAjudo\Accounts\Roles;
use EuTeAjudo\Tests\Integration\TestCase;
use EuTeAjudo\Tests\Support\FakeGoogle;

/**
 * Age, consent, email and password rules, and no hint about existing accounts.
 */
final class SignUpTest extends TestCase {

	private const GOOD = array(
		'age'      => 'adult',
		'consent'  => '1',
		'method'   => 'email',
		'email'    => 'someone@example.org',
		'password' => 'long enough secret',
	);

	public function set_up(): void {
		parent::set_up();
		Pages::install();
		reset_phpmailer_instance();
		add_filter( 'send_auth_cookies', '__return_false' );
	}

	/**
	 * Query parameter of a redirect URL.
	 *
	 * @param string $url URL.
	 * @param string $key Parameter.
	 */
	private function param( string $url, string $key ): string {
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
		return is_string( $query[ $key ] ?? null ) ? $query[ $key ] : '';
	}

	public function test_email_sign_up_creates_an_unconfirmed_friend_and_sends_the_link(): void {
		$url = SignUp::handle( self::GOOD );

		$this->assertSame( 'check_email', $this->param( $url, 'eta_step' ) );
		$user = get_user_by( 'email', 'someone@example.org' );
		$this->assertInstanceOf( \WP_User::class, $user );
		$this->assertSame( array( Roles::FRIEND ), $user->roles );
		$this->assertSame( Consent::VERSION, Consent::version_of( $user->ID ) );
		$this->assertFalse( EmailConfirmation::is_confirmed( $user->ID ) );
		$this->assertTrue( wp_check_password( 'long enough secret', $user->user_pass, $user->ID ) );
		$this->assertSame( 0, get_current_user_id(), 'Signs in only after confirming the email.' );
		$this->assertSame( 'someone@example.org', $this->last_email()['to'] );
	}

	public function test_minors_are_referred_and_no_account_is_created(): void {
		$url = SignUp::handle( array_merge( self::GOOD, array( 'age' => 'minor' ) ) );

		$this->assertSame( 'minor', $this->param( $url, 'eta_step' ) );
		$this->assertFalse( get_user_by( 'email', 'someone@example.org' ) );
	}

	/**
	 * Inputs that must be refused, with the error code.
	 *
	 * @return array<string, array{0: array<string, string>, 1: string}>
	 */
	public static function refused(): array {
		return array(
			'no age'         => array( array( 'age' => '' ), 'age_required' ),
			'no consent'     => array( array( 'consent' => '' ), 'consent_required' ),
			'invalid email'  => array( array( 'email' => 'not-an-email' ), 'email_invalid' ),
			'short password' => array( array( 'password' => 'short' ), 'password_short' ),
		);
	}

	/**
	 * Refuses each invalid input without creating an account.
	 *
	 * @dataProvider refused
	 *
	 * @param array<string, string> $change Fields to change.
	 * @param string                $error  Expected error code.
	 */
	public function test_refuses_invalid_input( array $change, string $error ): void {
		$url = SignUp::handle( array_merge( self::GOOD, $change ) );

		$this->assertSame( array( $error ), $this->form_state( $url )['errors'] );
		$this->assertFalse( get_user_by( 'email', 'someone@example.org' ) );
	}

	public function test_every_problem_comes_back_at_once_with_the_fields_kept_except_the_password(): void {
		$url = SignUp::handle(
			array(
				'age'      => 'adult',
				'consent'  => '',
				'method'   => 'email',
				'email'    => 'teste1@example',
				'password' => '123',
			)
		);

		$state = $this->form_state( $url );
		$this->assertSame( array( 'consent_required', 'email_invalid', 'password_short' ), $state['errors'] );
		$this->assertSame( 'adult', $state['values']['age'] );
		$this->assertSame( 'teste1@example', $state['values']['email'] );
		$this->assertArrayNotHasKey( 'password', $state['values'] );
	}

	public function test_six_characters_are_enough(): void {
		$url = SignUp::handle( array_merge( self::GOOD, array( 'password' => 'abc123' ) ) );

		$this->assertSame( 'check_email', $this->param( $url, 'eta_step' ) );
	}

	public function test_existing_email_gets_the_same_answer_and_a_notice_instead_of_a_new_account(): void {
		$existing = $this->new_user( array( 'user_email' => 'someone@example.org' ) );

		$url = SignUp::handle( self::GOOD );

		$this->assertSame( 'check_email', $this->param( $url, 'eta_step' ) );
		$this->assertSame( $existing->ID, get_user_by( 'email', 'someone@example.org' )->ID ?? null );
		$this->assertCount(
			1,
			get_users(
				array(
					'search'         => 'someone@example.org',
					'search_columns' => array( 'user_email' ),
				)
			)
		);
		$this->assertSame( 'someone@example.org', $this->last_email()['to'] );
	}

	public function test_google_method_goes_to_google_with_the_registration_intent(): void {
		putenv( 'EUTEAJUDO_GOOGLE_CLIENT_ID=' . FakeGoogle::CLIENT_ID );
		putenv( 'EUTEAJUDO_GOOGLE_CLIENT_SECRET=secret-xyz' );

		$url = SignUp::handle(
			array_merge(
				self::GOOD,
				array(
					'method'   => 'google',
					'email'    => '',
					'password' => '',
				)
			)
		);

		putenv( 'EUTEAJUDO_GOOGLE_CLIENT_ID' );
		putenv( 'EUTEAJUDO_GOOGLE_CLIENT_SECRET' );
		$this->assertStringStartsWith( 'https://accounts.google.com/o/oauth2/v2/auth?', $url );
	}
}
