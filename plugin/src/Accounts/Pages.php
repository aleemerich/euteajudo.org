<?php
/**
 * Account pages.
 *
 * @package EuTeAjudo
 */

declare(strict_types=1);

namespace EuTeAjudo\Accounts;

/**
 * The public, non-indexed account pages (ADR 0005). Each page holds one plugin block; the page
 * IDs live in an option, so administrators can rename titles and slugs freely.
 */
final class Pages {

	public const OPTION = 'eta_pages';

	public const SIGN_IN            = 'sign_in';
	public const SIGN_UP            = 'sign_up';
	public const PASSWORD_REQUEST   = 'password_request';
	public const PASSWORD_RESET     = 'password_reset';
	public const EMAIL_CONFIRMATION = 'email_confirmation';
	public const ACCOUNT            = 'account';
	public const WRITE              = 'write';
	public const MY_REQUESTS        = 'my_requests';

	public const BLOCKS = array(
		self::SIGN_IN            => 'euteajudo/sign-in',
		self::SIGN_UP            => 'euteajudo/sign-up',
		self::PASSWORD_REQUEST   => 'euteajudo/password-request',
		self::PASSWORD_RESET     => 'euteajudo/password-reset',
		self::EMAIL_CONFIRMATION => 'euteajudo/email-confirmation',
		self::ACCOUNT            => 'euteajudo/account',
		self::WRITE              => 'euteajudo/write',
		self::MY_REQUESTS        => 'euteajudo/my-requests',
	);

	/**
	 * Creates the pages that do not exist yet, titled in the site language. Idempotent.
	 */
	public static function install(): void {
		$ids      = self::ids();
		$switched = switch_to_locale( get_locale() );
		foreach ( self::BLOCKS as $key => $block ) {
			$existing = isset( $ids[ $key ] ) ? get_post( $ids[ $key ] ) : null;
			if ( $existing instanceof \WP_Post && 'trash' !== $existing->post_status ) {
				continue;
			}
			$page_id = wp_insert_post(
				array(
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'post_title'     => self::title( $key ),
					'post_content'   => '<!-- wp:' . $block . ' /-->',
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
				)
			);
			if ( is_int( $page_id ) && $page_id > 0 ) {
				$ids[ $key ] = $page_id;
			}
		}
		if ( $switched ) {
			restore_previous_locale();
		}
		update_option( self::OPTION, $ids, false );
	}

	/**
	 * Page ID for a key, or 0.
	 *
	 * @param string $key Page key.
	 */
	public static function id( string $key ): int {
		return self::ids()[ $key ] ?? 0;
	}

	/**
	 * Page URL for a key, with optional query arguments.
	 *
	 * @param string                $key  Page key.
	 * @param array<string, string> $args Query arguments.
	 */
	public static function url( string $key, array $args = array() ): string {
		$id   = self::id( $key );
		$base = $id > 0 ? (string) get_permalink( $id ) : home_url( '/' );
		return array() === $args ? $base : add_query_arg( array_map( 'rawurlencode', $args ), $base );
	}

	/**
	 * Whether the current request shows one of the given account pages (all when empty).
	 *
	 * @param string[] $keys Page keys.
	 */
	public static function is_current( array $keys = array() ): bool {
		$ids = self::ids();
		if ( array() !== $keys ) {
			$ids = array_intersect_key( $ids, array_flip( $keys ) );
		}
		return array() !== $ids && is_page( array_values( $ids ) );
	}

	/**
	 * Stored page IDs.
	 *
	 * @return array<string, int>
	 */
	private static function ids(): array {
		$stored = get_option( self::OPTION );
		if ( ! is_array( $stored ) ) {
			return array();
		}
		$ids = array();
		foreach ( $stored as $key => $id ) {
			$ids[ (string) $key ] = (int) $id;
		}
		return $ids;
	}

	/**
	 * Default title of a page.
	 *
	 * @param string $key Page key.
	 */
	private static function title( string $key ): string {
		return match ( $key ) {
			self::SIGN_IN            => __( 'Sign in', 'euteajudo' ),
			self::SIGN_UP            => __( 'Create account', 'euteajudo' ),
			self::PASSWORD_REQUEST   => __( 'Forgot password', 'euteajudo' ),
			self::PASSWORD_RESET     => __( 'New password', 'euteajudo' ),
			self::EMAIL_CONFIRMATION => __( 'Confirm email', 'euteajudo' ),
			self::ACCOUNT            => __( 'My account', 'euteajudo' ),
			self::WRITE              => __( 'Write', 'euteajudo' ),
			self::MY_REQUESTS        => __( 'My requests', 'euteajudo' ),
			default                  => $key,
		};
	}
}
