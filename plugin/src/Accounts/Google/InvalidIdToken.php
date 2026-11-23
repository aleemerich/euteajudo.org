<?php
/**
 * Raised when Google's ID token fails a check.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts\Google;

/**
 * ID token refused; `reason` says which check failed.
 */
final class InvalidIdToken extends \RuntimeException {

	/**
	 * Creates the exception.
	 *
	 * @param string $reason Short machine-readable reason.
	 */
	public function __construct( public readonly string $reason ) {
		parent::__construct( 'Invalid Google ID token: ' . $reason ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Not output.
	}
}
