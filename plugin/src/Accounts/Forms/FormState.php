<?php
/**
 * What a form had when it came back with problems.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts\Forms;

/**
 * When a form has problems, the browser goes back to the page with every problem listed and
 * the fields still filled, so nobody loses what they typed (a request can be long). Values stay
 * on the server for 10 minutes under a random token in the address, never in the address
 * itself, and passwords are never kept.
 */
final class FormState {

	public const PARAM = 'eta_form';
	public const TTL   = 10 * MINUTE_IN_SECONDS;

	/**
	 * State read for this request.
	 *
	 * @var array{errors: string[], values: array<string, string>}|null
	 */
	private static ?array $current = null;

	/**
	 * Stores problems and values and returns the token for the address.
	 *
	 * @param string[]              $errors Error codes, in the order of the form.
	 * @param array<string, string> $values Field values to show again (no passwords).
	 */
	public static function save( array $errors, array $values ): string {
		unset( $values['password'] );
		$token = strtolower( wp_generate_password( 20, false ) );
		set_transient(
			'eta_form_' . $token,
			array(
				'errors' => array_values( $errors ),
				'values' => $values,
			),
			self::TTL
		);
		return $token;
	}

	/**
	 * State for the current address (empty when there is none).
	 *
	 * @return array{errors: string[], values: array<string, string>}
	 */
	public static function current(): array {
		if ( null !== self::$current ) {
			return self::$current;
		}
		$token = isset( $_GET[ self::PARAM ] ) ? sanitize_key( wp_unslash( $_GET[ self::PARAM ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display only; the token is random.
		$state = '' === $token ? false : get_transient( 'eta_form_' . $token );
		$empty = array(
			'errors' => array(),
			'values' => array(),
		);
		if ( ! is_array( $state ) ) {
			self::$current = $empty;
			return $empty;
		}
		self::$current = array(
			'errors' => array_values( array_filter( (array) ( $state['errors'] ?? array() ), 'is_string' ) ),
			'values' => array_filter( (array) ( $state['values'] ?? array() ), 'is_string' ),
		);
		return self::$current;
	}

	/**
	 * A value typed before, or an empty string.
	 *
	 * @param string $field Field name.
	 */
	public static function value( string $field ): string {
		return self::current()['values'][ $field ] ?? '';
	}

	/**
	 * Whether the field had a problem.
	 *
	 * @param string $field Field name.
	 */
	public static function invalid( string $field ): bool {
		$map = array(
			'age'      => array( 'age_required' ),
			'consent'  => array( 'consent_required' ),
			'email'    => array( 'email_invalid', 'credentials' ),
			'password' => array( 'password_short', 'credentials' ),
			'title'    => array( 'title_required' ),
			'body'     => array( 'body_required' ),
		);
		return array() !== array_intersect( $map[ $field ] ?? array(), self::current()['errors'] );
	}

	/**
	 * Forgets the state read in this request (tests).
	 */
	public static function reset(): void {
		self::$current = null;
	}
}
