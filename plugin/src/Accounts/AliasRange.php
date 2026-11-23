<?php
/**
 * Range of alias numbers, expressed in digits.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts;

/**
 * Which numbers an alias can take, as a number of digits without leading zeros.
 *
 * 4 to 6 digits means 1000 to 999999. The range is data (a site option), not code, so it can
 * change without a deploy; it also widens itself when it gets full.
 */
final class AliasRange {

	public const OPTION     = 'eta_alias_range';
	public const MAX_DIGITS = 9;

	/**
	 * Creates a range.
	 *
	 * @param int $min_digits Fewest digits an alias can have.
	 * @param int $max_digits Most digits an alias can have.
	 *
	 * @throws \InvalidArgumentException When the range is empty or out of bounds.
	 */
	public function __construct(
		public readonly int $min_digits,
		public readonly int $max_digits,
	) {
		if ( $min_digits < 1 || $max_digits > self::MAX_DIGITS || $min_digits > $max_digits ) {
			throw new \InvalidArgumentException( 'Invalid alias range.' );
		}
	}

	/**
	 * Default range: 4 to 6 digits.
	 */
	public static function default(): self {
		return new self( 4, 6 );
	}

	/**
	 * Builds a range from a stored option value, falling back to the default when invalid.
	 *
	 * @param mixed $value Stored value.
	 */
	public static function from_array( mixed $value ): self {
		if ( ! is_array( $value ) || ! isset( $value['min_digits'], $value['max_digits'] ) ) {
			return self::default();
		}
		try {
			return new self( (int) $value['min_digits'], (int) $value['max_digits'] );
		} catch ( \InvalidArgumentException ) {
			return self::default();
		}
	}

	/**
	 * Range stored in the site options.
	 */
	public static function current(): self {
		return self::from_array( get_option( self::OPTION ) );
	}

	/**
	 * Smallest number in the range.
	 */
	public function lowest(): int {
		return 10 ** ( $this->min_digits - 1 );
	}

	/**
	 * Largest number in the range.
	 */
	public function highest(): int {
		return 10 ** $this->max_digits - 1;
	}

	/**
	 * The same range with one more digit at the top, or null when already at the limit.
	 */
	public function widened(): ?self {
		return $this->max_digits < self::MAX_DIGITS ? new self( $this->min_digits, $this->max_digits + 1 ) : null;
	}

	/**
	 * Option value: [min_digits, max_digits] as a list, for comparisons and storage.
	 *
	 * @return array{0: int, 1: int}
	 */
	public function to_array(): array {
		return array( $this->min_digits, $this->max_digits );
	}

	/**
	 * Option value with named keys.
	 *
	 * @return array{min_digits: int, max_digits: int}
	 */
	public function to_option(): array {
		return array(
			'min_digits' => $this->min_digits,
			'max_digits' => $this->max_digits,
		);
	}
}
