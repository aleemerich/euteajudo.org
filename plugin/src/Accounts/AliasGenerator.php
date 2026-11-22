<?php
/**
 * Alias number generator.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts;

/**
 * Draws random, unique five-digit alias numbers ("Amigo 48213").
 *
 * Random rather than sequential, so an alias reveals neither how many people use the site nor
 * when someone joined. Pure PHP: the random source and the uniqueness check are injected.
 */
final class AliasGenerator {

	public const MIN = 10000;
	public const MAX = 99999;

	/**
	 * Draws before giving up. With 90,000 numbers this is only reached when the space is
	 * nearly full, which is the signal to widen the range.
	 */
	public const MAX_ATTEMPTS = 50;

	/**
	 * Creates a generator.
	 *
	 * @param \Closure $random Returns a random integer between its two arguments, inclusive.
	 * @param \Closure $taken  Tells whether an alias number is already in use.
	 *
	 * @phpstan-param \Closure(int, int): int $random
	 * @phpstan-param \Closure(string): bool $taken
	 */
	public function __construct(
		private readonly \Closure $random,
		private readonly \Closure $taken,
	) {}

	/**
	 * Returns a free alias number.
	 *
	 * @throws AliasSpaceExhausted When no free number is found.
	 */
	public function generate(): string {
		for ( $attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++ ) {
			$number = (string) ( $this->random )( self::MIN, self::MAX );
			if ( ! ( $this->taken )( $number ) ) {
				return $number;
			}
		}
		throw new AliasSpaceExhausted( 'No free alias number after ' . self::MAX_ATTEMPTS . ' attempts.' ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Not output.
	}
}
