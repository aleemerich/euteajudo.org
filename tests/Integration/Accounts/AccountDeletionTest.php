<?php
/**
 * Integration tests: logical account deletion.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration\Accounts;

use EuTeAjudo\Accounts\AccountDeletion;
use EuTeAjudo\Accounts\Aliases;
use EuTeAjudo\Accounts\EmailConfirmation;
use EuTeAjudo\Accounts\Forms\AccountForm;
use EuTeAjudo\Accounts\Forms\SignIn;
use EuTeAjudo\Accounts\Forms\SignUp;
use EuTeAjudo\Accounts\Google\GoogleAuth;
use EuTeAjudo\Accounts\Pages;
use EuTeAjudo\Accounts\Roles;
use EuTeAjudo\HelpRequests\HelpRequests;
use EuTeAjudo\HelpRequests\HelpRequestType;
use EuTeAjudo\Tests\Integration\TestCase;

/**
 * Deleting an account removes the email and every way back in, and keeps the alias.
 */
final class AccountDeletionTest extends TestCase {

	public function set_up(): void {
		parent::set_up();
		Pages::install();
		reset_phpmailer_instance();
		add_filter( 'send_auth_cookies', '__return_false' );
	}

	/**
	 * Creates a confirmed Friend with a password and a Google link.
	 */
	private function friend(): \WP_User {
		$user = $this->new_user(
			array(
				'user_email' => 'someone@example.org',
				'user_pass'  => 'long enough secret',
				'role'       => Roles::FRIEND,
			)
		);
		EmailConfirmation::mark_confirmed( $user->ID );
		update_user_meta( $user->ID, GoogleAuth::SUBJECT_META, '110169484474386276334' );
		return $user;
	}

	public function test_deletion_removes_email_google_link_role_and_sessions_but_keeps_the_alias(): void {
		$user  = $this->friend();
		$alias = Aliases::number_of( $user->ID );
		\WP_Session_Tokens::get_instance( $user->ID )->create( time() + HOUR_IN_SECONDS );

		AccountDeletion::delete( $user->ID );

		$after = $this->reload( $user->ID );
		$this->assertNotSame( 'someone@example.org', $after->user_email );
		$this->assertStringEndsWith( '.invalid', $after->user_email );
		$this->assertSame( array(), $after->roles );
		$this->assertSame( '', (string) get_user_meta( $user->ID, GoogleAuth::SUBJECT_META, true ) );
		$this->assertSame( array(), \WP_Session_Tokens::get_instance( $user->ID )->get_all() );
		$this->assertSame( $alias, Aliases::number_of( $user->ID ) );
		$this->assertTrue( AccountDeletion::is_deleted( $user->ID ) );
	}

	public function test_only_one_short_email_says_the_account_was_deleted(): void {
		$user = $this->friend();
		reset_phpmailer_instance();

		AccountDeletion::delete( $user->ID );

		$mailer = tests_retrieve_phpmailer_instance();
		$this->assertInstanceOf( \MockPHPMailer::class, $mailer );
		$this->assertFalse( $mailer->get_sent( 1 ), 'Core email and password change notices stay off.' );
		$email = $this->last_email();
		$this->assertSame( 'someone@example.org', $email['to'] );
		$this->assertStringContainsString( 'EuTeAjudo', $email['subject'] );
		$this->assertStringNotContainsString( 'invalid', $email['body'] );
		$this->assertStringNotContainsString( 'deleted-', $email['body'] );
	}

	public function test_deletion_closes_open_requests(): void {
		$user = $this->new_user( array( 'role' => Roles::FRIEND ) );
		EmailConfirmation::mark_confirmed( $user->ID );
		$request = HelpRequests::create( $user->ID, 'Título', 'Texto' );
		$this->assertIsInt( $request );

		AccountDeletion::delete( $user->ID );

		$this->assertSame( HelpRequestType::CLOSED, get_post_status( $request ) );
		$this->assertSame( 'account_deleted', get_post_meta( $request, HelpRequests::CLOSED_REASON_META, true ) );
	}

	public function test_deleted_account_cannot_sign_in_and_the_email_can_start_a_new_account(): void {
		$user = $this->friend();
		AccountDeletion::delete( $user->ID );

		$url = SignIn::handle(
			array(
				'method'   => 'email',
				'email'    => 'someone@example.org',
				'password' => 'long enough secret',
			)
		);
		$this->assertSame( array( 'credentials' ), $this->form_state( $url )['errors'] );

		SignUp::handle(
			array(
				'age'      => 'adult',
				'consent'  => '1',
				'method'   => 'email',
				'email'    => 'someone@example.org',
				'password' => 'another long secret',
			)
		);
		$new = get_user_by( 'email', 'someone@example.org' );
		$this->assertInstanceOf( \WP_User::class, $new );
		$this->assertNotSame( $user->ID, $new->ID );
		$this->assertNotSame( Aliases::number_of( $user->ID ), Aliases::number_of( $new->ID ) );
	}

	public function test_form_requires_confirmation_and_a_signed_in_friend(): void {
		$user = $this->friend();
		wp_set_current_user( $user->ID );

		$this->assertStringContainsString( 'eta_auth_error=delete_unconfirmed', AccountForm::delete( array( 'confirm' => '' ) ) );
		$this->assertFalse( AccountDeletion::is_deleted( $user->ID ) );

		$url = AccountForm::delete( array( 'confirm' => '1' ) );
		$this->assertStringContainsString( 'eta_step=account_deleted', $url );
		$this->assertTrue( AccountDeletion::is_deleted( $user->ID ) );
		$this->assertSame( 0, get_current_user_id() );
	}

	public function test_administrators_cannot_delete_themselves_here(): void {
		$admin = $this->new_user( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin->ID );

		$this->assertStringContainsString( 'eta_auth_error=delete_not_allowed', AccountForm::delete( array( 'confirm' => '1' ) ) );
		$this->assertFalse( AccountDeletion::is_deleted( $admin->ID ) );
	}
}
