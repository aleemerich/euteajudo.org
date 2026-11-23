<?php
/**
 * Unit tests for the alias range.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Unit\Accounts;

use EuTeAjudo\Accounts\AliasRange;
use PHPUnit\Framework\TestCase;

/**
 * Digit-based ranges without leading zeros, and how they widen.
 */
final class AliasRangeTest extends TestCase {

	public function test_four_to_six_digits_spans_one_thousand_to_nine_hundred_ninety_nine_thousand(): void {
		$range = new AliasRange( 4, 6 );

		$this->assertSame( 1000, $range->lowest() );
		$this->assertSame( 999999, $range->highest() );
	}

	public function test_default_is_four_to_six_digits(): void {
		$this->assertSame( array( 4, 6 ), AliasRange::default()->to_array() );
	}

	public function test_widening_adds_one_digit_to_the_top(): void {
		$wider = ( new AliasRange( 4, 6 ) )->widened();

		$this->assertNotNull( $wider );
		$this->assertSame( array( 4, 7 ), $wider->to_array() );
	}

	public function test_cannot_widen_past_nine_digits(): void {
		$this->assertNull( ( new AliasRange( 4, 9 ) )->widened() );
	}

	public function test_invalid_stored_values_fall_back_to_the_default(): void {
		$this->assertSame(
			array( 4, 6 ),
			AliasRange::from_array(
				array(
					'min_digits' => 7,
					'max_digits' => 3,
				)
			)->to_array()
		);
		$this->assertSame( array( 4, 6 ), AliasRange::from_array( 'garbage' )->to_array() );
		$this->assertSame(
			array( 5, 8 ),
			AliasRange::from_array(
				array(
					'min_digits' => 5,
					'max_digits' => 8,
				)
			)->to_array()
		);
	}
}
