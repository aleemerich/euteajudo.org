<?php
/**
 * Integration tests: email confirmation.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration\Accounts;

use EuTeAjudo\Accounts\EmailConfirmation;
use EuTeAjudo\Accounts\Pages;
use EuTeAjudo\Tests\Integration\TestCase;

/**
 * Confirmation links are single use, expire and prove ownership of the address.
 */
final class EmailConfirmationTest extends TestCase {

	public function set_up(): void {
		parent::set_up();
		Pages::install();
		reset_phpmailer_instance();
	}

	/**
	 * Sends a confirmation and returns the token taken from the email link.
	 *
	 * @param int $user_id User ID.
	 */
	private function send_and_read_token( int $user_id ): string {
		EmailConfirmation::send( $user_id );
		$found = preg_match( '#[?&]t=([A-Za-z0-9]+)#', $this->last_email()['body'], $match );
		$this->assertSame( 1, $found );
		return $match[1] ?? '';
	}

	public function test_email_has_a_neutral_subject_and_a_link_to_the_confirmation_page(): void {
		$user = $this->new_user( array( 'user_email' => 'someone@example.org' ) );

		$this->send_and_read_token( $user->ID );

		$sent = $this->last_email();
		$this->assertSame( 'EuTeAjudo: confirm your email', $sent['subject'] );
		$this->assertStringContainsString( Pages::url( Pages::EMAIL_CONFIRMATION ), $sent['body'] );
	}

	public function test_valid_token_confirms_once(): void {
		$user  = $this->new_user();
		$token = $this->send_and_read_token( $user->ID );

		$this->assertTrue( EmailConfirmation::confirm( $user->ID, $token ) );
		$this->assertTrue( EmailConfirmation::is_confirmed( $user->ID ) );
		$this->assertFalse( EmailConfirmation::confirm( $user->ID, $token ) );
	}

	public function test_wrong_or_expired_token_does_not_confirm(): void {
		$user  = $this->new_user();
		$token = $this->send_and_read_token( $user->ID );

		$this->assertFalse( EmailConfirmation::confirm( $user->ID, 'wrong' ) );

		$record            = get_user_meta( $user->ID, EmailConfirmation::TOKEN_META, true );
		$record['expires'] = time() - 1;
		update_user_meta( $user->ID, EmailConfirmation::TOKEN_META, $record );

		$this->assertFalse( EmailConfirmation::confirm( $user->ID, $token ) );
		$this->assertFalse( EmailConfirmation::is_confirmed( $user->ID ) );
	}
}
