<?php
/**
 * Integration tests: roles, admin access and privacy of user data.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Tests\Integration\Accounts;

use EuTeAjudo\Accounts\AdminAccess;
use EuTeAjudo\Accounts\Roles;
use EuTeAjudo\Tests\Integration\TestCase;
use WP_REST_Request;

/**
 * Friends and agents stay out of wp-admin; nothing reveals who they are.
 */
final class RolesAndAccessTest extends TestCase {

	public function test_friend_and_agent_roles_exist_and_new_accounts_are_friends(): void {
		$this->assertNotNull( get_role( Roles::FRIEND ) );
		$this->assertNotNull( get_role( Roles::AGENT ) );
		$this->assertSame( Roles::FRIEND, get_option( 'default_role' ) );
	}

	public function test_friends_and_agents_cannot_enter_wp_admin_or_see_the_toolbar(): void {
		foreach ( array( Roles::FRIEND, Roles::AGENT ) as $role ) {
			$user = $this->new_user( array( 'role' => $role ) );
			$this->assertTrue( AdminAccess::is_blocked( $user ), $role );
			wp_set_current_user( $user->ID );
			$this->assertFalse( apply_filters( 'show_admin_bar', true ), $role ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core hook under test.
		}

		$admin = $this->new_user( array( 'role' => 'administrator' ) );
		$this->assertFalse( AdminAccess::is_blocked( $admin ) );
	}

	public function test_avatars_never_point_to_gravatar(): void {
		$user_id = $this->new_user( array( 'user_email' => 'someone@example.org' ) )->ID;

		$this->assertFalse( (bool) get_option( 'show_avatars' ) );
		$this->assertSame( '', (string) get_avatar_url( $user_id ) );
		$this->assertStringNotContainsString( 'gravatar', (string) get_avatar( $user_id ) );
	}

	public function test_rest_user_list_is_closed_to_people_who_cannot_list_users(): void {
		wp_set_current_user( $this->new_user( array( 'role' => Roles::FRIEND ) )->ID );

		$response = rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/users' ) );

		$this->assertGreaterThanOrEqual( 400, $response->get_status() );
	}

	public function test_author_archives_are_not_found(): void {
		$user_id = $this->new_user( array( 'role' => 'author' ) )->ID;
		self::factory()->post->create( array( 'post_author' => $user_id ) );

		$this->go_to( home_url( '/?author=' . $user_id ) );

		$this->assertTrue( is_404() );
	}
}
