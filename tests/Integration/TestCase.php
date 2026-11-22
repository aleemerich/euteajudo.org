<?php
/**
 * Base class for integration tests.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration;

use WP_UnitTestCase;
use WP_User;

/**
 * Typed helpers over the WordPress test factories.
 */
abstract class TestCase extends WP_UnitTestCase {

	/**
	 * Creates a user and returns it.
	 *
	 * @param array<string, mixed> $args User fields.
	 */
	protected function new_user( array $args = array() ): WP_User {
		$user = self::factory()->user->create_and_get( $args );
		$this->assertInstanceOf( WP_User::class, $user );
		return $user;
	}

	/**
	 * Reloads a user from the database.
	 *
	 * @param int $user_id User ID.
	 */
	protected function reload( int $user_id ): WP_User {
		clean_user_cache( $user_id );
		$user = get_userdata( $user_id );
		$this->assertInstanceOf( WP_User::class, $user );
		return $user;
	}
}
