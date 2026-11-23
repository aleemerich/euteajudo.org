<?php
/**
 * Conversation addresses and access.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\HelpRequests;

use EuTeAjudo\Accounts\Pages;

/**
 * A conversation lives at /<my requests page>/<code>/ (ADR 0005). The code is random; anyone who
 * is not the author or an administrator gets a 404, never a 403, so the address does not even
 * confirm that the request exists.
 */
final class Conversations {

	public const QUERY_VAR = 'eta_code';

	/**
	 * Hooks routing and access checks.
	 */
	public static function register(): void {
		add_filter( 'query_vars', array( self::class, 'query_vars' ) );
		add_action( 'init', array( self::class, 'add_rule' ), 20 );
		add_action( 'template_redirect', array( self::class, 'guard' ), 1 );
		add_action( 'post_updated', array( self::class, 'refresh_rules_on_slug_change' ), 10, 3 );
	}

	/**
	 * Adds the code query variable.
	 *
	 * @param string[] $vars Query variables.
	 * @return string[]
	 */
	public static function query_vars( array $vars ): array {
		$vars[] = self::QUERY_VAR;
		return $vars;
	}

	/**
	 * Maps /<my requests path>/<code>/ to the My requests page with the code.
	 */
	public static function add_rule(): void {
		$page_id = Pages::id( Pages::MY_REQUESTS );
		$path    = $page_id > 0 ? get_page_uri( $page_id ) : '';
		if ( ! is_string( $path ) || '' === $path ) {
			return;
		}
		add_rewrite_rule( '^' . preg_quote( $path, '#' ) . '/([a-z0-9]{12})/?$', 'index.php?page_id=' . $page_id . '&' . self::QUERY_VAR . '=$matches[1]', 'top' );
	}

	/**
	 * Regenerates rewrite rules when the My requests page changes its address.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $after   Post after the update.
	 * @param \WP_Post $before  Post before the update.
	 */
	public static function refresh_rules_on_slug_change( int $post_id, \WP_Post $after, \WP_Post $before ): void {
		if ( Pages::id( Pages::MY_REQUESTS ) === $post_id && ( $after->post_name !== $before->post_name || $after->post_parent !== $before->post_parent ) ) {
			delete_option( 'rewrite_rules' );
		}
	}

	/**
	 * Address of a conversation.
	 *
	 * @param \WP_Post $request Help request.
	 */
	public static function url( \WP_Post $request ): string {
		return trailingslashit( Pages::url( Pages::MY_REQUESTS ) ) . $request->post_name . '/';
	}

	/**
	 * Code in the current request, or an empty string.
	 */
	public static function current_code(): string {
		$code = get_query_var( self::QUERY_VAR );
		return is_string( $code ) ? $code : '';
	}

	/**
	 * The request for a code, if the current user may see it.
	 *
	 * @param string $code Code.
	 */
	public static function visible_request( string $code ): ?\WP_Post {
		$post = HelpRequests::find_by_code( $code );
		return null !== $post && HelpRequests::can_view( $post->ID, get_current_user_id() ) ? $post : null;
	}

	/**
	 * Before rendering: sign-in for guests, 404 for people without access, "seen" for the author.
	 */
	public static function guard(): void {
		$code = self::current_code();
		if ( '' === $code ) {
			return;
		}
		if ( ! is_user_logged_in() ) {
			return; // The account dispatcher sends guests to the sign-in page.
		}
		$post = self::visible_request( $code );
		if ( null === $post ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
			// WordPress would otherwise "guess" the right page and redirect to My requests,
			// turning the 404 into a 200 (ADR 0005 requires a plain 404).
			remove_action( 'template_redirect', 'redirect_canonical' );
			return;
		}
		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : 'GET';
		if ( 'GET' === $method && get_current_user_id() === (int) $post->post_author ) {
			HelpRequests::mark_seen( $post->ID );
		}
	}
}
