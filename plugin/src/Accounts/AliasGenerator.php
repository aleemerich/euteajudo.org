<?php
/**
 * Alias number generator.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts;

/**
 * Draws random, unique alias numbers ("Amigo 48213") inside a range of digits.
 *
 * Random rather than sequential, so an alias reveals neither how many people use the site nor
 * when someone joined. Pure PHP: the random source, the uniqueness check and the range are injected.
 */
final class AliasGenerator {

	/**
	 * Draws before giving up. Only reached when the range is nearly full, which is the signal
	 * to widen it.
	 */
	public const MAX_ATTEMPTS = 50;

	/**
	 * Creates a generator.
	 *
	 * @param \Closure   $random Returns a random integer between its two arguments, inclusive.
	 * @param \Closure   $taken  Tells whether an alias number is already in use.
	 * @param AliasRange $range  Numbers an alias may take.
	 *
	 * @phpstan-param \Closure(int, int): int $random
	 * @phpstan-param \Closure(string): bool $taken
	 */
	public function __construct(
		private readonly \Closure $random,
		private readonly \Closure $taken,
		private readonly AliasRange $range,
	) {}

	/**
	 * Returns a free alias number.
	 *
	 * @throws AliasSpaceExhausted When no free number is found.
	 */
	public function generate(): string {
		for ( $attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++ ) {
			$number = (string) ( $this->random )( $this->range->lowest(), $this->range->highest() );
			if ( ! ( $this->taken )( $number ) ) {
				return $number;
			}
		}
		throw new AliasSpaceExhausted( 'No free alias number after ' . self::MAX_ATTEMPTS . ' attempts.' ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Not output.
	}
}
