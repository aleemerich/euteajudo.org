<?php
/**
 * Integration tests: mail and hardening.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration\Operations;

use EuTeAjudo\Accounts\Roles;
use EuTeAjudo\Migrations;
use EuTeAjudo\Tests\Integration\TestCase;

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- PHPMailer API and a core hook.

/**
 * SMTP from settings, neutral sender, XML-RPC off, Application Passwords only where needed.
 */
final class OperationsTest extends TestCase {

	public function tear_down(): void {
		foreach ( array( 'EUTEAJUDO_SMTP_HOST', 'EUTEAJUDO_SMTP_PORT', 'EUTEAJUDO_SMTP_USER', 'EUTEAJUDO_SMTP_PASSWORD', 'EUTEAJUDO_MAIL_FROM' ) as $name ) {
			putenv( $name );
		}
		parent::tear_down();
	}

	public function test_smtp_is_used_when_configured(): void {
		putenv( 'EUTEAJUDO_SMTP_HOST=smtp.example.org' );
		putenv( 'EUTEAJUDO_SMTP_PORT=465' );
		putenv( 'EUTEAJUDO_SMTP_USER=apikey' );
		putenv( 'EUTEAJUDO_SMTP_PASSWORD=secret' );
		$mailer = new \MockPHPMailer( true );

		do_action_ref_array( 'phpmailer_init', array( &$mailer ) );

		$this->assertSame( 'smtp', $mailer->Mailer );
		$this->assertSame( 'smtp.example.org', $mailer->Host );
		$this->assertSame( 465, $mailer->Port );
		$this->assertSame( 'ssl', $mailer->SMTPSecure );
		$this->assertTrue( $mailer->SMTPAuth );
	}

	public function test_without_smtp_settings_the_default_mailer_stays(): void {
		$mailer = new \MockPHPMailer( true );

		do_action_ref_array( 'phpmailer_init', array( &$mailer ) );

		$this->assertNotSame( 'smtp', $mailer->Mailer );
	}

	public function test_sender_is_neutral(): void {
		reset_phpmailer_instance();
		wp_mail( 'someone@example.org', 'Assunto', 'Corpo' );
		$mailer = tests_retrieve_phpmailer_instance();
		$this->assertInstanceOf( \MockPHPMailer::class, $mailer );

		$this->assertSame( 'EuTeAjudo', $mailer->FromName );
		$this->assertStringStartsWith( 'nao-responda@', $mailer->From );

		putenv( 'EUTEAJUDO_MAIL_FROM=contato@euteajudo.org' );
		reset_phpmailer_instance();
		wp_mail( 'someone@example.org', 'Assunto', 'Corpo' );
		$mailer = tests_retrieve_phpmailer_instance();
		$this->assertInstanceOf( \MockPHPMailer::class, $mailer );
		$this->assertSame( 'contato@euteajudo.org', $mailer->From );
	}

	public function test_xmlrpc_is_off(): void {
		$this->assertFalse( apply_filters( 'xmlrpc_enabled', true ) );
	}

	public function test_application_passwords_only_for_agents_and_administrators(): void {
		Roles::install();
		Migrations::agent_capabilities();
		$friend = $this->new_user( array( 'role' => Roles::FRIEND ) );
		$agent  = $this->new_user( array( 'role' => Roles::AGENT ) );
		$admin  = $this->new_user( array( 'role' => 'administrator' ) );
		add_filter( 'wp_is_application_passwords_available', '__return_true' );

		$this->assertFalse( wp_is_application_passwords_available_for_user( $friend ) );
		$this->assertTrue( wp_is_application_passwords_available_for_user( $agent ) );
		$this->assertTrue( wp_is_application_passwords_available_for_user( $admin ) );
	}
}
