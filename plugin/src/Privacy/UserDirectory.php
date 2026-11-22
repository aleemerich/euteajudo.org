<?php
/**
 * Closes the places where WordPress lists users.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Privacy;

/**
 * The REST user list and author archives reveal accounts and their slugs. Only people who can
 * list users (administrators) may see them; everyone else gets an error or a 404.
 */
final class UserDirectory {

	/**
	 * Hooks the filters.
	 */
	public static function register(): void {
		add_filter( 'rest_pre_dispatch', array( self::class, 'guard_rest_users' ), 10, 3 );
		add_action( 'parse_query', array( self::class, 'hide_author_archives' ) );
		add_filter( 'author_link', array( self::class, 'neutral_author_link' ) );
	}

	/**
	 * Refuses /wp/v2/users for people who cannot list users, except their own /users/me.
	 *
	 * @param mixed            $result  Response to replace the requested one.
	 * @param \WP_REST_Server  $server  Server instance.
	 * @param \WP_REST_Request $request Request.
	 * @return mixed
	 *
	 * @phpstan-param \WP_REST_Request<array<string, mixed>> $request
	 */
	public static function guard_rest_users( mixed $result, \WP_REST_Server $server, \WP_REST_Request $request ): mixed {
		unset( $server );
		$route = untrailingslashit( $request->get_route() );
		if ( ! str_starts_with( $route, '/wp/v2/users' ) || '/wp/v2/users/me' === $route || current_user_can( 'list_users' ) ) {
			return $result;
		}
		return new \WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to list users.', 'euteajudo' ), array( 'status' => rest_authorization_required_code() ) );
	}

	/**
	 * Turns front-end author archives into 404 pages.
	 *
	 * @param \WP_Query $query Query being parsed.
	 */
	public static function hide_author_archives( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_author() ) {
			return;
		}
		$query->set_404();
	}

	/**
	 * Points author links to the home page, so no template prints a user slug.
	 */
	public static function neutral_author_link(): string {
		return home_url( '/' );
	}
}
