<?php
/**
 * Unit tests for the alias generator.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Unit\Accounts;

use EuTeAjudo\Accounts\AliasGenerator;
use EuTeAjudo\Accounts\AliasSpaceExhausted;
use PHPUnit\Framework\TestCase;

/**
 * Alias numbers are random, five digits, and never repeat.
 */
final class AliasGeneratorTest extends TestCase {

	/**
	 * Builds a generator with a scripted random source.
	 *
	 * @param int[]    $draws Numbers the random source returns, in order.
	 * @param string[] $taken Alias numbers already in use.
	 */
	private function generator( array $draws, array $taken = array() ): AliasGenerator {
		return new AliasGenerator(
			static function ( int $min, int $max ) use ( &$draws ): int {
				unset( $min, $max );
				return (int) array_shift( $draws );
			},
			static fn ( string $number ): bool => in_array( $number, $taken, true )
		);
	}

	public function test_returns_a_five_digit_number(): void {
		$this->assertSame( '48213', $this->generator( array( 48213 ) )->generate() );
	}

	public function test_draws_from_the_five_digit_range_without_leading_zeros(): void {
		$bounds    = array();
		$generator = new AliasGenerator(
			static function ( int $min, int $max ) use ( &$bounds ): int {
				$bounds = array( $min, $max );
				return $min;
			},
			static fn (): bool => false
		);

		$generator->generate();

		$this->assertSame( array( 10000, 99999 ), $bounds );
	}

	public function test_draws_again_when_the_number_is_taken(): void {
		$alias = $this->generator( array( 11111, 22222, 33333 ), array( '11111', '22222' ) )->generate();

		$this->assertSame( '33333', $alias );
	}

	public function test_gives_up_after_too_many_collisions(): void {
		$this->expectException( AliasSpaceExhausted::class );

		$this->generator( array_fill( 0, AliasGenerator::MAX_ATTEMPTS, 11111 ), array( '11111' ) )->generate();
	}
}
