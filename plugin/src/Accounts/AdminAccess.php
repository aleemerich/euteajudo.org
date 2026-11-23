<?php
/**
 * Keeps Friends and Agents out of wp-admin.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts;

/**
 * Only people who manage content use wp-admin and the toolbar. Agents work through the REST API,
 * which does not go through admin_init.
 */
final class AdminAccess {

	/**
	 * Hooks the redirect and the toolbar filter.
	 */
	public static function register(): void {
		add_action( 'admin_init', array( self::class, 'redirect_blocked_users' ) );
		add_filter( 'show_admin_bar', array( self::class, 'filter_admin_bar' ) );
	}

	/**
	 * Whether a user must stay out of wp-admin.
	 *
	 * @param \WP_User $user User.
	 */
	public static function is_blocked( \WP_User $user ): bool {
		return $user->exists() && ! user_can( $user, 'edit_posts' );
	}

	/**
	 * Sends blocked users back to the site, except for admin-ajax requests.
	 */
	public static function redirect_blocked_users(): void {
		if ( wp_doing_ajax() || ! self::is_blocked( wp_get_current_user() ) ) {
			return;
		}
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	/**
	 * Hides the toolbar from blocked users.
	 *
	 * @param bool $show Whether to show the toolbar.
	 */
	public static function filter_admin_bar( bool $show ): bool {
		return $show && ! self::is_blocked( wp_get_current_user() );
	}
}
