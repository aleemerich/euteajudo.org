<?php
/**
 * Integration tests: every account gets an alias and exposes nothing else.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration\Accounts;

use EuTeAjudo\Accounts\Aliases;
use EuTeAjudo\Tests\Integration\TestCase;

/**
 * Alias assignment and protection of personal data on user records.
 */
final class AliasesTest extends TestCase {

	public function test_new_user_gets_an_alias_used_as_every_public_name(): void {
		$user_id = $this->new_user(
			array(
				'user_login' => 'maria.silva@example.org',
				'user_email' => 'maria.silva@example.org',
				'first_name' => 'Maria',
				'last_name'  => 'Silva',
			)
		)->ID;

		$number = Aliases::number_of( $user_id );
		$this->assertMatchesRegularExpression( '/^[1-9]\d{4}$/', $number );

		$user  = $this->reload( $user_id );
		$label = 'Friend ' . $number;
		$this->assertSame( $label, $user->display_name );
		$this->assertSame( $label, $user->nickname );
		$this->assertSame( 'friend-' . $number, $user->user_nicename );
		$this->assertSame( '', $user->first_name );
		$this->assertSame( '', $user->last_name );
	}

	public function test_aliases_are_unique(): void {
		$numbers = array();
		for ( $i = 0; $i < 20; $i++ ) {
			$numbers[] = Aliases::number_of( $this->new_user()->ID );
		}
		$this->assertCount( 20, array_unique( $numbers ) );
	}

	public function test_profile_updates_cannot_replace_the_alias_with_a_name(): void {
		$user_id = $this->new_user()->ID;
		$label   = $this->reload( $user_id )->display_name;

		wp_update_user(
			array(
				'ID'           => $user_id,
				'display_name' => 'Maria Silva',
				'nickname'     => 'Maria',
				'first_name'   => 'Maria',
				'last_name'    => 'Silva',
			)
		);

		$user = $this->reload( $user_id );
		$this->assertSame( $label, $user->display_name );
		$this->assertSame( $label, $user->nickname );
		$this->assertSame( '', $user->first_name );
		$this->assertSame( '', $user->last_name );
	}

	public function test_backfill_gives_aliases_to_accounts_created_before_the_plugin(): void {
		$user_id = $this->new_user( array( 'display_name' => 'Old Admin' ) )->ID;
		delete_user_meta( $user_id, Aliases::META );

		Aliases::backfill();

		$this->assertNotSame( '', Aliases::number_of( $user_id ) );
		$this->assertStringStartsWith( 'Friend ', $this->reload( $user_id )->display_name );
	}
}
