<?php
/**
 * Integration tests: the users screen shows and finds aliases.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration\Admin;

use EuTeAjudo\Accounts\Aliases;
use EuTeAjudo\Admin\UsersList;
use EuTeAjudo\Tests\Integration\TestCase;
use WP_User_Query;

/**
 * Administrators identify accounts by alias.
 */
final class UsersListTest extends TestCase {

	public function set_up(): void {
		parent::set_up();
		set_current_screen( 'users' );
	}

	public function tear_down(): void {
		set_current_screen( 'front' );
		parent::tear_down();
	}

	public function test_alias_column_replaces_the_always_empty_name_column(): void {
		$this->assertSame( 10, has_filter( 'manage_users_columns', array( UsersList::class, 'columns' ) ) );

		$columns = UsersList::columns(
			array(
				'cb'       => '',
				'username' => 'Username',
				'name'     => 'Name',
				'email'    => 'Email',
				'role'     => 'Role',
			)
		);

		$this->assertSame( array( 'cb', 'username', UsersList::COLUMN, 'email', 'role' ), array_keys( $columns ) );
	}

	public function test_alias_column_shows_the_alias_label(): void {
		$user = $this->new_user();

		$output = apply_filters( 'manage_users_custom_column', '', UsersList::COLUMN, $user->ID ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core hook under test.

		$this->assertSame( Aliases::label( Aliases::number_of( $user->ID ) ), $output );
	}

	public function test_searching_an_alias_number_finds_the_account(): void {
		$target = $this->new_user();
		$this->new_user();

		$query = new WP_User_Query( array( 'search' => Aliases::number_of( $target->ID ) ) );

		$this->assertSame( array( $target->ID ), array_map( static fn ( $user ): int => $user->ID, $query->get_results() ) );
	}

	public function test_alias_column_is_sortable(): void {
		$sortable = apply_filters( 'manage_users_sortable_columns', array() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core hook under test.

		$this->assertArrayHasKey( UsersList::COLUMN, $sortable );
	}
}
