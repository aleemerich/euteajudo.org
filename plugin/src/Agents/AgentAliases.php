<?php
/**
 * Agent alias bank.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Agents;

use EuTeAjudo\Accounts\Aliases;

/**
 * Agents appear to Friends with a different alias in each request (ADR 0001). The aliases come
 * from a bank drawn at installation and stored only in the database, never in the code, so the
 * public repository does not reveal which "Amigo NNNNN" is an Agent. People never get a number
 * from the bank.
 */
final class AgentAliases {

	public const OPTION = 'eta_agent_aliases';
	public const META   = '_eta_agent_alias';
	public const SIZE   = 30;

	/**
	 * Fills the bank up to SIZE numbers. Idempotent.
	 */
	public static function install(): void {
		$bank    = self::all();
		$missing = self::SIZE - count( $bank );
		for ( $i = 0; $i < $missing; $i++ ) {
			$bank[] = Aliases::free_number();
			update_option( self::OPTION, $bank, false ); // Saved each time, so the next draw sees it.
		}
	}

	/**
	 * Every number in the bank.
	 *
	 * @return string[]
	 */
	public static function all(): array {
		$bank = get_option( self::OPTION, array() );
		return is_array( $bank ) ? array_values( array_filter( $bank, 'is_string' ) ) : array();
	}

	/**
	 * Whether a number belongs to the bank.
	 *
	 * @param string $number Alias number.
	 */
	public static function contains( string $number ): bool {
		return in_array( $number, self::all(), true );
	}

	/**
	 * Alias number the Agents use in a request, drawn once.
	 *
	 * @param int $request_id Request ID.
	 */
	public static function for_request( int $request_id ): string {
		$number = get_post_meta( $request_id, self::META, true );
		if ( is_string( $number ) && '' !== $number ) {
			return $number;
		}
		$bank = self::all();
		if ( array() === $bank ) {
			self::install();
			$bank = self::all();
		}
		$number = $bank[ random_int( 0, count( $bank ) - 1 ) ];
		update_post_meta( $request_id, self::META, $number );
		return $number;
	}

	/**
	 * Alias label for a request, e.g. "Amigo 48213".
	 *
	 * @param int $request_id Request ID.
	 */
	public static function label_for_request( int $request_id ): string {
		return Aliases::label( self::for_request( $request_id ) );
	}
}
