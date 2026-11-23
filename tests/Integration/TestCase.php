<?php
/**
 * Base class for integration tests.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration;

use WP_UnitTestCase;
use WP_User;

/**
 * Typed helpers over the WordPress test factories.
 */
abstract class TestCase extends WP_UnitTestCase {

	/**
	 * Creates a user and returns it.
	 *
	 * @param array<string, mixed> $args User fields.
	 */
	protected function new_user( array $args = array() ): WP_User {
		$user = self::factory()->user->create_and_get( $args );
		$this->assertInstanceOf( WP_User::class, $user );
		return $user;
	}

	/**
	 * Last email captured by the test mailer.
	 *
	 * @return array{to: string, subject: string, body: string}
	 */
	protected function last_email(): array {
		$mailer = tests_retrieve_phpmailer_instance();
		$this->assertInstanceOf( \MockPHPMailer::class, $mailer );
		$sent = $mailer->get_sent();
		$this->assertIsObject( $sent, 'An email was sent.' );
		$fields    = get_object_vars( $sent );
		$recipient = $mailer->get_recipient( 'to' );
		$address   = is_object( $recipient ) ? get_object_vars( $recipient )['address'] ?? '' : '';
		return array(
			'to'      => is_string( $address ) ? $address : '',
			'subject' => is_string( $fields['subject'] ?? null ) ? $fields['subject'] : '',
			'body'    => is_string( $fields['body'] ?? null ) ? $fields['body'] : '',
		);
	}

	/**
	 * Whether the test mailer captured no email.
	 */
	protected function no_email_sent(): bool {
		$mailer = tests_retrieve_phpmailer_instance();
		return ! $mailer instanceof \MockPHPMailer || false === $mailer->get_sent();
	}

	/**
	 * Problems and values a form brought back, read from the token in the address.
	 *
	 * @param string $url Redirect URL.
	 * @return array{errors: string[], values: array<string, string>}
	 */
	protected function form_state( string $url ): array {
		parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
		$token = is_string( $query['eta_form'] ?? null ) ? $query['eta_form'] : '';
		$state = get_transient( 'eta_form_' . $token );
		$this->assertIsArray( $state, 'The address carries a form state.' );
		return array(
			'errors' => array_values( (array) $state['errors'] ),
			'values' => (array) $state['values'],
		);
	}

	/**
	 * Reloads a user from the database.
	 *
	 * @param int $user_id User ID.
	 */
	protected function reload( int $user_id ): WP_User {
		clean_user_cache( $user_id );
		$user = get_userdata( $user_id );
		$this->assertInstanceOf( WP_User::class, $user );
		return $user;
	}
}
